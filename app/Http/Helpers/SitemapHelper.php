<?php

namespace App\Http\Helpers;

use App\Models\Categories;
use App\Models\Series;
use App\Models\YearReleases;
use SimpleXMLElement;

class SitemapHelper
{
    public static function create(): void
    {
        if (file_exists(public_path('sitemap.xml')))
            return;

        $sitemap = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
            <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
              xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
              xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9"
              xml:lang="ru"></urlset>');
        $siteUrl = config('app.url');

        $categoriesGame = Categories::where('for_soft', 0)->get();
        $baseUrls = ['/all', '/new', '/waiting', '/russian', '/weak', '/repacks'];

        $gamesCategoriesUrls = [];
        foreach ($categoriesGame as $category) {
            $gamesCategoriesUrls[] = "/$category->url";
        }

        $allUrls = [];
        foreach ($baseUrls as $baseUrl) {
            $allUrls[] = $baseUrl;
            foreach ($gamesCategoriesUrls as $gamesCategoriesUrl) {
                $allUrls[] = $baseUrl . $gamesCategoriesUrl;
            }
        }

        $series = Series::get();
        foreach($series as $serie) {
            $allUrls[] = "/series/$serie->uri";
        }

        $categoriesSoft = Categories::where('for_soft', 1)->get();
        foreach ($categoriesSoft as $category) {
            $allUrls[] = "/soft/$category->url";
        }

        $yearsGame = YearReleases::all();
        foreach ($yearsGame as $year) {
            $allUrls[] = "/year/$year->year";
        }

        array_push($allUrls, '/', '/feedback', '/recommended', '/series');
        foreach ($allUrls as $allUrl) {
            $urlElement = $sitemap->addChild('url');
            $urlElement->addChild('loc', $siteUrl . $allUrl);
            $urlElement->addChild('lastmod', date('Y-m-d'));
            $urlElement->addChild('priority', '1');
        }

        file_put_contents(public_path('sitemap.xml'), $sitemap->asXML(), LOCK_EX);
    }

    public static function add(string $url): void
    {
        if (!file_exists(public_path('sitemap.xml')))
            self::create();

        $xmlString = file_get_contents(public_path('sitemap.xml'));
        $sitemap   = simplexml_load_string($xmlString);
        $siteUrl   = config('app.url');

        $newElement = $sitemap->addChild('url');
        $newElement->addChild('loc', "$siteUrl/$url");
        $newElement->addChild('lastmod', date('Y-m-d'));
        $newElement->addChild('priority', '1');

        file_put_contents(public_path('sitemap.xml'), $sitemap->asXML(), LOCK_EX);
    }

    public static function update(string $urlToUpdate, string $urlNew): void
    {
        if (!file_exists(public_path('sitemap.xml')))
            return;

        $xmlString = file_get_contents(public_path('sitemap.xml'));
        $sitemap   = simplexml_load_string($xmlString);

        $urlToUpdate = config('app.url') . "/$urlToUpdate";

        foreach ($sitemap->url as $url) {
            if ((string)$url->loc === $urlToUpdate) {
                if ($urlNew)
                    $url->loc = $urlNew;
                $url->lastmod = date('Y-m-d');

                file_put_contents(public_path('sitemap.xml'), $sitemap->asXML(), LOCK_EX);
                break;
            }
        }
    }

    public static function delete(string $urlToDelete): void
    {
        if (!file_exists(public_path('sitemap.xml')))
            return;

        $sitemapPath = public_path('sitemap.xml');
        $sitemap = simplexml_load_file($sitemapPath);

        $newSitemap = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" ?>
            <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');

        $urlToDelete = config('app.url') . "/$urlToDelete";

        foreach ($sitemap->url as $url) {
            if ((string)$url->loc !== $urlToDelete) {
                $newUrl = $newSitemap->addChild('url');
                $newUrl->addChild('loc', (string)$url->loc);
                $newUrl->addChild('lastmod', (string)$url->lastmod);
                $newUrl->addChild('priority', (string)$url->priority);
            }
        }

        file_put_contents($sitemapPath, $newSitemap->asXML(), LOCK_EX);
    }
}
