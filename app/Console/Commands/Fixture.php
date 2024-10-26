<?php

namespace App\Console\Commands;

use App\Http\Helpers\DateHelper;
use App\Http\Helpers\UriHelper;
use App\Models\Banners;
use App\Models\BannerStatistics;
use App\Models\Categories;
use App\Models\Comments;
use App\Models\Detail;
use App\Models\Film;
use App\Models\Screenshots;
use App\Models\File;
use App\Models\Users;
use App\Models\YearReleases;
use Carbon\Carbon;
use DateTime;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class Fixture extends Command
{
    protected $signature = 'app:fixture';
    protected $description = 'Setup fixture';

    public function handle()
    {
        $filmCategories = Categories::query()->get()->toArray();

        for ($count = 1; $count < 500; $count++) {
            $start = strtotime('2000-01-01');
            $end = strtotime('2025-12-31');

            $randomTimestamp = mt_rand($start, $end);
            $randomDate = date('Y-m-d', $randomTimestamp);

            $categoryLabels = [];
            $categories = [];

            for ($i = 0; $i < rand(1, 3); $i++) {
                $category = $filmCategories[rand(0, count($filmCategories) - 1)];

                if (in_array($category["label"], $categoryLabels))
                    continue;

                $categories[] = $category;
                $categoryLabels[] = $category['label'];
            }

            $is_waiting = (int)($randomDate > date('Y-m-d'));
            $uri = UriHelper::convertToUriWhileUnique('film' . implode(',', $categoryLabels) . $count);
            $is_sponsor = 0;

            $this->info("Create $uri");
            mkdir(storage_path("app/public/films/$uri/previewGrid"), 0755, true);
            copy(public_path('fixture/preview_grid_photo.jpg'),
                storage_path("app/public/films/$uri/previewGrid/preview_grid_photo.png"));

            $statusInt = rand(0, 2);
            $status = Film::STATUS_UNPUBLISHED;
            if ($statusInt == 1)
                $status = Film::STATUS_PUBLISHED;

            $film = Film::query()->firstOrCreate([
                'name' => 'film' . implode(',', $categoryLabels) . $count
            ], [
                'name' => 'film' . implode(',', $categoryLabels) . $count,
                'uri' => $uri,
                'date_release' => DateHelper::dateFormatterForDateReleaseView($randomDate),
                'preview_grid' => "films/$uri/previewGrid/preview_grid_photo.png",
                'is_waiting' => $is_waiting,
                'is_russian_lang' => rand(0, 1),
                'is_sponsor' => $is_sponsor,
                'status' => $status,
                'is_weak_pc' => rand(0, 1),
            ]);

            if (!$is_sponsor) {
                if (!$is_waiting && $status == Film::STATUS_PUBLISHED) {
                    $date = new DateTime($randomDate);
                    $year = $date->format('Y');

                    YearReleases::query()->firstOrCreate([
                        'year' => $year
                    ], [
                        'year' => $year
                    ]);
                }

                $countFiles = rand(1, 3);
                for ($i = 0; $i < $countFiles; $i++) {
                    $version = 'v ' . rand(0, 10) . '.' . rand(0, 10);
                    $pathName = $this->createFile($version, $uri);

                    File::query()->create([
                        'film_id' => $film->id,
                        'name' => "$uri-$version.txt",
                        'version' => $version,
                        'size' => rand(0, 10) . '.' . rand(0, 10) . ' ГБ',
                        'is_link' => 0,
                        'source' => 'fixture',
                        'path' => $pathName,
                        'additional_info' => json_encode(
                            "В центре сюжета — детектив Александра Мартынова, который получает странное дело о
                             серии загадочных исчезновений в небольшом российском городке. Исследуя запутанные улики,
                              он натыкается на древнюю тайну, связанную с потерянным артефактом, который может открывать
                               порталы в параллельные миры.
                            По мере расследования Александр обнаруживает, что каждое исчезновение связано с людьми,
                            которые каким-то образом были связаны с его прошлым. Преследуемый собственными демонами
                            и воспоминаниями, он вынужден столкнуться с темными моментами своей жизни и выбрать,
                             готов ли он пожертвовать всем ради раскрытия правды.
                             Сюжет насыщен напряженными поворотами, мрачной атмосферой и философскими вопросами о том,
                              как наши прошлые выборы формируют наше настоящее."
                            , JSON_UNESCAPED_UNICODE),
                    ]);
                }
            } else {
                $version = 'v ' . rand(0, 10) . '.' . rand(0, 10);

                File::query()->create([
                    'film_id' => $film->id,
                    'name' => "$uri-$version.txt",
                    'version' => $version,
                    'size' => rand(0, 10) . '.' . rand(0, 10) . ' ГБ',
                    'is_link' => 1,
                    'source' => 'fixture',
                    'path' => 'https://web.telegram.org',
                    'additional_info' => json_encode(
                        "В центре сюжета — детектив Александра Мартынова, который получает странное дело о
                             серии загадочных исчезновений в небольшом российском городке. Исследуя запутанные улики,
                              он натыкается на древнюю тайну, связанную с потерянным артефактом, который может открывать
                               порталы в параллельные миры.
                            По мере расследования Александр обнаруживает, что каждое исчезновение связано с людьми,
                            которые каким-то образом были связаны с его прошлым. Преследуемый собственными демонами
                            и воспоминаниями, он вынужден столкнуться с темными моментами своей жизни и выбрать,
                             готов ли он пожертвовать всем ради раскрытия правды.
                             Сюжет насыщен напряженными поворотами, мрачной атмосферой и философскими вопросами о том,
                              как наши прошлые выборы формируют наше настоящее."
                        , JSON_UNESCAPED_UNICODE),
                ]);
            }

            foreach ($categories as $category)
                $film->categories()->attach($category['id']);

            $screen = [2, 3, 4];
            foreach ($screen as $item) {
                if (!is_dir(storage_path("app/public/films/$uri/screenshots")))
                    mkdir(storage_path("app/public/films/$uri/screenshots"), 0755, true);
                copy(public_path('fixture/preview_detail_screenshot.png'),
                    storage_path("app/public/films/$uri/screenshots/preview_detail_screenshot$item.png"));

                Screenshots::query()->create([
                    'film_id' => $film->id,
                    'path' => "films/$uri/screenshots/preview_detail_screenshot$item.png"
                ]);
            }

            $this->recordDetail($film, $uri);
        }

        $this->info('Fixtures added.');

        if (false) {
            $this->info('Emulation Of Website Activity.');
            self::emulationOfWebsiteActivity();
            $this->info('Emulation Of Website Activity Ended.');
        }
    }

    public function recordDetail($film, $uri)
    {
        $info = [
            'summary' => [
                'Режиссёр:' => 'Иван Петров',
                'Сценарист:' => 'Елена Смирнова',
                'Продюсер:' => 'Александр Волков',
                'Александр Мартынов' => 'Главный детектив, который расследует исчезновения.',
                'Мария Иванова' => 'Секретарша, которая помогает детективу в его деле.',
                'Николай Петров' => 'Антагонист',
                'Язык оригинала:' => 'Русский'
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
            'description' => "В центре сюжета — детектив Александра Мартынова, который получает странное дело о
                             серии загадочных исчезновений в небольшом российском городке. Исследуя запутанные улики,
                              он натыкается на древнюю тайну, связанную с потерянным артефактом, который может открывать
                               порталы в параллельные миры.
                            По мере расследования Александр обнаруживает, что каждое исчезновение связано с людьми,
                            которые каким-то образом были связаны с его прошлым. Преследуемый собственными демонами
                            и воспоминаниями, он вынужден столкнуться с темными моментами своей жизни и выбрать,
                             готов ли он пожертвовать всем ради раскрытия правды.
                             Сюжет насыщен напряженными поворотами, мрачной атмосферой и философскими вопросами о том,
                              как наши прошлые выборы формируют наше настоящее.",
        ];

        mkdir(storage_path("app/public/films/$uri/previewDetail"), 0755, true);
        copy(public_path("fixture/preview_detail_screenshot.png"),
            storage_path("app/public/films/$uri/previewDetail/preview_detail_screenshot.png"));

        mkdir(storage_path("app/public/films/$uri/previewTrailer"), 0755, true);
        copy(public_path("fixture/trailer_detail.jpg"),
            storage_path("app/public/films/$uri/previewTrailer/trailer_detail.png"));

        Detail::query()->create([
            'id' => $film->id,
            'info' => json_encode($info, JSON_UNESCAPED_UNICODE),
            'preview_detail' => "films/$uri/previewDetail/preview_detail_screenshot.png",
            'preview_trailer' => "films/$uri/previewTrailer/trailer_detail.png",
            'trailer_detail' => "https://www.youtube-nocookie.com/embed/Qj5rGh-ww7M",
        ]);
    }

    public static function createFile(string $version, string $uri): ?string
    {
        $newFilename = "films/$uri/files/$version";
        if (!is_dir(storage_path("app/public/films/$uri/files")))
            mkdir(storage_path("app/public/films/$uri/files"), 0755, true);

        $filePath = storage_path("app/public/films/$uri/files/$version");

        $counter = 1;
        while (file_exists($filePath)) {
            $filePath = storage_path("app/public/films/$uri/files/$version-$counter");
            $counter++;
        }

        copy(public_path('fixture/file.txt'), $filePath);
        return $newFilename;
    }

    public static function emulationOfWebsiteActivity()
    {
        $users = Users::query()->latest('id')->first();
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

        $comments = Comments::query()->latest('id')->first();
        if (!$comments || $comments->id < 500) {
            for ($count = $comments?->id ?? 0; $count < 500; $count++) {
                $now = Carbon::now();
                $randomDays = mt_rand(1, 1825);
                $randomDate = $now->subDays($randomDays);
                $formattedDate = $randomDate->format('Y-m-d H:i:s');

                var_dump('comment' . $count);

                $randomUser = Users::query()->inRandomOrder()->first();
                $randomFilm = Film::query()->inRandomOrder()
                    ->where('status', Film::STATUS_UNPUBLISHED)->first();

                $quote = rand(0, 1) == 1;

                if ($quote) {
                    $quoteComment = Comments::query()->inRandomOrder()->where('film_id', $randomFilm->id)->first();
                    if (!$quoteComment)
                        $quote = false;
                    else
                        $text = json_decode($quoteComment->comment);
                }

                $comment = Comments::query()->create([
                    'from_id' => $randomUser->id,
                    'whom_id' => $quote ? $randomUser->id : null,
                    'file_id' => $randomFilm->id,
                    'comment' => json_encode([
                        'quote' => isset($text) ? $text->comment : '',
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

            $randomUser = Users::query()->inRandomOrder()->first();
            $randomBanner = Banners::query()->inRandomOrder()->first();

            $banner = BannerStatistics::query()->create([
                'banner_id' => $randomBanner->id,
                'user_id' => $randomUser->id,
            ]);

            $banner->created_at = $formattedDate;
            $banner->save();
        }

    }
}
