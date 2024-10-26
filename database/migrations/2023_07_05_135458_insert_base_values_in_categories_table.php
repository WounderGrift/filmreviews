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
        DB::table('categories')->insert([
            ['label' => 'Драма', 'url' => 'drama'],
            ['label' => 'Комедия', 'url' => 'comedy'],
            ['label' => 'Триллер', 'url' => 'thriller'],
            ['label' => 'Боевик', 'url' => 'action'],
            ['label' => 'Приключения', 'url' => 'adventure'],
            ['label' => 'Фантастика', 'url' => 'sci-fi'],
            ['label' => 'Фэнтези', 'url' => 'fantasy'],
            ['label' => 'Ужасы', 'url' => 'horror'],
            ['label' => 'Мистика', 'url' => 'mystic'],
            ['label' => 'Мелодрама', 'url' => 'romance'],
            ['label' => 'Документальный', 'url' => 'documentary'],
            ['label' => 'Криминал', 'url' => 'crime'],
            ['label' => 'Анимация', 'url' => 'animation'],
            ['label' => 'Исторический', 'url' => 'historical'],
            ['label' => 'Военный', 'url' => 'war'],
            ['label' => 'Музыкальный', 'url' => 'musical'],
            ['label' => 'Семейный', 'url' => 'family'],
            ['label' => 'Детектив', 'url' => 'detective'],
            ['label' => 'Биография', 'url' => 'biography']
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('categories')->truncate();
    }
};
