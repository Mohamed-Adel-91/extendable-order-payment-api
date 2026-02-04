<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_id',
        'user_id',
        'method',
        'status',
        'amount',
        'currency',
        'merchant_order_id',
        'gateway_payment_id',
        'payment_url',
        'gateway_request',
        'gateway_response',
        'gateway_webhook',
        'paid_at',
        'cancelled_at',
        'refunded_at',
    ];

    protected $casts = [
        'method'          => 'int',
        'status'          => 'int',
        'amount'          => 'decimal:2',
        'gateway_request' => 'array',
        'gateway_response'=> 'array',
        'gateway_webhook' => 'array',
        'paid_at'         => 'datetime',
        'cancelled_at'    => 'datetime',
        'refunded_at'     => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isPending(): bool
    {
        return (int) $this->status === PaymentStatus::PENDING;
    }

    public function isSuccess(): bool
    {
        return (int) $this->status === PaymentStatus::SUCCESS;
    }

    public function isRefunded(): bool
    {
        return (int) $this->status === PaymentStatus::REFUNDED;
    }

    public function isKashier(): bool
    {
        return (int) $this->method === PaymentMethod::KASHIER;
    }
}
