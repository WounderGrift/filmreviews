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
        Schema::create('games_categories_link', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('game');
            $table->foreignId('category_id')->constrained('categories');
            $table->index(['game_id', 'category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games_categories_link');
    }
};
