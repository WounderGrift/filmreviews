<?php

namespace Modules\SeriesModule\Http\Interfaces;

use Illuminate\Http\Request;

interface EditSeriesInterface
{
    public function index();
    public function indexSeriesDetail($uri);
    public function indexView($uri);
    public function update(Request $request);
    public function delete(Request $request);
    public function setPreviewFromExists(Request $request);
    public function setPreviewRemoveExists(Request $request);
}
