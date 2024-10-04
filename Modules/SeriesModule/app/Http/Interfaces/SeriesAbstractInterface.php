<?php

namespace Modules\SeriesModule\Http\Interfaces;

interface SeriesAbstractInterface
{
    public function replaceSeriesPreview(string $oldAvatar, string $avatarPreview, string $uri);
    public function removePreview(string $oldAvatar);
    public function repathSeriesFolder($series, string $newUri);
}
