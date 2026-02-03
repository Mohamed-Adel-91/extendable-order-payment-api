<?php

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->unsignedTinyInteger('method')->default(PaymentMethod::KASHIER);
            $table->unsignedTinyInteger('status')->default(PaymentStatus::PENDING);
            $table->decimal('amount', 12, 2);
            $table->char('currency', 3)->default(env('APP_CURRENCY', 'EGP'));
            $table->string('merchant_order_id')->index();
            $table->string('gateway_payment_id')->nullable()->index();
            $table->text('payment_url')->nullable();
            $table->json('gateway_request')->nullable();
            $table->json('gateway_response')->nullable();
            $table->json('gateway_webhook')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();
            $table->index(['order_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
