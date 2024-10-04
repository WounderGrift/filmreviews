<?php

namespace Modules\MainModule\Http\Interfaces;

interface SeriesPageInterface
{
    public function index();
    public function indexSeries($uri);
}
