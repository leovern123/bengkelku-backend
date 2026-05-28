<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->string('item_id', 20)->primary();
            $table->unsignedBigInteger('item_category_id');
            $table->string('item_name', 100);
            $table->decimal('purchase_price', 12, 2)->default(0);
            $table->decimal('selling_price', 12, 2)->default(0);
            $table->integer('stock')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();

            $table->foreign('item_category_id')
                ->references('item_category_id')
                ->on('item_categories')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};