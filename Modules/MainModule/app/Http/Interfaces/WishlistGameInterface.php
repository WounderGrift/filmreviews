<?php

namespace Modules\MainModule\Http\Interfaces;

use Illuminate\Http\Request;

interface WishlistGameInterface
{
    public function index($category = null);
    public function toggleWishlist (Request $request);
}
