<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->string('payment_id', 20)->primary();
            $table->string('order_id', 20);
            $table->enum('payment_method', ['cash', 'qris', 'transfer', 'debit'])->default('cash');
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('change_amount', 12, 2)->default(0);
            $table->enum('payment_status', ['unpaid', 'paid', 'cancelled'])->default('unpaid');
            $table->dateTime('payment_date')->nullable();
            $table->timestamps();

            $table->foreign('order_id')
                ->references('order_id')
                ->on('orders')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};