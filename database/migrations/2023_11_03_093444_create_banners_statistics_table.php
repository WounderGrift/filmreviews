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
        Schema::create('banners_statistics', function(Blueprint $table) {
            $table->id();
            $table->foreignId('banner_id')->constrained('banners');
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->timestamp('created_at');
            $table->index([ 'user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners_statistics');
    }
};
