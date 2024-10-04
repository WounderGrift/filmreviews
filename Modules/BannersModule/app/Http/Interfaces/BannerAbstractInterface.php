<?php

namespace Modules\BannersModule\Http\Interfaces;

interface BannerAbstractInterface
{
    public function createBanner($addBanner, string $type);
    public function removeBannerSoft($removeBannerId);
    public function setOptionBanner($optionBanners);
}
