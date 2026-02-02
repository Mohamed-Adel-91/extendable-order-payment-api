<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Admin;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function createOrder(User $user, array $payload): Order
    {
        return DB::transaction(function () use ($user, $payload) {
            $items = $payload['items'] ?? [];

            $products = Product::query()
                ->whereIn('id', collect($items)->pluck('product_id')->all())
                ->get()
                ->keyBy('id');

            $order = Order::create([
                'user_id'        => $user->id,
                'status'         => OrderStatus::CREATED,
                'total_amount'   => 0,
                'currency'       => env('APP_CURRENCY', 'EGP'),
                'notes'          => $payload['notes'] ?? null,
                'cancel_reason'  => null,
            ]);

            $total = 0;

            foreach ($items as $item) {
                $productId = (int) $item['product_id'];
                $qty       = (int) $item['quantity'];

                $product = $products->get($productId);
                if (!$product) {
                    throw ValidationException::withMessages([
                        'items' => ["Invalid product_id: {$productId}"],
                    ]);
                }

                $unitPrice = (float) $product->price;
                $lineTotal = $unitPrice * $qty;
                $total    += $lineTotal;

                $order->items()->create([
                    'product_id'   => $product->id,
                    'product_name' => (string) $product->name,
                    'unit_price'   => $unitPrice,
                    'quantity'     => $qty,
                    'line_total'   => $lineTotal,
                ]);
            }

            $order->update(['total_amount' => $total]);

            return $order->load('items');
        });
    }

    public function updateOrder(Order $order, array $payload): Order
    {
        if ((int) $order->status !== OrderStatus::CREATED) {
            throw ValidationException::withMessages([
                'order' => ['Only CREATED orders can be updated.'],
            ]);
        }

        return DB::transaction(function () use ($order, $payload) {
            if (array_key_exists('notes', $payload)) {
                $order->notes = $payload['notes'];
            }

            if (array_key_exists('items', $payload)) {
                $items = $payload['items'] ?? [];

                $products = Product::query()
                    ->whereIn('id', collect($items)->pluck('product_id')->all())
                    ->get()
                    ->keyBy('id');

                $order->items()->delete();

                $total = 0;

                foreach ($items as $item) {
                    $productId = (int) $item['product_id'];
                    $qty       = (int) $item['quantity'];

                    $product = $products->get($productId);
                    if (!$product) {
                        throw ValidationException::withMessages([
                            'items' => ["Invalid product_id: {$productId}"],
                        ]);
                    }

                    $unitPrice = (float) $product->price;
                    $lineTotal = $unitPrice * $qty;
                    $total    += $lineTotal;

                    $order->items()->create([
                        'product_id'   => $product->id,
                        'product_name' => (string) $product->name,
                        'unit_price'   => $unitPrice,
                        'quantity'     => $qty,
                        'line_total'   => $lineTotal,
                    ]);
                }

                $order->total_amount = $total;
            }

            $order->save();

            return $order->refresh()->load('items');
        });
    }

    public function cancelOrder(Order $order, ?string $reason = null): Order
    {
        if ((int) $order->status !== OrderStatus::CREATED) {
            throw ValidationException::withMessages([
                'order' => ['Only CREATED orders can be cancelled by user.'],
            ]);
        }

        $order->status = OrderStatus::CANCELLED;
        $order->cancel_reason = $reason ? mb_substr(trim($reason), 0, 500) : null;
        $order->save();

        return $order->refresh()->load('items');
    }

    public function updateStatusByAdmin(Admin $admin, Order $order, int $newStatus, ?string $cancelReason = null): Order
    {
        $current = (int) $order->status;

        $allowedTargets = [OrderStatus::SHIPPING, OrderStatus::DELIVERED, OrderStatus::CANCELLED];
        if (!in_array($newStatus, $allowedTargets, true)) {
            throw ValidationException::withMessages([
                'status' => ['Admin can only set SHIPPING, DELIVERED, or CANCELLED.'],
            ]);
        }

        if (in_array($current, [OrderStatus::DELIVERED, OrderStatus::CANCELLED], true)) {
            throw ValidationException::withMessages([
                'order' => ['This order cannot be updated anymore.'],
            ]);
        }

        // Only super_admin can change status from CREATED -> SHIPPING/DELIVERED
        if ($current === OrderStatus::CREATED && in_array($newStatus, [OrderStatus::SHIPPING, OrderStatus::DELIVERED], true)) {
            if (($admin->role ?? null) !== 'super_admin') {
                throw ValidationException::withMessages([
                    'status' => ['Only super_admin can move CREATED order to SHIPPING/DELIVERED directly.'],
                ]);
            }
        }

        $allowedByCurrent = match ($current) {
            OrderStatus::CREATED  => [OrderStatus::SHIPPING, OrderStatus::DELIVERED, OrderStatus::CANCELLED],
            OrderStatus::PAID     => [OrderStatus::SHIPPING, OrderStatus::DELIVERED, OrderStatus::CANCELLED],
            OrderStatus::SHIPPING => [OrderStatus::DELIVERED, OrderStatus::CANCELLED],
            default               => [],
        };

        if (!in_array($newStatus, $allowedByCurrent, true)) {
            throw ValidationException::withMessages([
                'status' => ['Invalid status transition for this order.'],
            ]);
        }

        $order->status = $newStatus;

        if ($newStatus === OrderStatus::CANCELLED) {
            $order->cancel_reason = $cancelReason ? mb_substr(trim($cancelReason), 0, 500) : ($order->cancel_reason ?? null);
        } else {
            $order->cancel_reason = null;
        }

        $order->save();

        return $order->refresh()->load('items');
    }
}
