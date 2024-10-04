<?php

namespace App\Http\Helpers;

use App\Models\Banners;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class BannerHelper
{
    public static function getBasementBanners($onlyActive = false): Collection
    {
        $basementBanners = Banners::query()->where('type', 'basement_banner');
        if ($onlyActive)
            $basementBanners->where('active', 1);
        return $basementBanners->orderBy('banners.position', 'ASC')->get();
    }

    public static function getBigBannerMenu($onlyActive = false)
    {
        $bigBannerBanners = Banners::query()->where('type', 'big_banner_menu');
        if ($onlyActive)
            $bigBannerBanners->where('active', 1);
        return $bigBannerBanners->orderBy('banners.position', 'ASC')->get();
    }

    public static function getDetailBannerMenu($onlyActive = false): Collection
    {
        $detailBanner = Banners::query()->where('type', 'detail_banner');
        if ($onlyActive)
            $detailBanner->where('active', 1);
        return $detailBanner->orderBy('banners.position', 'ASC')->get();
    }

    public static function getExtraBanners(): array
    {
        $banners = Banners::withTrashed()->get();
        $bannersFolder = 'banners';

        $needFilesName = [];
        foreach ($banners as $banner) {
            $arrayPath = explode('/', $banner->banner_path);
            $needFilesName[] = $arrayPath[count($arrayPath) - 1];
        }

        $pathFiles = [];
        foreach (Storage::disk('public')->files($bannersFolder) as $file) {
            $fileName = pathinfo($file, PATHINFO_BASENAME);

            if (!in_array($fileName, $needFilesName))
                $pathFiles[] = Storage::url("$bannersFolder/$fileName");
        }

        return $pathFiles;
    }
}
