<?php

use App\Models\Game;
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
        Schema::create('game', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('uri')->unique();
            $table->string('preview_grid')->nullable();
            $table->string('date_release');
            $table->boolean('is_russian_lang');
            $table->boolean('is_weak_pc');
            $table->boolean('is_soft');
            $table->boolean('is_waiting');
            $table->enum('status', [
                Game::STATUS_UNPUBLISHED,
                Game::STATUS_PUBLISHED
            ]);
            $table->boolean('is_sponsor')->default(false);
            $table->softDeletes();
            $table->timestamps();
            $table->index(['name', 'date_release', 'uri']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game');
    }
};
