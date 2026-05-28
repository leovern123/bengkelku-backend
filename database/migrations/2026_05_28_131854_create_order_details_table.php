<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_details', function (Blueprint $table) {
            $table->string('order_detail_id', 20)->primary();
            $table->string('order_id', 20);
            $table->string('item_id', 20);
            $table->integer('quantity')->default(1);
            $table->decimal('purchase_price_at_transaction', 12, 2)->default(0);
            $table->decimal('selling_price_at_transaction', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->timestamps();

            $table->foreign('order_id')
                ->references('order_id')
                ->on('orders')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('item_id')
                ->references('item_id')
                ->on('items')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_details');
    }
};