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
            ['label' => 'Аркады', 'url' => 'arcade', 'for_soft' => 0],
            ['label' => 'Экшены', 'url' => 'action', 'for_soft' => 0],
            ['label' => 'Приключения', 'url' => 'adventure', 'for_soft' => 0],
            ['label' => 'RPG Игры', 'url' => 'rpg-games', 'for_soft' => 0],
            ['label' => 'CRPG Игры', 'url' => 'crpg-games', 'for_soft' => 0],
            ['label' => 'JRPG Игры', 'url' => 'jrpg-games', 'for_soft' => 0],
            ['label' => 'Стратегии', 'url' => 'strategy', 'for_soft' => 0],
            ['label' => 'Соулсы', 'url' => 'souls', 'for_soft' => 0],
            ['label' => 'Шутеры', 'url' => 'shooter', 'for_soft' => 0],
            ['label' => 'Симуляторы', 'url' => 'simulators', 'for_soft' => 0],
            ['label' => 'Спортивные', 'url' => 'sports', 'for_soft' => 0],
            ['label' => 'Головоломки', 'url' => 'puzzle', 'for_soft' => 0],
            ['label' => 'Платформеры', 'url' => 'platformer', 'for_soft' => 0],
            ['label' => 'Песочницы', 'url' => 'sandbox', 'for_soft' => 0],
            ['label' => 'Экономические', 'url' => 'economic', 'for_soft' => 0],
            ['label' => 'Тактические', 'url' => 'tactical', 'for_soft' => 0],
            ['label' => 'Ужасы', 'url' => 'horror', 'for_soft' => 0],
            ['label' => 'Машинки', 'url' => 'racing', 'for_soft' => 0],
            ['label' => 'Ритмичные', 'url' => 'rhythm', 'for_soft' => 0],
            ['label' => 'Драки', 'url' => 'fighting', 'for_soft' => 0],
            ['label' => 'Инди', 'url' => 'indie', 'for_soft' => 0],
            ['label' => 'Открытый мир', 'url' => 'open-world', 'for_soft' => 0],
            ['label' => 'Выживалки', 'url' => 'survival', 'for_soft' => 0],
            ['label' => 'Стелс Игры', 'url' => 'stealth-games', 'for_soft' => 0],
            ['label' => 'Онлайн игры', 'url' => 'online-games', 'for_soft' => 0],
            ['label' => 'Игры на двоих', 'url' => 'split-screen', 'for_soft' => 0],
            ['label' => 'От 1-го лица', 'url' => '1st-person', 'for_soft' => 0],
            ['label' => 'От 3-го лица', 'url' => '3rd-person', 'for_soft' => 0],
            ['label' => 'Изометрия', 'url' => 'Isometric', 'for_soft' => 0],
            ['label' => 'Windows', 'url' => 'windows', 'for_soft' => 1],
            ['label' => 'Безопасность', 'url' => 'security', 'for_soft' => 1],
            ['label' => 'Графика', 'url' => 'graphic', 'for_soft' => 1],
            ['label' => 'Утилиты', 'url' => 'utilities', 'for_soft' => 1],
            ['label' => 'Интернет', 'url' => 'internet', 'for_soft' => 1],
            ['label' => 'Драйвера', 'url' => 'drivers', 'for_soft' => 1],
            ['label' => 'Текстовые', 'url' => 'office', 'for_soft' => 1],
            ['label' => 'Мультимедиа', 'url' => 'multimedia', 'for_soft' => 1],
            ['label' => 'Портативные', 'url' => 'portable', 'for_soft' => 1],
            ['label' => 'Файловые', 'url' => 'files', 'for_soft' => 1],
            ['label' => 'Видео', 'url' => 'video', 'for_soft' => 1],
            ['label' => 'Звуковые', 'url' => 'sound', 'for_soft' => 1],
            ['label' => 'Сборники', 'url' => 'wpi', 'for_soft' => 1],
            ['label' => 'Прочие', 'url' => 'others', 'for_soft' => 1],
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
