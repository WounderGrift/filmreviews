<?php

use App\Models\Users;
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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('cid')->unique()->nullable();
            $table->string('name');
            $table->string('email')->unique();
            $table->enum('role', [
                Users::ROLE_OWNER,
                Users::ROLE_ADMIN,
                Users::ROLE_FREQUENTER
            ]);
            $table->string('password');
            $table->string('avatar_name')->nullable();
            $table->string('avatar_path')->nullable();
            $table->string('status')->nullable();
            $table->text('about_me')->nullable();
            $table->boolean('is_verify')->default(false);
            $table->string('timezone')->nullable();
            $table->boolean('get_letter_release')->default(false);
            $table->boolean('is_banned')->default(false);
            $table->rememberToken();
            $table->integer('last_activity')->index()->default(now()->timestamp);
            $table->timestamps();
            $table->index(['cid', 'name', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
