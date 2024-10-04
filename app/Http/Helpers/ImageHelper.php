<?php

namespace App\Http\Helpers;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;


class ImageHelper
{
    public static function convertImageToWebp($inputPath, $outputPath): void
    {
        if (!Storage::disk('public')->exists($inputPath))
            return;

        $image = Image::make(Storage::disk('public')->get($inputPath));
        $image->encode('webp', 80);

        Storage::disk('public')->delete($inputPath);
        Storage::disk('public')->put($outputPath, $image);
    }
}
