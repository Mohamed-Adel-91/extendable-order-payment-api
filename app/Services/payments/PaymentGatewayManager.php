<?php

namespace App\Services\Payments;

use App\Enums\PaymentMethod;
use App\Interfaces\PaymentGatewayInterface;
use App\Services\Payments\Gateways\KashierGateway;
use InvalidArgumentException;

class PaymentGatewayManager
{
    private array $map = [
        PaymentMethod::KASHIER => KashierGateway::class,
        // PaymentMethod::PAYMOB   => PaymobGateway::class,
    ];

    public function resolve(int $method): PaymentGatewayInterface
    {
        $class = $this->map[$method] ?? null;

        if (!$class) {
            throw new InvalidArgumentException("Unsupported payment method: {$method}");
        }

        return app($class);
    }
}
