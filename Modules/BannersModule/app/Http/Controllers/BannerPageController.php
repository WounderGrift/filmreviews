<?php

namespace Modules\BannersModule\Http\Controllers;

use App\Http\Helpers\FileHelper;
use App\Models\Banners;
use App\Models\BannerStatistics;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\BannersModule\Http\Abstractes\BannerPageAbstract;
use Modules\BannersModule\Http\Interfaces\BannerPageInterface;

class BannerPageController extends BannerPageAbstract implements BannerPageInterface
{
    const IN_OWNER_PANEL          = true;
    const IN_BIG_BANNER_PAGE      = true;
    const IN_DETAIL_BANNER_PAGE   = true;
    const IN_BASEMENT_BANNER_PAGE = true;

    public function indexBigBanner()
    {
        $title   = 'Настройка Большого Баннер Меню';
        $banners = Banners::withTrashed()->where('type', 'big_banner_menu')
            ->orderBy('banners.position', 'ASC')->get();

        $mimeTypeBanner = implode(', ', FileHelper::ACCESS_BANNER_MIME_TYPE);
        return view('bannersmodule::banners', [
            'title'   => $title,
            'inOwnerPanel'    => self::IN_OWNER_PANEL,
            'inBigBannerPage' => self::IN_BIG_BANNER_PAGE,
            'banners' => $banners,
            'mimeTypeBanner' => $mimeTypeBanner
        ]);
    }

    public function indexDetailBanner()
    {
        $title   = 'Настройка Баннер Детали';
        $banners = Banners::withTrashed()->where('type', 'detail_banner')
            ->orderBy('banners.position', 'ASC')->get();

        $mimeTypeBanner = implode(', ', FileHelper::ACCESS_BANNER_MIME_TYPE);
        return view('bannersmodule::banners', [
            'title'   => $title,
            'inOwnerPanel' => self::IN_OWNER_PANEL,
            'inDetailBannerPage' => self::IN_DETAIL_BANNER_PAGE,
            'banners' => $banners,
            'mimeTypeBanner' => $mimeTypeBanner
        ]);
    }

    public function indexBasementBanner()
    {
        $title   = 'Настройка Баннер Подвала';
        $banners = Banners::withTrashed()->where('type', 'basement_banner')
            ->orderBy('banners.position', 'ASC')->get();

        $mimeTypeBanner = implode(', ', FileHelper::ACCESS_BANNER_MIME_TYPE);
        return view('bannersmodule::banners', [
            'title'   => $title,
            'inOwnerPanel' => self::IN_OWNER_PANEL,
            'inBasementBannerPage' => self::IN_BASEMENT_BANNER_PAGE,
            'banners' => $banners,
            'mimeTypeBanner' => $mimeTypeBanner
        ]);
    }

    public function bannerJump(Request $request): bool|JsonResponse
    {
        $code = $request->input('id');
        $bannerId = base64_decode($code);
        $banner   = Banners::query()->find($bannerId);

        if (empty($banner->href))
            return false;

        if ($banner) {
            if ($banner->active) {
                BannerStatistics::create([
                    'banner_id' => $bannerId,
                    'user_id'   => $request->user()->id ?? null,
                ]);
            }

            return response()->json(['redirect_url' => $banner->href]);
        }

        return false;
    }

    public function bannersSave(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $addBanner        = $request->input('bannerNewAdd');
            $additionalBanner = $request->input('allBannerAdditional');
            $typeBanner       = $request->input('typeBanner');

            if ($addBanner)
                parent::createBanner($addBanner, $typeBanner);

            if ($additionalBanner)
                parent::setOptionBanner($additionalBanner);

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Произошла ошибка при добавлении баннера: '
                . $e->getMessage()], 400, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function bannerRemoveSoftly(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $removeBigBannerMenu = $request->input('id');
            $result = false;

            if ($removeBigBannerMenu)
                $result = parent::removeBannerSoft($removeBigBannerMenu);

            DB::commit();
            return response()->json(['success' => true, 'isDeleted' => $result]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Произошла ошибка при мягком удалении баннера: '
                . $e->getMessage()], 400);
        }
    }

    public function bannerRemoveForced(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $bannerId = $request->input('id');

            if ($bannerId) {
                $banner = Banners::withTrashed()->find($bannerId);
                if ($banner->banner_path && Storage::disk('public')->exists($banner->banner_path))
                    Storage::disk('public')->delete($banner->banner_path);

                if ($banner->bannersStatistics) {
                    foreach ($banner->bannersStatistics as $statistic)
                        $statistic->forceDelete();
                }
                $banner->forceDelete();
            }

            $bannerUrl = $request->input('url');
            $bannerUrl = str_replace("/storage", "", $bannerUrl);

            if ($bannerUrl) {
                Storage::disk('public')->delete($bannerUrl);
            }

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Произошла ошибка при удалении баннера: '
                . $e->getMessage()], 400);
        }
    }

    public function bannerActivate(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $bannerId = $request->input('id');
            $banner   = Banners::query()->find($bannerId);

            $banner->update(['active' => !$banner->active]);

            DB::commit();
            return response()->json(['success' => true, 'active' => $banner->active]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Произошла ошибка при активации баннера: '
                . $e->getMessage()], 400);
        }
    }
}
