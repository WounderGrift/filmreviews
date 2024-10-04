<?php

namespace App\Http\Helpers;

use App\Models\Game;
use SimpleXMLElement;

class RssHelper
{
    public static function create(): void
    {
        $rss = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
        <rss xmlns:yandex="http://news.yandex.ru"
             xmlns:media="http://search.yahoo.com/mrss/"
             xmlns:turbo="http://turbo.yandex.ru"
             version="2.0">
        </rss>');

        $channel = $rss->addChild("channel");
        $channel->addChild('title', config('app.app_name')
            ." - Скачать игры через торрент бесплатно на компьютер");
        $channel->addChild('link', config('app.url'));
        $channel->addChild('language', config('app.locale'));
        $channel->addChild('description',
            "Скачать торрентом игры на ПК, сайт ".config('app.app_name')." - СКАЧАТЬ ТОРРЕНТ");
        $channel->addChild('generator', "DataLife Engine");

        $games = Game::where('status', Game::STATUS_PUBLISHED)->orderBy('updated_at', 'DESC')->take(10)->get();
        foreach ($games as $game) {
            $item = $channel->addChild('item');

            $item->addAttribute('turbo', 'true');
            $item->addChild('title', $game->name);

            $item->addChild('guid', route('detail.index.uri', ['uri' => $game->uri]))
                ->addAttribute('isPermaLink', 'true');

            $item->addChild('link', route('detail.index.uri', ['uri' => $game->uri]));

            $info = htmlspecialchars(strip_tags(json_decode($game->detail?->info)->description));
            $sentences = preg_split('/(?<=[.!?])\s+/', $info, -1, PREG_SPLIT_NO_EMPTY);
            $sentences = array_map(function ($sentence) {
                return str_replace("\n", '', $sentence);
            }, $sentences);
            $resultText = trim(implode(' ', $sentences));

            $item->addChild('content', "<![CDATA[$resultText]]>",
                'http://turbo.yandex.ru');

            $categories = $game->categories()->distinct()
            ->pluck('categories.label')
            ->filter(function ($item) {
                return !empty($item);
            })->toArray();

            $item->addChild('categories', trim(implode(' / ', $categories)));
            $item->addChild('pubDate', $game->updated_at);
        }

        file_put_contents(public_path('rss.xml'), html_entity_decode($rss->asXML()), LOCK_EX);
    }
}
