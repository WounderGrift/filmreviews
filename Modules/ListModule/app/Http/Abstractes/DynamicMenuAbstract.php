<?php

namespace Modules\ListModule\Http\Abstractes;

use App\Http\Controllers\Controller;
use App\Models\Categories;
use App\Models\Repacks;
use App\Models\Torrents;

abstract class DynamicMenuAbstract extends Controller
{
    public function categoriesItemsAdd($categoriesAdd): void
    {
        foreach ($categoriesAdd as $url => $value) {
            $array = [
                'label'    => $value['key'],
                'url'      => $url,
                'for_soft' => $value['soft'] === 'true'
            ];

            if (isset($value['code']))
                Categories::query()->updateOrCreate(['id' => base64_decode($value['code'])], $array);
            else
                Categories::query()->create($array);
        }
    }

    public function categoriesItemsRemove($categoriesRemove): void
    {
        foreach ($categoriesRemove as $code)
        {
            $categoryId = base64_decode($code);
            $category   = Categories::query()->find($categoryId);
            $category->gamesCategoriesLink()->detach();
            $category->delete();
        }
    }

    public function repacksItemsAdd($repacksAdd): void
    {
        foreach ($repacksAdd as $url => $value) {
            $array = [
                'label' => $value['key'],
                'url'   => $url
            ];

            if (isset($value['code']))
                Repacks::query()->updateOrCreate(['id' => base64_decode($value['code'])], $array);
            else
                Repacks::query()->create($array);
        }
    }

    public function repacksItemsRemove($repacksRemove): void
    {
        foreach ($repacksRemove as $code)
        {
            $repackId  = base64_decode($code);
            $chunkSize = 1000;

            Torrents::query()->where('repack_id', $repackId)->chunk($chunkSize, function ($torrents) {
                $torrents->each(function ($torrent) {
                    $torrent->repack_id = null;
                    $torrent->save();
                });
            });

            $repack = Repacks::query()->find($repackId);
            if ($repack)
                $repack->delete();
        }
    }
}
