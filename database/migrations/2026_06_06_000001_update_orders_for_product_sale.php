<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('transaction_type', 20)->default('service')->after('order_id');

            // Drop existing FK constraints before modifying columns
            $table->dropForeign(['customer_id']);
            $table->dropForeign(['vehicle_id']);

            // Make nullable
            $table->string('customer_id', 20)->nullable()->change();
            $table->string('vehicle_id', 20)->nullable()->change();

            // Re-add FK constraints allowing null
            $table->foreign('customer_id')
                ->references('customer_id')
                ->on('customers')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->foreign('vehicle_id')
                ->references('vehicle_id')
                ->on('vehicles')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('transaction_type');

            $table->dropForeign(['customer_id']);
            $table->dropForeign(['vehicle_id']);

            $table->string('customer_id', 20)->nullable(false)->change();
            $table->string('vehicle_id', 20)->nullable(false)->change();

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
        });
    }
};
