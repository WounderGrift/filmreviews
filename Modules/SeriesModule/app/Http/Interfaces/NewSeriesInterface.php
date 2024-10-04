<?php

namespace Modules\SeriesModule\Http\Interfaces;

use Illuminate\Http\Request;

interface NewSeriesInterface
{
    public function index();
    public function create(Request $request);
}
