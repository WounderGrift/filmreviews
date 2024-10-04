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
        Schema::create('repacks', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('url');
            $table->index(['label', 'url']);
        });

        Schema::table('torrents', function (Blueprint $table) {
            $table->unsignedBigInteger('repack_id')->nullable();
            $table->foreign('repack_id')->references('id')->on('repacks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('torrents', function (Blueprint $table) {
            $table->dropForeign(['repack_id']);
            $table->dropColumn('repack_id');
        });

        Schema::dropIfExists('repacks');
    }
};
