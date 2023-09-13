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
        Schema::table('reviews', function (Blueprint $table) {
             $table->foreignId("product_id")->constrained()->onUpdate("cascade")->onDelete("cascade");
             $table->foreignId("user_id")->constrained()->onUpdate("cascade")->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
             $table->dropForeign(["product_id"]);
              $table->dropForeign(["user_id"]);
        });
    }
};
