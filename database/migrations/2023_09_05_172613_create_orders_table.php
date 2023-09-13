<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('transaction_id')->nullable();
            $table->string('email_buyer');
            $table->text('items');
            $table->string('address_shipping');
            $table->string('coupon');
            $table->string('delivery');
            $table->decimal('total_value', 15, 2);
            $table->decimal('cost_of_goods', 15, 2);
            $table->decimal('profit', 15, 2);
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
