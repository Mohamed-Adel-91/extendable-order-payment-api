<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Requests\CancelOrderRequest;

class OrderController extends Controller
{

    public function __construct(private readonly OrderService $orderService)
    {
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);

        $orders = Order::query()
            ->where('user_id', $request->user()->id)
            ->with('items')
            ->latest('id')
            ->paginate($perPage);

        return $this->successResponse([
            'orders' => $orders->items(),
            'pagination' => [
                'total' => $orders->total(),
                'count' => $orders->count(),
                'per_page' => $orders->perPage(),
                'current_page' => $orders->currentPage(),
                'total_pages' => $orders->lastPage(),
            ],
        ]);
    }

    public function show(Request $request, Order $order)
    {
        if ((int) $order->user_id !== (int) $request->user()->id) {
            throw ValidationException::withMessages([
                'order' => ['Not allowed to access this order.'],
            ]);
        }

        $order->load('items');

        return $this->successResponse($order);
    }

    public function store(StoreOrderRequest $request)
    {
        $order = $this->orderService->createOrder($request->user(), $request->validated());

        return $this->successResponse($order, message: 'Order created successfully', statusCode: 201);
    }

    public function update(UpdateOrderRequest $request, Order $order)
    {
        if ((int) $order->user_id !== (int) $request->user()->id) {
            throw ValidationException::withMessages([
                'order' => ['Not allowed to update this order.'],
            ]);
        }

        $order = $this->orderService->updateOrder($order, $request->validated());

        return $this->successResponse($order, message: 'Order updated successfully');
    }

    public function cancel(CancelOrderRequest $request, Order $order)
    {
        if ((int) $order->user_id !== (int) $request->user()->id) {
            throw ValidationException::withMessages([
                'order' => ['Not allowed to cancel this order.'],
            ]);
        }

        $reason = $request->validated()['cancel_reason'] ?? null;

        $order = $this->orderService->cancelOrder($order, $reason);

        return $this->successResponse($order, message: 'Order cancelled successfully');
    }
}
