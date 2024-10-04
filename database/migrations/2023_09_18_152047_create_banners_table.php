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
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('banner_path');
            $table->string('banner_name');
            $table->enum('type', [
                'big_banner_menu',
                'detail_banner',
                'basement_banner'
            ]);
            $table->enum('media_type', [
                'image',
                'video'
            ]);
            $table->integer('position')->default(1);
            $table->string('href')->nullable();
            $table->boolean('active')->default(false);
            $table->softDeletes();
            $table->timestamps();
            $table->index(['type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
