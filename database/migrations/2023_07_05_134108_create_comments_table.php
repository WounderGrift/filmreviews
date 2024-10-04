<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_id')->constrained('users');
            $table->foreignId('whom_id')->nullable()->constrained('users');
            $table->foreignId('game_id')->constrained('game');
            $table->json('comment');
            $table->softDeletes();
            $table->timestamps();
            $table->index(['from_id', 'whom_id', 'game_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
