<?php

namespace App\Services\Payments\Gateways;

use App\Models\Order;
use App\Models\Payment;
use App\Interfaces\PaymentGatewayInterface;
use Illuminate\Support\Facades\Log;

class KashierGateway implements PaymentGatewayInterface
{
    public function initiate(Order $order, Payment $payment): string
    {
        $baseUrl    = rtrim(config('payments.kashier.base_url'), '/');
        $merchantId = (string) config('payments.kashier.merchant_id');
        $apiKey     = (string) config('payments.kashier.api_key');
        $mode       = (string) config('payments.kashier.mode');
        $currency   = strtoupper((string) $payment->currency);
        $amount     = number_format((float) $payment->amount, 2, '.', '');
        $merchantRedirect = urlencode((string) config('payments.kashier.merchant_redirect'));
        $orderRef   = (string) $payment->merchant_order_id;

        $path = "/?payment={$merchantId}.{$orderRef}.{$amount}.{$currency}";
        $hash = hash_hmac('sha256', $path, $apiKey, false);

        $query = http_build_query([
            'merchantId'       => $merchantId,
            'orderId'          => $orderRef,
            'amount'           => $amount,
            'currency'         => $currency,
            'mode'             => $mode,
            'merchantRedirect' => $merchantRedirect,
            'hash'             => $hash,
        ]);

        Log::info('Kashier Hash Generation', [
            'merchantId'        => $merchantId,
            'orderId'           => $orderRef,
            'amount'            => $amount,
            'currency'          => $currency,
            'mode'              => $mode,
            'merchantRedirect'  => $merchantRedirect,
            'path'              => $path,
            'secret'            => substr($apiKey, 0, 10) . '...',
            'hash'              => $hash,
        ]);

        return "{$baseUrl}/?{$query}";
    }

    public function verifySignature(array $payload): bool
    {
        // webhook/callback
        return true;
    }
}
