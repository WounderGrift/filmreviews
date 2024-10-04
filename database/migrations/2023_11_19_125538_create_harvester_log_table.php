<?php

use App\Models\Harvester;
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
        Schema::create('harvester_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->nullable()->constrained('game');
            $table->string('name');
            $table->string('url')->nullable();
            $table->integer('page_count')->nullable();
            $table->string('source')->nullable();
            $table->enum('action', Harvester::getActions());
            $table->enum('status', Harvester::getStatus());
            $table->timestamps();
            $table->index(['name', 'url', 'source', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('harvester_log');
    }
};
