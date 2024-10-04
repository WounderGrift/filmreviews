<?php

namespace Modules\MainModule\Http\Interfaces;

use Illuminate\Http\Request;

interface SearchGamesInterface
{
    public function index(Request $request);
    public function searchForWishlist(Request $request);
    public function searchForUnpublished(Request $request);
    public function searchForSeries(Request $request);
}
