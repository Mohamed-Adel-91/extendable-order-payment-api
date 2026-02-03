<?php

namespace App\Interfaces;

use App\Models\Order;
use App\Models\Payment;

interface PaymentGatewayInterface
{

    // Start payment and returns payment url (redirect / iframe).
    public function initiate(Order $order, Payment $payment): string;


    //  callback/webhook integrity
    public function verifySignature(array $payload): bool;
}
