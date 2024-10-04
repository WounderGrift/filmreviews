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
        Schema::create('likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('game');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('comment_id')->nullable()->constrained('comments');
            $table->timestamp('created_at');
            $table->index(['id', 'user_id', 'game_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('likes');
    }
};
