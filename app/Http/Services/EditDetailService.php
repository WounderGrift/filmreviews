<?php

namespace App\Http\Services;

use App\Http\Helpers\DetailHelper;
use App\Http\Helpers\ImageHelper;
use App\Http\Helpers\UriHelper;
use App\Models\File;
use App\Models\Film;
use App\Models\Screenshots;
use App\Models\Series;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EditDetailService
{
    public function updateFilm($data, $files, $film, $detail): JsonResponse
    {
        try {
            $newUrl = UriHelper::convertToUriWhileUnique($data['filmName'], $film->id);

            if ($newUrl != $film->uri) {
                $this->repathfilmFolder($film, $newUrl);
            }

            if (!empty($data['avatarGrid'])) {
                if ($data['avatarGrid'] == 'remove')
                    $avatarGrid = $this->removeAvatarGeneralPreview($film->preview_grid);
                elseif ($data['avatarGrid'] != '')
                    $avatarGrid = $this->replaceAvatarGrid($film->preview_grid, $data['avatarGrid'], $newUrl);
            }

            if (!empty($data['avatarPreview'])) {
                if ($data['avatarPreview'] == 'remove') {
                    $avatarPreview = $this->removeAvatarGeneralPreview($detail->preview_detail);
                } elseif ($data['avatarPreview'] != '') {
                    if ($data['getAvatarPreviewFromScreen']) {
                        $avatarPreview = $this->getAvatarPreviewFromScreen(
                            $detail->preview_detail,
                            $data['avatarPreview'],
                            $newUrl
                        );
                    } else {
                        $avatarPreview = $this->replaceAvatarPreview(
                            $detail->preview_detail,
                            $data['avatarPreview'],
                            $newUrl
                        );
                    }
                }
            }

            if (!empty($data['series']) && $data['series'] != 'null') {
                $filmSeries = Series::withTrashed()->firstOrCreate([
                    'name' => $data['series']
                ], [
                    'name' => $data['series'],
                    'url' => mb_strtolower($data['series'])
                ]);
            }

            $film->update([
                'name' => trim($data['filmName']),
                'uri'  => trim($newUrl),
                'series_id'    => !empty($filmSeries) ? $filmSeries->id : null,
                'date_release' => !empty($data['dateRelease']) ? $data['dateRelease'] : $film->date_release,
                'preview_grid' => $avatarGrid ?? $film->preview_grid,
                'is_russian_lang' => DetailHelper::checkRussianLanguage($data['summaryObject']),
                'is_waiting' => $data['checkboxes']['isWaiting'],
                'is_sponsor' => $data['checkboxes']['isSponsor'],
                'is_soft'    => $data['checkboxes']['isSoft'],
                'is_weak_pc' => $data['checkboxes']['isWeak'],
                'status'     => Film::STATUS_UNPUBLISHED,
            ]);

            if ($data['filesNew']) {
                foreach ($data['filesNew'] as $key => $file) {

                    $version = $file['version'];
                    $additionalInfo = trim($file['additional_info']);
                    if ($file['sponsor_url']) {
                        File::query()->create([
                            'film_id' => $data['filmId'],
                            'name'    => "$newUrl-$version.link",
                            'version'   => $version,
                            'size' => $file['size'],
                            'path' => $file['sponsor_url'],
                            'source'  => 'handle',
                            'is_link' => 1,
                            'additional_info' => !empty($additionalInfo) ? $additionalInfo : null,
                        ]);
                    } else {
                        $pathFile = $this->createFile($files[$key], $file, $newUrl);
                        File::query()->create([
                            'film_id' => $data['filmId'],
                            'name'    => basename($pathFile),
                            'version'   => $version,
                            'size' => $file['size'],
                            'path' => $pathFile,
                            'source'  => 'handle',
                            'is_link' => 0,
                            'additional_info' => !empty($additionalInfo) ? $additionalInfo : null,
                        ]);
                    }
                }
            }

            if ($data['filesOld']) {
                foreach ($data['filesOld'] as $key => $file) {
                    $additionalInfo = trim($data['filesOld'][$key]['additional_info']);

                    $file = File::withTrashed()->where('id', $key)->first();
                    $file->version   = $data['filesOld'][$key]['version'];

                    $version = $data['filesOld'][$key]['version'];
                    $versionString = !empty($version) && $version != 'v0.0' ? "-$version" : "";
                    $extended = pathinfo(Storage::disk('public')->path($file->path), PATHINFO_EXTENSION);

                    $file->name = "{$newUrl}{$versionString}.{$extended}";
                    $file->size = $data['filesOld'][$key]['size'];

                    Storage::disk('public')->move($file->path,
                        "films/$newUrl/file/{$newUrl}{$versionString}.{$extended}");

                    $file->path = "films/$newUrl/file/{$newUrl}{$versionString}.{$extended}";
                    $file->additional_info = !empty($additionalInfo) ? $additionalInfo : null;
                    $file->save();
                }
            }

            $film->categories()->sync(DetailHelper::getIdsCategories($data['categories']));

            if (!empty($data['screenshotsNew'])) {
                $this->createScreenshots($film->id, $newUrl, $data['screenshotsNew']);
            }

            $info = [
                'summary' => $data['summaryObject'],
                'system'  => $data['requireObject'],
                'description' => $data['description'],
            ];

            if (!empty($data['previewTrailer']) && basename($data['previewTrailer']) != basename($detail->preview_trailer)
                && $data['previewTrailer'] != '') {

                $data['previewTrailer'] = str_replace('/storage/', '', $data['previewTrailer']);

                if ($detail->preview_trailer)
                    Storage::disk('public')->delete($detail->preview_trailer);

                $previewTrailer = "films/$newUrl/previewTrailer/" . Str::random(12) . ".png";
                Storage::disk('public')->copy($data['previewTrailer'], $previewTrailer);

                $pathAvatarWebp = "films/$newUrl/previewTrailer/" . Str::random(12) . ".webp";
                ImageHelper::convertImageToWebp($previewTrailer, $pathAvatarWebp);
            }

            $detail->update([
                'info' => json_encode($info, JSON_UNESCAPED_UNICODE),
                'preview_detail'  => $avatarPreview ?? $detail->preview_detail,
                'preview_trailer' => $pathAvatarWebp ?? $detail->preview_trailer,
                'trailer_detail'  => $data['trailer'],
            ]);

            return response()->json(['success' => true]);
        } catch(\Exception $error) {
            return response()->json(['success' => false, 'message' => $error->getMessage()], 403);
        }
    }

    public function removeAvatarGeneralPreview(?string $oldAvatar): ?string
    {
        $pathOldAvatar = !empty($oldAvatar)
            ? str_replace('/storage', '', $oldAvatar) : null;

        if (!empty($pathOldAvatar) && Storage::disk('public')->exists($pathOldAvatar))
            Storage::disk('public')->delete($pathOldAvatar);
        return null;
    }

    public function replaceAvatarGrid(?string $oldAvatar, ?string $avatarGrid, string $uri): ?string
    {
        if (!empty($oldAvatar))
            $pathOldAvatar = str_replace('/storage', '', $oldAvatar);

        if (empty($oldAvatar) && empty($avatarGrid)) {
            $avatarGrid   = asset('images/440.png');
            $imageContent = file_get_contents($avatarGrid);
            $imageType = exif_imagetype("data://image/jpeg;base64," . base64_encode($imageContent));
            $extension = image_type_to_extension($imageType, false);

            $base64ImageWithoutPrefix = base64_encode($imageContent);
            $pathAvatar = "films/$uri/previewGrid/" . Str::random(12) . ".$extension";

            Storage::disk('public')->put($pathAvatar, base64_decode($base64ImageWithoutPrefix));
        } elseif (!empty($oldAvatar) && empty($avatarGrid)) {
            $pathAvatar   = "films/$uri/previewGrid/" . basename($oldAvatar);
            Storage::disk('public')->copy($pathOldAvatar, $pathAvatar);
        } elseif (empty($oldAvatar) && !empty($avatarGrid)) {

            $imageContent = file_get_contents($avatarGrid);
            $imageType = exif_imagetype("data://image/jpeg;base64," . base64_encode($imageContent));
            $extension = image_type_to_extension($imageType, false);

            $pathAvatar = "films/$uri/previewGrid/" . Str::random(12) . ".$extension";
            $base64ImageWithoutPrefix = base64_encode($imageContent);
            Storage::disk('public')->put($pathAvatar, base64_decode($base64ImageWithoutPrefix));
        } elseif (!empty($oldAvatar) && !empty($avatarGrid)) {
            $pathAvatar   = "films/$uri/previewGrid/" . basename($oldAvatar);
            $imageContent = file_get_contents($avatarGrid);

            $base64ImageWithoutPrefix = base64_encode($imageContent);
            Storage::disk('public')->put($pathAvatar, base64_decode($base64ImageWithoutPrefix));
        }

        if (isset($pathAvatar)) {
            $pathAvatarWebp = "films/$uri/previewGrid/" . Str::random(12) . ".webp";
            ImageHelper::convertImageToWebp($pathAvatar, $pathAvatarWebp);
        }

        return $pathAvatarWebp ?? null;
    }

    public function replaceAvatarPreview(?string $oldAvatar, ?string $avatarPreview, string $uri): ?string
    {
        if (!empty($oldAvatar))
            $pathOldAvatar = str_replace('/storage', '', $oldAvatar);

        if (empty($oldAvatar) && empty($avatarPreview)) {
            $avatarPreview = asset('images/730.png');

            $imageContent  = file_get_contents($avatarPreview);
            $imageType = exif_imagetype("data://image/jpeg;base64," . base64_encode($imageContent));
            $extension = image_type_to_extension($imageType, false);

            $base64ImageWithoutPrefix = base64_encode($imageContent);
            $pathAvatar = "films/$uri/previewDetail/" . Str::random(12) . ".$extension";

            Storage::disk('public')->put($pathAvatar, base64_decode($base64ImageWithoutPrefix));
        } elseif (!empty($oldAvatar) && empty($avatarPreview)) {
            $pathAvatar = "films/$uri/previewDetail/" . basename($oldAvatar);
            Storage::disk('public')->copy($pathOldAvatar, $pathAvatar);
        } elseif (empty($oldAvatar) && !empty($avatarPreview)) {
            $imageContent = file_get_contents($avatarPreview);
            $imageType = exif_imagetype("data://image/jpeg;base64," . base64_encode($imageContent));
            $extension = image_type_to_extension($imageType, false);

            $pathAvatar = "films/$uri/previewDetail/" . Str::random(12) . ".$extension";
            $base64ImageWithoutPrefix = base64_encode($imageContent);
            Storage::disk('public')->put($pathAvatar, base64_decode($base64ImageWithoutPrefix));
        } elseif (!empty($oldAvatar) && !empty($avatarPreview)) {
            $pathAvatar   = "films/$uri/previewDetail/" . basename($oldAvatar);
            $imageContent = file_get_contents($avatarPreview);

            $base64ImageWithoutPrefix = base64_encode($imageContent);
            Storage::disk('public')->put($pathAvatar, base64_decode($base64ImageWithoutPrefix));
        }

        if (isset($pathAvatar)) {
            $pathAvatarWebp = "films/$uri/previewDetail/" . Str::random(12) . ".webp";
            ImageHelper::convertImageToWebp($pathAvatar, $pathAvatarWebp);
        }

        return $pathAvatarWebp ?? null;
    }

    public function getAvatarPreviewFromScreen(?string $oldAvatar, ?string $avatarPreview, string $uri): ?string
    {
        $pathOldAvatar = str_replace('/storage', '', $oldAvatar);
        $pathNewAvatar = str_replace('/storage', '', $avatarPreview);

        if (Storage::disk('public')->exists($pathNewAvatar)) {
            if (empty($pathOldAvatar))
                $pathOldAvatar = "films/$uri/previewDetail/" . basename($oldAvatar);

            if (Storage::disk('public')->copy($pathNewAvatar, $pathOldAvatar)) {
                $pathAvatarWebp = "films/$uri/previewDetail/" . Str::random(12) . ".webp";
                ImageHelper::convertImageToWebp($pathOldAvatar, $pathAvatarWebp);

                return $pathAvatarWebp;
            }
        } else {
            $pathNonameImage = asset('images/730.png');
            $base64ImageWithoutPrefix = file_get_contents($pathNonameImage);
            $base64ImageWithoutPrefix = base64_encode($base64ImageWithoutPrefix);

            Storage::disk('public')->delete($pathOldAvatar);
            if (Storage::disk('public')->put($pathOldAvatar, base64_decode($base64ImageWithoutPrefix))) {
                $pathAvatarWebp = "films/$uri/previewDetail/" . Str::random(12) . ".webp";
                ImageHelper::convertImageToWebp($pathOldAvatar, $pathAvatarWebp);

                return $pathAvatarWebp;
            }
        }

        return '';
    }

    public function createScreenshots(int $filmId, string $uri, $screenshotsNew): void
    {
        foreach ($screenshotsNew as $image) {
            $screenshotPath = "films/$uri/screenshots/" . Str::random(12) . '.png';
            $base64ImageWithoutPrefix = substr($image, strpos($image, ',') + 1);

            if (Storage::disk('public')->put($screenshotPath, base64_decode($base64ImageWithoutPrefix)))
            {
                $pathScreenshotWebp = "films/$uri/screenshots/" . Str::random(12) . ".webp";
                ImageHelper::convertImageToWebp($screenshotPath, $pathScreenshotWebp);

                Screenshots::query()->create([
                    'film_id' => $filmId,
                    'path'    => $pathScreenshotWebp
                ]);
            }
        }
    }

    public function createFile($file, $fileDescription, string $uri): ?string
    {
        $pathNewFile = null;

        if ($file) {
            $version = $fileDescription['version'];
            $versionString = !empty(trim($version)) && $version != 'v0.0' ? "-$version" : "";

            $newFilePath = "films/$uri/file/{$uri}{$versionString}."
                . $file['files'][0]->getClientOriginalExtension();

            if (Storage::disk('public')->put($newFilePath, file_get_contents($file['files'][0])))
                $pathNewFile = $newFilePath;
        }

        return $pathNewFile;
    }

    public function repathfilmFolder($film, string $newUri): void
    {
        Storage::disk('public')->move("films/$film->uri", "films/$newUri");

        $film->preview_grid = "films/$newUri/previewGrid/".basename($film->preview_grid);
        $film->save();

        $detail = $film->detail;
        if (isset($detail->preview_trailer)) {
            $pathAvatarWebp = "films/$newUri/previewTrailer/" . Str::random(12) . ".webp";
            ImageHelper::convertImageToWebp("films/$newUri/previewTrailer/" . basename($detail->preview_trailer),
                $pathAvatarWebp);

            $detail->preview_trailer = $pathAvatarWebp;
            $detail->save();
        }

        if (isset($detail->preview_detail)) {
            $detail->preview_detail = "films/$newUri/previewDetail/" . basename($detail->preview_detail);
            $detail->save();
        }

        $screens = $detail->screenshots;
        $screens->each(function ($screenshot) use ($newUri) {
            $pathScreenshotsWebp = "films/$newUri/screenshots/" . Str::random(12) . ".webp";
            ImageHelper::convertImageToWebp("films/$newUri/screenshots/" . basename($screenshot->path),
                $pathScreenshotsWebp);

            $screenshot->path = $pathScreenshotsWebp;
            $screenshot->save();
        });

        $film->files()->each(function ($file) use ($newUri) {
            $versionString = !empty($file->version) && $file->version != 'v0.0' ? $file->version : "";
            $extended   = pathinfo(Storage::disk('public')->path($file->path), PATHINFO_EXTENSION);

            $fileName = "$newUri";
            if ($versionString)
                $fileName .= "-$versionString";
            $fileName .= ".$extended";

            Storage::disk('public')->move("films/$newUri/file/$file->name",
                "films/$newUri/file/$fileName");

            $file->name = $fileName;
            $file->path = "films/$newUri/file/$fileName";
            $file->save();
        });
    }
}
