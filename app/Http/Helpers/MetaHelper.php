<?php

namespace App\Http\Helpers;

class MetaHelper
{
    public static function keywordsGrid($games, $categories): string
    {
        $keywords = ["repack", "репаки", "скачать игры", "скачать новые игры", "скачать", "торрент", "игры", "games",
            "дата", "выхода", "ожидаемые игры", "бесплатно", "rus", "сайт", "бесплатно", "новости", "crack", "таблетка"];

        foreach ($games as $game) {
            $keywords[] = mb_strtolower($game->name);

            foreach ($game->detail?->torrents as $torrent) {
                if ($torrent?->repacks) {
                    $label = mb_strtolower($torrent?->repacks?->label);
                    $keywords[] = "$label";
                    $keywords[] = "$label repack";
                }
            }
        }

        foreach ($categories as $category) {
            $keywords[] = $category;
        }

        return implode(", ", $keywords);
    }

    public static function keywordsDetail($game): string
    {
        $gameName = mb_strtolower($game->name);
        $keywords = ["repack", "репаки", "скачать игры", "скачать новые игры", "скачать", "торрент", "игры", "games",
            "дата", "выхода", "ожидаемые игры", "бесплатно", "rus", "сайт", "бесплатно", "новости", "crack", "таблетка",
            "скачать $gameName", "скачать бесплатно $gameName", "скачать бесплатно торрент $gameName"];

        return implode(", ", $keywords);
    }

    public static function keywordsUsually(): string
    {
        $keywords = [config('app.app_name'), "repack", "репаки", "скачать игры", "скачать новые игры", "скачать",
            "торрент", "игры", "games", "дата", "выхода", "ожидаемые игры", "бесплатно", "rus", "сайт",
            "бесплатно", "новости", "crack", "таблетка"];

        return implode(", ", $keywords);
    }

    public static function descriptionDetail($detail): string
    {
        $info = strip_tags(json_decode($detail->info)->description);
        $sentences = preg_split('/(?<=[.!?])\s+/', $info, -1, PREG_SPLIT_NO_EMPTY);
        $firstFiveSentences = array_slice($sentences, 0, 5);

        $firstFiveSentences = array_map(function ($sentence) {
            return str_replace("\n", '', $sentence);
        }, $firstFiveSentences);

        return "{$detail->game()->withTrashed()->first()->name} - " . implode(' ', $firstFiveSentences);
    }
}
