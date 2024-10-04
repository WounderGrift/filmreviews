<?php

namespace Modules\BannersModule\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class BannerSpoiler extends Component
{
    public string $mimeTypeBanner;

    public function __construct($memeType)
    {
        $this->mimeTypeBanner = $memeType;
    }

    /**
     * Get the view/contents that represent the component.
     */
    public function render(): View|string
    {
        return view('bannersmodule::components.bannerspoiler');
    }
}
