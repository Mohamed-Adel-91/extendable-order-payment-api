<?php
namespace App\Services\Payments\Callback;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class KashierPaymentCallbackService
{
    public function handleKashierCallback(array $payload): array
    {
        foreach (['merchantOrderId', 'paymentStatus', 'signature'] as $key) {
            if (!isset($payload[$key]) || $payload[$key] === '') {
                throw ValidationException::withMessages([$key => ['Missing required callback field.']]);
            }
        }
        $merchantOrderId = (string) $payload['merchantOrderId'];
        $statusText      = strtoupper((string) $payload['paymentStatus']);
        if (!$this->verifyKashierSignature($payload)) {
            throw ValidationException::withMessages(['signature' => ['Invalid signature.']]);
        }
        $payment = Payment::query()
            ->where('merchant_order_id', $merchantOrderId)
            ->latest('id')
            ->first();
        if (!$payment) {
            throw ValidationException::withMessages(['merchantOrderId' => ['Payment not found.']]);
        }
        $newPaymentStatus = match ($statusText) {
            'SUCCESS'   => PaymentStatus::SUCCESS,
            'FAILED'    => PaymentStatus::FAILED,
            'CANCELLED' => PaymentStatus::CANCELLED,
            default     => PaymentStatus::FAILED,
        };
        return DB::transaction(function () use ($payment, $payload, $newPaymentStatus, $statusText) {
            $payment->status = $newPaymentStatus;
            $payment->gateway_payment_id = $payload['transactionId'] ?? $payment->gateway_payment_id;
            $payment->gateway_response = $payload;
            if ($newPaymentStatus === PaymentStatus::SUCCESS) {
                $payment->paid_at = now();
                $order = Order::lockForUpdate()->findOrFail($payment->order_id);
                $order->status = OrderStatus::PAID;
                $order->save();
            }
            if ($newPaymentStatus === PaymentStatus::CANCELLED) {
                $payment->cancelled_at = now();
            }
            $payment->save();
            return [
                'merchantOrderId' => $payment->merchant_order_id,
                'payment_status'  => (int) $payment->status,
                'order_id'        => $payment->order_id,
                'order_status'    => $newPaymentStatus === PaymentStatus::SUCCESS ? OrderStatus::PAID : null,
                'transactionId'   => $payload['transactionId'] ?? null,
                'gateway_status'  => $statusText,
            ];
        });
    }
    private function verifyKashierSignature(array $data): bool
    {
        if (!isset($data['signature'])) {
            return false;
        }
        $received = (string) $data['signature'];
        $queryString = '';
        foreach ($data as $key => $value) {
            if ($key === 'signature' || $key === 'mode') {
                continue;
            }
            $queryString .= "&{$key}={$value}";
        }
        $queryString = ltrim($queryString, '&');
        $apiKey = (string) config('payments.kashier.api_key');
        $computed = hash_hmac('sha256', $queryString, $apiKey, false);
        return hash_equals($computed, $received);
    }
}
