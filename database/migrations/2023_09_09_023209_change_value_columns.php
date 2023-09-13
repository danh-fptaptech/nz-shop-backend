<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->text('address_shipping')->change();
            $table->text('coupon')->nullable()->change();
            $table->decimal('total_value', 15, 2)->nullable()->change();
            $table->decimal('cost_of_goods', 15, 2)->nullable()->change();
            $table->decimal('profit', 15, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            //
        });
    }
};
