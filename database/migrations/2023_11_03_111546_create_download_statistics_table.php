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
            $table->foreignId('file_id')->constrained('file');
            $table->boolean('is_link');
            $table->timestamp('created_at');
            $table->index('user_id', 'idx_user_id');
            $table->index('file_id', 'idx_file_id');
            $table->index(['user_id', 'file_id', 'created_at']);
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
