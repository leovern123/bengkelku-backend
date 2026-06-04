<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['phone_number', 'note']);
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('year');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('phone_number', 20)->nullable();
            $table->text('note')->nullable();
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->year('year')->nullable();
        });
    }
};
