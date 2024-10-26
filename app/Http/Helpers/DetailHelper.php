<?php

namespace App\Http\Helpers;

use App\Models\Categories;
use App\Models\Detail;
use App\Models\YearReleases;
use DateTime;
use Exception;
use Illuminate\Support\Facades\Storage;

class DetailHelper
{
    public static function checkRussianLanguage(array $summary): bool
    {
        if (!empty($summary['Язык озвучки:'])) {
            $langs = explode(',', $summary['Язык озвучки:']);
            foreach ($langs as $lang)
            {
                $lang = trim($lang);
                if ($lang == 'Русский')
                    return 1;
            }
        }

        if (!empty($summary['Язык интерфейса:'])) {
            $langs = explode(',', $summary['Язык интерфейса:']);
            foreach ($langs as $lang)
            {
                $lang = trim($lang);
                if ($lang == 'Русский')
                    return 1;
            }
        }

        return 0;
    }

    public static function getIdsCategories($categories): array
    {
        $categories = explode(', ', trim(str_replace('null', null, $categories)));
        $ids = [];
        foreach ($categories as $category)
            $ids[] = Categories::query()->where(['label' => $category])->value('id');
        return $ids;
    }

    public static function addYear($year): void
    {
        try {
            $date = new DateTime($year);
            $year = $date->format('Y');
            YearReleases::query()->firstOrCreate([
                'year' => $year
            ]);

        } catch (Exception $e) {
            return;
        }
    }

    public static function getFilmPreviews(?string $pathPreview): array
    {
        if (!$pathPreview)
            return [];

        $needPath = explode('/', "$pathPreview");
        array_pop($needPath);
        $needPath = implode('/', $needPath);

        $pathFiles = [];
        foreach (Storage::disk('public')->files($needPath) as $file) {
            $pathPreview = explode('/', $file);
            $pathFiles[] = $pathPreview[count($pathPreview) - 1];
        }

        return $pathFiles;
    }

    public static function getCategoriesForView(Detail $detail): string
    {
        $labels = $detail->categories->pluck('label')->all();

        $categoriesView = '';
        foreach ($labels as $index => $label) {
            if ($index == 0)
                $categoriesView .= "$label";
            else {
                if ($index % 2 == 0)
                    $categoriesView .= ",<br>$label";
                else
                    $categoriesView .= ", $label";
            }
        }

        return $categoriesView;
    }

    public static function getSeparatedSummary(string $text): string
    {
        $text = explode(', ', $text);

        $textView = '';
        foreach ($text as $index => $label) {
            if ($index == 0)
                $textView .= "$label";
            else {
                if ($index % 2 == 0)
                    $textView .= ",<br>$label";
                else
                    $textView .= ", $label";
            }
        }

        return $textView;
    }

    public static function getExtraScreenshots(Detail $detail, ?string $pathPreview): array
    {
        if (!$pathPreview)
            return [];

        $needPath = explode('/', "$pathPreview");
        array_pop($needPath);

        $screenshotFolder = 'screenshots';
        $screenshotsPath  = dirname(implode('/', $needPath)) . "/$screenshotFolder";

        $screens = $detail->screenshots()->withTrashed()->get();
        $needFilesName = [];
        foreach ($screens as $screen) {
            $arrayPath = explode('/', $screen->path);
            $needFilesName[] = $arrayPath[count($arrayPath) - 1];
        }

        $pathFiles = [];
        foreach (Storage::disk('public')->files($screenshotsPath) as $file) {
            $fileName = pathinfo($file, PATHINFO_BASENAME);

            if (!in_array($fileName, $needFilesName)) {
                $pathFiles[] = Storage::url("$screenshotsPath/$fileName");
            }
        }

        return $pathFiles;
    }

    public static function getExtraFiles(Detail $detail, ?string $pathPreview): array
    {
        $needPath = explode('/', "$pathPreview");
        array_pop($needPath);

        $screenshotFolder = 'file';
        $screenshotsPath  = dirname(implode('/', $needPath)) . "/$screenshotFolder";

        $screens = $detail->files()->withTrashed()->get();
        $needFilesName = [];
        foreach ($screens as $screen) {
            $arrayPath = explode('/', $screen->path);
            $needFilesName[] = $arrayPath[count($arrayPath) - 1];
        }

        $pathFiles = [];
        foreach (Storage::disk('public')->files($screenshotsPath) as $file) {
            $fileName = pathinfo($file, PATHINFO_BASENAME);

            if (!in_array($fileName, $needFilesName)) {
                $pathFiles[] = Storage::url("$screenshotsPath/$fileName");
            }
        }

        return $pathFiles;
    }
}
