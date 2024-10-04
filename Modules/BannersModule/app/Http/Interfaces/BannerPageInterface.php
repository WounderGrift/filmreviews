<?php

namespace Modules\BannersModule\Http\Interfaces;

use Illuminate\Http\Request;

interface BannerPageInterface
{
    public function indexBigBanner();
    public function indexDetailBanner();
    public function indexBasementBanner();

    public function bannerJump(Request $request);
    public function bannersSave(Request $request);
    public function bannerRemoveSoftly(Request $request);
    public function bannerRemoveForced(Request $request);
    public function bannerActivate(Request $request);
}
