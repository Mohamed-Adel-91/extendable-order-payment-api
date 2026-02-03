<?php

namespace App\Services\Payments\Gateways;

use App\Models\Order;
use App\Models\Payment;
use App\Interfaces\PaymentGatewayInterface;

class KashierGateway implements PaymentGatewayInterface
{
    public function initiate(Order $order, Payment $payment): string
    {
        $baseUrl   = rtrim(config('payments.kashier.base_url'), '/');
        $merchantId = (string) config('payments.kashier.merchant_id');
        $mode      = (string) config('payments.kashier.mode');
        $currency  = (string) $payment->currency;
        $amount    = number_format((float) $payment->amount, 2, '.', '');
        $merchantRedirect = (string) config('payments.kashier.merchant_redirect');
        $orderRef = (string) $payment->merchant_order_id;

        $hash = $this->buildHash([
            'merchantId'       => $merchantId,
            'order'            => $orderRef,
            'amount'           => $amount,
            'currency'         => $currency,
            'mode'             => $mode,
            'merchantRedirect' => $merchantRedirect,
        ]);

        $query = http_build_query([
            'merchantId'       => $merchantId,
            'order'            => $orderRef,
            'amount'           => $amount,
            'currency'         => $currency,
            'mode'             => $mode,
            'merchantRedirect' => $merchantRedirect,
            'hash'             => $hash,
        ]);

        return "{$baseUrl}/?{$query}";
    }

    public function verifySignature(array $payload): bool
    {
        // webhook/callback
        return true;
    }

    private function buildHash(array $params): string
    {
        $secret = (string) config('payments.kashier.secret');

        $plain = implode('', [
            $params['merchantId'],
            $params['order'],
            $params['amount'],
            $params['currency'],
            $params['mode'],
            $params['merchantRedirect'],
            $secret,
        ]);

        return hash('sha256', $plain);
    }
}
