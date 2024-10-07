<?php

namespace Modules\BannersModule\Http\Abstractes;

use App\Http\Controllers\Controller;
use App\Models\Banners;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\BannersModule\Http\Interfaces\BannerAbstractInterface;

abstract class BannerPageAbstract extends Controller implements BannerAbstractInterface
{
    public function createBanner($addBanner, string $type): void
    {
        foreach ($addBanner as $id => $banner)
        {
            if ($banner['type'] == 'video') {
                $bannerName = Str::random(12) . '.webm';
                $bannerPath = "banners/" . $bannerName;
            } elseif ($banner['type'] == 'image') {
                $bannerName = Str::random(12) . '.png';
                $bannerPath = "banners/" . $bannerName;
            }

            if (!isset($bannerName) || !isset($bannerPath))
                return;

            $base64ImageWithoutPrefix = substr($banner['result'], strpos($banner['result'], ',') + 1);

            if (Storage::disk('public')->put($bannerPath, base64_decode($base64ImageWithoutPrefix)))
            {
                $recordedBanner = Banners::query()->find($id);

                if (isset($recordedBanner) && $recordedBanner->banner_path
                    && Storage::disk('public')->exists($recordedBanner->banner_path)) {
                    Storage::disk('public')->delete($recordedBanner->banner_path);
                    $recordedBanner->update([
                        'banner_path' => $bannerPath,
                        'banner_name' => $bannerName,
                        'type' => $type,
                        'media_type' => $banner['type'],
                        'active' => false
                    ]);

                    continue;
                }

                $lastBanner = Banners::query()->where('type', $type)
                    ->latest('created_at')->first();

                Banners::query()->create([
                    'banner_path' => $bannerPath,
                    'banner_name' => $bannerName,
                    'type' => $type,
                    'media_type' => $banner['type'],
                    'position' => $lastBanner ? $lastBanner->position + 1 : 1,
                    'active' => false
                ]);
            }
        }
    }

    public function removeBannerSoft($removeBannerId): bool
    {
        $isDeleted = false;
        $current   = Banners::withTrashed()->find($removeBannerId);

        if (!$current->trashed()) {
            $current->update(['active' => false]);
            $isDeleted = $current->delete();
        } else
            $current->restore();

        return $isDeleted;
    }

    public function setOptionBanner($optionBanners): void
    {
        foreach ($optionBanners as $id => $option)
        {
            $banner = Banners::withTrashed()->find($id);

            if ($banner) {
                $banner->update([
                    'banner_name' => $option['name'],
                    'position'    => $option['position'],
                    'href'        => $option['href'],
                ]);
            }
        }
    }
}
