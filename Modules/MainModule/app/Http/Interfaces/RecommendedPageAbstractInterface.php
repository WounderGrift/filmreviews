<?php

namespace Modules\MainModule\Http\Interfaces;

interface RecommendedPageAbstractInterface
{
    public function getPopularCategoriesFromLikes(int $userId = null);
    public function getPopularCategoriesFromWishlist(int $userId = null);
    public function getPopularCategoriesFromNewsletter(int $userId = null);
    public function getPopularCategoriesFromDownloads(int $userId = null);
}
