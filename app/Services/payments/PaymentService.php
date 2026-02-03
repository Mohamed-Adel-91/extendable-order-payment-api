<?php

namespace App\Services\Payments;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PaymentService
{
    public function __construct(private readonly PaymentGatewayManager $gatewayManager)
    {
    }

    public function startPayment(Order $order, int $method = PaymentMethod::KASHIER): Payment
    {
        if ((int) $order->status !== OrderStatus::CREATED) {
            throw new RuntimeException('Payment can only be initiated for CREATED orders.');
        }

        return DB::transaction(function () use ($order, $method) {

            $payment = Payment::create([
                'order_id'          => $order->id,
                'user_id'           => $order->user_id,
                'method'            => $method,
                'status'            => PaymentStatus::PENDING,
                'amount'            => $order->total_amount,
                'currency'          => $order->currency ?? config('payments.currency', 'EGP'),
                'merchant_order_id' => $this->buildMerchantOrderId($order),
            ]);

            $gateway = $this->gatewayManager->resolve((int) $payment->method);

            $paymentUrl = $gateway->initiate($order, $payment);

            $payment->update([
                'payment_url'     => $paymentUrl,
                'gateway_request' => [
                    'method' => (int) $payment->method,
                    'type'   => 'initiate',
                ],
            ]);

            return $payment->fresh();
        });
    }

    private function buildMerchantOrderId(Order $order): string
    {
        return 'ORD-' . $order->id . '-' . Str::upper(Str::random(8));
    }
}
