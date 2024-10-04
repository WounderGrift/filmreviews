<?php

namespace Modules\SeriesModule\Http\Abstractes;

use App\Http\Helpers\ImageHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\SeriesModule\Http\Interfaces\SeriesAbstractInterface;

abstract class SeriesAbstract extends Controller implements SeriesAbstractInterface
{
    public function replaceSeriesPreview(?string $oldAvatar, ?string $avatarPreview, string $uri): ?string
    {
        if (!empty($oldAvatar))
            $pathOldAvatar = str_replace('/storage', '', $oldAvatar);

        if (empty($oldAvatar) && empty($avatarPreview)) {
            $avatarPreview = asset('images/730.png');

            $imageContent  = file_get_contents($avatarPreview);
            $imageType = exif_imagetype("data://image/jpeg;base64," . base64_encode($imageContent));
            $extension = image_type_to_extension($imageType, false);


            $base64ImageWithoutPrefix = base64_encode($imageContent);
            $pathAvatar = "series/$uri/" . Str::random(12) . ".$extension";

            Storage::disk('public')->put($pathAvatar, base64_decode($base64ImageWithoutPrefix));
        } elseif (!empty($oldAvatar) && empty($avatarPreview)) {
            $pathAvatar   = "series/$uri/" . basename($oldAvatar);
            Storage::disk('public')->copy($pathOldAvatar, $pathAvatar);
        } elseif (empty($oldAvatar) && !empty($avatarPreview)) {
            $imageContent  = file_get_contents($avatarPreview);
            $imageType = exif_imagetype("data://image/jpeg;base64," . base64_encode($imageContent));
            $extension = image_type_to_extension($imageType, false);

            $pathAvatar   = "series/$uri/" . Str::random(12) . ".$extension";
            $base64ImageWithoutPrefix = base64_encode($imageContent);
            Storage::disk('public')->put($pathAvatar, base64_decode($base64ImageWithoutPrefix));
        } elseif (!empty($oldAvatar) && !empty($avatarPreview)) {
            $pathAvatar   = "series/$uri/" . basename($oldAvatar);
            $imageContent = file_get_contents($avatarPreview);

            $base64ImageWithoutPrefix = base64_encode($imageContent);
            Storage::disk('public')->put($pathAvatar, base64_decode($base64ImageWithoutPrefix));
        }

        if (isset($pathAvatar)) {
            $pathAvatarWebp = "series/$uri/" . Str::random(12) . ".webp";
            ImageHelper::convertImageToWebp($pathAvatar, $pathAvatarWebp);
        }

        return $pathAvatarWebp ?? null;
    }

    public function removePreview(?string $oldAvatar): ?string
    {
        $pathOldAvatar = !empty($oldAvatar)
            ? str_replace('/storage', '', $oldAvatar) : null;

        if (!empty($pathOldAvatar) && Storage::disk('public')->exists($pathOldAvatar))
            Storage::disk('public')->delete($pathOldAvatar);
        return null;
    }

    public function repathSeriesFolder($series, string $newUri): void
    {
        Storage::disk('public')->move("series/$series->uri", "series/$newUri");
        $series->preview = "series/$newUri/".basename($series->preview);
        $series->save();
    }
}
