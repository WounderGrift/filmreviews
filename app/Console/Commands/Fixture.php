<?php

namespace App\Console\Commands;

use App\Http\Helpers\DateHelper;
use App\Http\Helpers\SitemapHelper;
use App\Http\Helpers\UriHelper;
use App\Models\Banners;
use App\Models\BannerStatistics;
use App\Models\Categories;
use App\Models\Comments;
use App\Models\Detail;
use App\Models\Game;
use App\Models\Repacks;
use App\Models\Screenshots;
use App\Models\Torrents;
use App\Models\Users;
use App\Models\YearReleases;
use Carbon\Carbon;
use DateTime;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class Fixture extends Command
{
    protected $signature   = 'app:fixture';
    protected $description = 'Setup fixture';

    public function handle()
    {
        $repacks = [null, 'XATAB', 'FENIXX', 'IGRUHA', 'mechanics'];

        $gameCategories = Categories::where('for_soft', 0)->get()->toArray();
        $softCategories = Categories::where('for_soft', 1)->get()->toArray();

        SitemapHelper::create();
        for ($count = 1; $count < 500; $count++) {
            $start = strtotime('2000-01-01');
            $end   = strtotime('2025-12-31');

            $randomTimestamp = mt_rand($start, $end);
            $randomDate = date('Y-m-d', $randomTimestamp);

            $is_soft = rand(0, 1);

            $categoryLabels = [];
            $categories     = [];

            for ($i = 0; $i < rand(1, 3); $i++) {
                $category = $is_soft
                    ? $softCategories[rand(0, count($softCategories) - 1)]
                    : $gameCategories[rand(0, count($gameCategories) - 1)];

                if (in_array($category["label"], $categoryLabels))
                    continue;

                $categories[] = $category;
                $categoryLabels[] = $category['label'];
            }

            $is_waiting = (int)($randomDate > date('Y-m-d'));
            $uri = UriHelper::convertToUriWhileUnique('game' . implode(',', $categoryLabels) . $count);
            $is_sponsor = rand(1, 10) <= 3 ? 1 : 0;

            $this->info("Create $uri");
            mkdir(storage_path("app/public/games/$uri/previewGrid"), 0755, true);
            copy(public_path('fixture/preview_grid_photo.jpg'),
                storage_path("app/public/games/$uri/previewGrid/preview_grid_photo.png"));

            $statusInt = rand(0, 2);
            $status = Game::STATUS_UNPUBLISHED;
            if ($statusInt == 1)
                $status = Game::STATUS_PUBLISHED;

            $game = Game::query()->firstOrCreate([
                'name' => 'game' . implode(',', $categoryLabels) . $count
            ], [
                'name' => 'game' . implode(',', $categoryLabels) . $count,
                'uri' => $uri,
                'date_release' => DateHelper::dateFormatterForDateReleaseView($randomDate),
                'preview_grid' => "games/$uri/previewGrid/preview_grid_photo.png",
                'is_waiting' => $is_waiting,
                'is_russian_lang' => rand(0, 1),
                'is_sponsor' => $is_sponsor,
                'is_soft' => $is_soft,
                'status' => $status,
                'is_weak_pc' => rand(0, 1),
            ]);

            if (!$is_sponsor) {
                if (!$is_soft) {

                    if (!$is_waiting && $status == Game::STATUS_PUBLISHED) {
                        $date = new DateTime($randomDate);
                        $year = $date->format('Y');

                        YearReleases::query()->firstOrCreate([
                            'year' => $year
                        ], [
                            'year' => $year
                        ]);
                    }

                    $countTorrent = rand(1, 3);
                    for ($i = 0; $i < $countTorrent; $i++) {
                        $repackInt = array_rand($repacks);
                        $label = $repacks[$repackInt];

                        if (!$is_waiting && !is_null($label)) {

                            $gameRepacks = Repacks::query()->firstOrCreate([
                                'label' => $label
                            ], [
                                'label' => $label,
                                'url' => mb_strtolower($label)
                            ]);
                        }

                        $version = 'v ' . rand(0, 10) . '.' . rand(0, 10) . ' + все DLC';
                        $pathName = $this->createTorrentFile($version, $uri);

                        Torrents::query()->create([
                            'game_id' => $game->id,
                            'repack_id' => isset($gameRepacks) ? $gameRepacks->id : null,
                            'name' => "$uri-$version.torrent",
                            'version' => $version,
                            'size' => rand(0, 10) . '.' . rand(0, 10) . ' ГБ',
                            'is_link' => 0,
                            'source' => 'fixture',
                            'path' => $pathName,
                            'additional_info' => json_encode(
                                "<h3>Сюжетная линия</h3>
                                <p>Любителям отличной графики, проработанной истории и не совсем типичного,
                                 черного юмора, да и всем другим, кто решит поиграть в этот усовершенствованный выпуск проекта,
                                 добро пожаловать! Здесь вам предстоит встретиться со всеми полюбившимися персонажами,
                                 однако не стоит ожидать обычного и уже приевшегося поведения, ведь их характеры будут отличаться.</p>"
                            , JSON_UNESCAPED_UNICODE),
                        ]);
                    }
                } else {
                    $version = 'v ' . rand(0, 10) . '.' . rand(0, 10) . ' + все DLC';
                    $pathName = $this->createTorrentFile($version, $uri);

                    Torrents::query()->create([
                        'game_id' => $game->id,
                        'repack_id' => isset($gameRepacks) ? $gameRepacks->id : null,
                        'name' => "$uri-$version.torrent",
                        'version' => $version,
                        'size' => rand(0, 10) . '.' . rand(0, 10) . ' ГБ',
                        'is_link' => 0,
                        'source' => 'fixture',
                        'path' => $pathName,
                        'additional_info' => json_encode(
                            "<h3>Сюжетная линия</h3>
                            <p>Любителям отличной графики, проработанной истории и не совсем типичного,
                             черного юмора, да и всем другим, кто решит поиграть в этот усовершенствованный выпуск проекта,
                             добро пожаловать! Здесь вам предстоит встретиться со всеми полюбившимися персонажами,
                            однако не стоит ожидать обычного и уже приевшегося поведения, ведь их характеры будут отличаться.</p>"
                        , JSON_UNESCAPED_UNICODE),
                    ]);
                }
            } else {
                $version = 'v ' . rand(0, 10) . '.' . rand(0, 10) . ' + все DLC';

                Torrents::query()->create([
                    'game_id' => $game->id,
                    'repack_id' => isset($gameRepacks) ? $gameRepacks->id : null,
                    'name' => "$uri-$version.torrent",
                    'version' => $version,
                    'size' => rand(0, 10) . '.' . rand(0, 10) . ' ГБ',
                    'is_link' => 1,
                    'source' => 'fixture',
                    'path' => 'https://web.telegram.org/a/#-1897665344',
                    'additional_info' => json_encode(
                        "<h3>Сюжетная линия</h3>
                        <p>Любителям отличной графики, проработанной истории и не совсем типичного,
                         черного юмора, да и всем другим, кто решит поиграть в этот усовершенствованный выпуск проекта,
                         добро пожаловать! Здесь вам предстоит встретиться со всеми полюбившимися персонажами,
                        однако не стоит ожидать обычного и уже приевшегося поведения, ведь их характеры будут отличаться.</p>"
                    , JSON_UNESCAPED_UNICODE),
                ]);
            }

            foreach ($categories as $category)
                $game->categories()->attach($category['id']);

            $screen = [2,3,4];
            foreach ($screen as $item) {
                if (!is_dir(storage_path("app/public/games/$uri/screenshots")))
                    mkdir(storage_path("app/public/games/$uri/screenshots"), 0755, true);
                copy(public_path('fixture/preview_detail_screenshot.png'),
                    storage_path("app/public/games/$uri/screenshots/preview_detail_screenshot$item.png"));

                Screenshots::query()->create([
                    'game_id' => $game->id,
                    'path' => "games/$uri/screenshots/preview_detail_screenshot$item.png"
                ]);
            }

            $this->recordDetail($game, $uri);
            SitemapHelper::add($uri);
        }

        $this->info('Fixtures added.');

        if (false)
        {
            $this->info('Emulation Of Website Activity.');
            self::emulationOfWebsiteActivity();
            $this->info('Emulation Of Website Activity Ended.');
        }
    }

    public function recordDetail($game, $uri)
    {
        $info = [
            'summary' => [
                'Разработчик:' => 'Bethesda Game Studios',
                'Издатель:' => 'Bethesda Softworks',
                'Тип издания:' => 'RePack',
                'Язык интерфейса:' => 'Русский, Английский',
                'Язык озвучки:' => 'Русский, Английский',
                'Таблетка:' => 'Вшита (CODEX)',
            ],
            'system' => [
                'min' => [
                    'ОС:' => 'Windows 7 / 8 / 10 (64-bit)',
                    'Процессор:' => 'Core i3-2310M CPU @ 2.10GHz',
                    'Оперативная память:' => '6 GB',
                    'Видеокарта:' => 'Intel HD Graphics 620',
                    'Место на диске:' => '25 GB',
                ],
                'max' => [
                    'ОС:' => 'Windows 7 / 8 / 10 (64-bit)',
                    'Процессор:' => 'Core i3-2310M CPU @ 2.10GHz',
                    'Оперативная память:' => '6 GB',
                    'Видеокарта:' => 'Intel HD Graphics 620',
                    'Место на диске:' => '25 GB',
                ]
            ],
            'description' =>
                "<p class='snglp'>Сейчас на просторах интерактивного рынка можно найти большое количество проектов, появившихся совсем недавно. Не обошли мы вниманием игровой проект Pathfinder Wrath of the Righteous. Он выполнен в качестве продолжения одноименной РПГ. Игровые события перекинут вас в другую часть города. Тут открылся новый портал, ведущий в неизвестность. Это просто огромная бездна, поражающая всех своими масштабами и мощью. И неведомым образом в нашем мире стали появляться демонические создания, а значит вашим заданием будет устранение их. Для выживания нужно потратить немало сил и времени. Отныне вы должны будете проходить максимально сложные задания и стараться закрыть этот портал. Изначально вы должны подобрать себе оптимального игрового героя. Именно его умения станут определяющими для прохождения игры. Кроме всего выше перечисленного, нужно указать на необходимость изучения местности, общение с коренным населением, а самое основное – выполнение большого количества заданий. Поэтому, если вы хотите узнать больше об этой игре, рекомендуем посетить наше игровое хранилище, чтобы там скачать торрент Pathfinder Wrath of the Righteous совершенно бесплатно. Вы станете главным персонажем, который должен дать равный бой истинному злу.</p>
                 <p class='snglp'>Геймерам будут начисляться не только игровые баллы, но и мощные артефакты, которые точно понадобятся вам в будущем. Нужно также сказать о том, что геймеры должны найти себе снаряжение и инновационное вооружение. Не забывайте постоянно улучшать характеристики своего персонажа, чтобы у него хватило сил уничтожить истинное зло. Если опыта будет достаточно, вы можете вступить в противостояние с опасными главарями и получить награду за победу. Также можно стать участником настоящей войны. Игра представлена с наличием качественной сюжетной линии, предложены тематические изображения и видео. Из-за того, что авторы немало потрудились над проектом, игроки могут получить удовольствие от качественной озвучки. Правдоподобность всего происходящего понравится всем геймерам. В ходе игрового процесса вы должны быть очень осмотрительны и аккуратны, чтобы получить больше возможностей для положительного исхода. Вы получите возможность насладиться ярким продолжением с большим количеством новшеств. Продвигаясь по сюжетной линии, геймеров ждет большое количество преград и вознаграждений за выполнение миссий. Теперь вы сможете наблюдать обновленную физику и механику игры, что существенно упрощает геймплей. Также авторы приложили силы и для визуализации, теперь картинка смотрится интересно, с большим количеством деталей на ней.</p>
                 <p class='snglp'>На этой странице по кнопке ниже вы можете скачать Pathfinder: Wrath of the Righteous через торрент бесплатно.</p>",
        ];

        mkdir(storage_path("app/public/games/$uri/previewDetail"), 0755, true);
        copy(public_path("fixture/preview_detail_screenshot.png"),
            storage_path("app/public/games/$uri/previewDetail/preview_detail_screenshot.png"));

        mkdir(storage_path("app/public/games/$uri/previewTrailer"), 0755, true);
        copy(public_path("fixture/trailer_detail.jpg"),
            storage_path("app/public/games/$uri/previewTrailer/trailer_detail.png"));

        Detail::query()->create([
            'id' => $game->id,
            'info' => json_encode($info, JSON_UNESCAPED_UNICODE),
            'preview_detail'  => "games/$uri/previewDetail/preview_detail_screenshot.png",
            'preview_trailer' => "games/$uri/previewTrailer/trailer_detail.png",
            'trailer_detail'  => "https://www.youtube-nocookie.com/embed/Qj5rGh-ww7M",
        ]);
    }

    public static function createTorrentFile(string $version, string $uri): ?string
    {
        $newFilename = "games/$uri/torrent/$version";
        if (!is_dir(storage_path("app/public/games/$uri/torrent")))
            mkdir(storage_path("app/public/games/$uri/torrent"), 0755, true);

        $filePath = storage_path("app/public/games/$uri/torrent/$version");

        $counter = 1;
        while (file_exists($filePath)) {
            $filePath = storage_path("app/public/games/$uri/torrent/$version-$counter");
            $counter++;
        }

        copy(public_path('fixture/file.torrent'), $filePath);
        return $newFilename;
    }

    public static function emulationOfWebsiteActivity() {
        $users = Users::latest('id')->first();
        if (!$users || $users->id < 500) {
            for ($count = $users?->id ?? 0; $count < 500; $count++) {
                $now = Carbon::now();
                $randomDays = mt_rand(1, 1825);
                $randomDate = $now->subDays($randomDays);
                $formattedDate = $randomDate->format('Y-m-d H:i:s');

                var_dump('user' . $count . '@gmailer.com');
                $user = Users::query()->create([
                    'cid' => Str::random(12),
                    'name' => 'User' . $count,
                    'email' => 'user' . $count . '@gmailer.com',
                    'role' => Users::ROLE_FREQUENTER,
                    'password' => Hash::make(Str::random(12)),
                    'status' => Str::random(15),
                    'about_me' => Str::random(36),
                    'is_verify' => rand(0, 1),
                    'timezone' => 'Asia/Tbilisi',
                    'get_letter_release' => rand(0, 1),
                    'is_banned' => rand(0, 1),
                    'last_activity' => 1699113556,
                ]);

                $user->created_at = $formattedDate;
                $user->save();
            }
        }

        $comments = Comments::latest('id')->first();
        if (!$comments || $comments->id < 500) {
            for ($count = $comments?->id ?? 0; $count < 500; $count++) {
                $now = Carbon::now();
                $randomDays = mt_rand(1, 1825);
                $randomDate = $now->subDays($randomDays);
                $formattedDate = $randomDate->format('Y-m-d H:i:s');

                var_dump('comment' . $count);

                $randomUser = Users::inRandomOrder()->first();
                $randomGame = Game::inRandomOrder()->where('status', Game::STATUS_UNPUBLISHED)->first();

                $quote = rand(0, 1) == 1;

                if ($quote) {
                    $quoteComment = Comments::inRandomOrder()->where('game_id', $randomGame->id)->first();
                    if (!$quoteComment)
                        $quote = false;
                    else
                        $text = json_decode($quoteComment->comment);
                }

                $comment = Comments::query()->create([
                    'from_id' => $randomUser->id,
                    'whom_id' => $quote ? $randomUser->id : null,
                    'game_id' => $randomGame->id,
                    'comment' => json_encode([
                        'quote'   => isset($text) ? $text->comment : '',
                        'comment' => Str::random(36),
                    ], JSON_UNESCAPED_UNICODE),
                ]);

                $comment->created_at = $formattedDate;
                $comment->save();
            }
        }

        for ($count = 0; $count < 500; $count++) {
            $now = Carbon::now();
            $randomDays = mt_rand(1, 1825);
            $randomDate = $now->subDays($randomDays);
            $formattedDate = $randomDate->format('Y-m-d H:i:s');

            var_dump('bannersJump' . $count);

            $randomUser   = Users::inRandomOrder()->first();
            $randomBanner = Banners::inRandomOrder()->first();

            $banner = BannerStatistics::query()->create([
                'banner_id' => $randomBanner->id,
                'user_id' => $randomUser->id,
            ]);

            $banner->created_at = $formattedDate;
            $banner->save();
        }

    }
}
