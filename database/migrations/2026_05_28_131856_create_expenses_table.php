<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->string('expense_id', 20)->primary();
            $table->string('user_id', 20);
            $table->string('expense_name', 100);
            $table->string('expense_category', 100)->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->date('expense_date');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('user_id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};