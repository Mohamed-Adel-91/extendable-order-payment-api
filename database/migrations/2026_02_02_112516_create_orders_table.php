<?php

use App\Enums\OrderStatus;
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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->unsignedTinyInteger('status')->default(OrderStatus::CREATED);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->char('currency', 3)->default(env('APP_CURRENCY', 'EGP'));
            $table->text('notes')->nullable();
            $table->text('cancel_reason')->nullable();
            $table->index(['user_id']);
            $table->index(['status']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
