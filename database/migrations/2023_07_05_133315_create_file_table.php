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
        Schema::create('file', function (Blueprint $table) {
            $table->id();
            $table->foreignId('film_id')->constrained('film');
            $table->string('name');
            $table->string('path');
            $table->boolean('is_link');
            $table->text('additional_info')->nullable();
            $table->string('version')->nullable();
            $table->string('size');
            $table->string('source');
            $table->softDeletes();
            $table->timestamps();
            $table->index(['film_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file');
    }
};
