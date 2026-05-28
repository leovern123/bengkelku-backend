<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_categories', function (Blueprint $table) {
            $table->unsignedBigInteger('item_category_id')->primary();
            $table->unsignedBigInteger('item_type_id');
            $table->string('category_name', 100);
            $table->timestamps();

            $table->foreign('item_type_id')
                ->references('item_type_id')
                ->on('item_types')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_categories');
    }
};