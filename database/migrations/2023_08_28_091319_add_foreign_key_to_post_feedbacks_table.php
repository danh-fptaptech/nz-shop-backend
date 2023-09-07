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
        Schema::table('post_feedbacks', function (Blueprint $table) {
            $table->foreignId("post_comment_id")->constrained()->onUpdate("cascade")->onDelete("cascade");
            $table->foreignId("user_id")->constrained()->onUpdate("cascade")->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('post_feedbacks', function (Blueprint $table) {
            $table->dropForeign(["post_comment_id"]);
            $table->dropForeign(["user_id"]);
        });
    }
};
