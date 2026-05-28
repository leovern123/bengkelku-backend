<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->string('user_id', 20)->primary();
            $table->unsignedBigInteger('role_id');
            $table->string('name', 100);
            $table->string('email', 100)->unique();
            $table->string('password');
            $table->string('phone_number', 20)->nullable();
            $table->string('avatar')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('role_id')
                ->references('role_id')
                ->on('roles')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};