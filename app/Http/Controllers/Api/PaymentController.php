<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StartPaymentRequest;
use App\Models\Order;
use App\Services\Payments\PaymentService;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{

    public function __construct(private readonly PaymentService $paymentService)
    {
    }

    public function start(StartPaymentRequest $request, Order $order)
    {
        if ((int) $order->user_id !== (int) $request->user()->id) {
            throw ValidationException::withMessages([
                'order' => ['Not allowed to pay for this order.'],
            ]);
        }

        $method = $request->validated()['method'] ?? null;

        $payment = $this->paymentService->startPayment($order, $method ?? \App\Enums\PaymentMethod::KASHIER);

        return $this->successResponse([
            'payment_id'  => $payment->id,
            'order_id'    => $payment->order_id,
            'status'      => (int) $payment->status,
            'method'      => (int) $payment->method,
            'amount'      => (string) $payment->amount,
            'currency'    => $payment->currency,
            'payment_url' => $payment->payment_url,
        ], message: 'Payment initiated successfully', statusCode: 201);
    }
}
