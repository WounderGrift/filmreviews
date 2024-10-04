<?php

namespace App\Http\Helpers;

use App\Models\Game;
use App\Models\Series;
use App\Models\YearReleases;
use Illuminate\Support\Str;

class UriHelper
{
    const TRANSLIT = [
        'а'=>'a', 'б'=>'b', 'в'=>'v', 'г'=>'g', 'д'=>'d', 'е'=>'e', 'ё'=>'yo', 'ж'=>'zh', 'з'=>'z', 'и'=>'i',
        'й'=>'y', 'к'=>'k', 'л'=>'l', 'м'=>'m', 'н'=>'n', 'о'=>'o', 'п'=>'p', 'р'=>'r', 'с'=>'s', 'т'=>'t',
        'у'=>'u', 'ф'=>'f', 'х'=>'h', 'ц'=>'ts', 'ч'=>'ch', 'ш'=>'sh', 'щ'=>'shch', 'ъ'=>'', 'ы'=>'y', 'ь'=>'',
        'э'=>'e', 'ю'=>'yu', 'я'=>'ya', ' '=>'-', ',' => '-', ', ' => '-'
    ];

    public static function convertToUriWhileUnique(string $title, int $gameId = 0): string
    {
        $text = mb_strtolower($title, 'UTF-8');
        $uri  = preg_replace('/[^a-z0-9-]/', '', strtr($text, self::TRANSLIT));
        $uri  = trim($uri, '-');
        $uri  = preg_replace('/-+/', '-', $uri);

        $existingUri = Game::withTrashed()->where('uri', $uri)
            ->where('id', '!=', $gameId)->value('uri');

        if ($uri === $existingUri) {
            $suffix = '';

            do {
                $suffix .= Str::random(1);
                $newUri = $uri . '-' . $suffix;
            } while (Game::withTrashed()->where('uri', $newUri)->exists());

            $uri = $newUri;
        }

        return trim($uri, '-');
    }

    public static function convertToUriSeriesWhileUnique(string $title, int $seriesId = 0): string
    {
        $text = mb_strtolower($title, 'UTF-8');
        $uri  = preg_replace('/[^a-z0-9-]/', '', strtr($text, self::TRANSLIT));
        $uri  = trim($uri, '-');
        $uri  = preg_replace('/-+/', '-', $uri);

        $existingUri = Series::withTrashed()->where('uri', $uri)
            ->where('id', '!=', $seriesId)->value('uri');

        if ($uri === $existingUri) {
            $suffix = '';

            do {
                $suffix .= Str::random(1);
                $newUri = $uri . '-' . $suffix;
            } while (Game::withTrashed()->where('uri', $newUri)->exists());

            $uri = $newUri;
        }

        return trim($uri, '-');
    }

    public static function convertToUri(string $title): string
    {
        $text = mb_strtolower($title, 'UTF-8');
        $uri  = preg_replace('/[^a-z0-9-]/', '', strtr($text, self::TRANSLIT));
        $uri  = trim($uri, '-');
        $uri  = preg_replace('/-+/', '-', $uri);

        return trim($uri, '-');
    }

    public static function yearForMenu(): string
    {
        $yearsInBd = YearReleases::query()->orderBy('year', 'DESC')->pluck('year')->toArray();
        foreach ($yearsInBd as $year) {
            $gamesCount = Game::query()->whereRaw("YEAR(STR_TO_DATE(date_release, '%d %M %Y')) = ?", [$year])
                ->where('is_soft', 0)
                ->where('is_waiting', 0)
                ->where('status', Game::STATUS_PUBLISHED)
                ->exists();

            if ($gamesCount) {
                return $year;
            }
        }

        return date('Y');
    }

    public static function getSourceInUrlInOwnerPanel(): string
    {
        $currentUrl = url()->current();
        $parsedUrl  = parse_url($currentUrl);
        $parsedUrl  = explode("/", $parsedUrl["path"]);

        return $parsedUrl[3];
    }
}
