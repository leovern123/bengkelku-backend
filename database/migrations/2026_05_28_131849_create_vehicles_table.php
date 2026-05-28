<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->string('vehicle_id', 20)->primary();
            $table->string('customer_id', 20);
            $table->string('license_plate', 20)->unique();
            $table->string('brand', 50)->nullable();
            $table->string('model', 50)->nullable();
            $table->year('year')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')
                ->references('customer_id')
                ->on('customers')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};