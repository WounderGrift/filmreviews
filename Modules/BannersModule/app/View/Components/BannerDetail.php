<?php

namespace Modules\BannersModule\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;
use Illuminate\Support\Collection;

class BannerDetail extends Component
{
    public Collection $banners;

    public function __construct($banners)
    {
        $this->banners = $banners;
    }

    public function render(): View|string
    {
        return view('bannersmodule::components.bannerdetail');
    }
}
