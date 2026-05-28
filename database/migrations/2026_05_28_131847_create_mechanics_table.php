<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mechanics', function (Blueprint $table) {
            $table->string('mechanic_id', 20)->primary();
            $table->string('mechanic_name', 100);
            $table->string('phone_number', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('specialization', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mechanics');
    }
};