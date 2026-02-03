<?php

namespace App\Services\Payments\Gateways;

use App\Models\Order;
use App\Models\Payment;
use App\Interfaces\PaymentGatewayInterface;

class PaymobGateway implements PaymentGatewayInterface
{
    public function initiate(Order $order, Payment $payment): string
    {
        // Implementation for Paymob payment initiation
        return 'https://paymob.com/';
    }

    public function verifySignature(array $payload): bool
    {
        // Implementation for Paymob signature verification
        return true;
    }
}
