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
        Schema::create('download_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->foreignId('torrent_id')->constrained('torrents');
            $table->boolean('is_link');
            $table->timestamp('created_at');
            $table->index('user_id', 'idx_user_id');
            $table->index('torrent_id', 'idx_torrent_id');
            $table->index(['user_id', 'torrent_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('download_statistics');
    }
};
