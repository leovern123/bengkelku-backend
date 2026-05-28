<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->string('order_id', 20)->primary();
            $table->string('customer_id', 20);
            $table->string('vehicle_id', 20);
            $table->string('user_id', 20);
            $table->string('mechanic_id', 20)->nullable();
            $table->string('order_code', 50)->unique();
            $table->enum('order_status', ['pending', 'process', 'completed', 'cancelled'])->default('pending');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->timestamps();

            $table->foreign('customer_id')
                ->references('customer_id')
                ->on('customers')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('vehicle_id')
                ->references('vehicle_id')
                ->on('vehicles')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('user_id')
                ->references('user_id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('mechanic_id')
                ->references('mechanic_id')
                ->on('mechanics')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};