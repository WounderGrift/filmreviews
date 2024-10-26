<?php

namespace App\Http\Helpers;

class FileHelper
{
    const ACCESS_IMAGE_MIME_TYPE = ['image/png', 'image/jpg', 'image/jpeg', 'image/tiff',
        'image/webp', 'image/svg', 'image/bmp'];

    const ACCESS_FILE_MIME_TYPE = ['application/x-rar', 'application/zip', 'application/x-bitfile', 'application/x-file'];

    const CONVERT_FILE_MIME_TYPE = [
        'application/x-bitfile' => '.file',
        'application/x-file'    => '.file',
        'application/x-rar' => '.rar',
        'application/zip'   => '.zip',
    ];

    const ACCESS_BANNER_MIME_TYPE = ['video/webm', 'video/mp4', 'video/ogg', 'image/png', 'image/jpg', 'image/jpeg',
        'image/tiff', 'image/webp', 'image/svg', 'image/bmp'];

    public static function checkImageMimeType(?string $avatar): bool
    {
        $mimeStart = strpos($avatar, ':') + 1;
        $mimeEnd   = strpos($avatar, ';');
        $mimeType  = substr($avatar, $mimeStart, $mimeEnd - $mimeStart);

        return in_array($mimeType, self::ACCESS_IMAGE_MIME_TYPE);
    }

    public static function checkFileMimeType(string $file): bool
    {
        $mimeStart = strpos($file, ':') + 1;
        $mimeEnd   = strpos($file, ';');
        $mimeType  = substr($file, $mimeStart, $mimeEnd - $mimeStart);

        return !in_array($mimeType, self::ACCESS_FILE_MIME_TYPE);
    }

    public static function checkBannerMimeType(string $banner): bool
    {
        $mimeStart = strpos($banner, ':') + 1;
        $mimeEnd   = strpos($banner, ';');
        $mimeType  = substr($banner, $mimeStart, $mimeEnd - $mimeStart);

        return !in_array($mimeType, self::ACCESS_BANNER_MIME_TYPE);
    }
}
