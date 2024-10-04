<?php

namespace Modules\DetailModule\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class SpoilerDownload extends Component
{
    public string $mimeTypeFile;
    public string $isSponsor;

    public function __construct($isSponsor, $memeType)
    {
        $this->isSponsor    = $isSponsor;
        $this->mimeTypeFile = $memeType;
    }

    public function render(): View|string
    {
        return view('detailmodule::components.spoiler-download');
    }
}
