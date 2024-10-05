<?php

namespace Modules\DetailModule\Http\Abstractes;

use App\Http\Controllers\Controller;
use App\Http\Helpers\DetailHelper;
use App\Http\Helpers\ImageHelper;
use App\Http\Helpers\SitemapHelper;
use App\Http\Helpers\UriHelper;
use App\Models\Game;
use App\Models\Repacks;
use App\Models\Screenshots;
use App\Models\Series;
use App\Models\Torrents;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\DetailModule\Http\Interfaces\DetailAbstractInterface;

abstract class DetailAbstract extends Controller implements DetailAbstractInterface
{
    public function updateGame($data, $files, $game, $detail): JsonResponse
    {
        try {
            $newUrl = UriHelper::convertToUriWhileUnique($data['gameName'], $game->id);

            if ($newUrl != $game->uri) {
                $this->repathGameFolder($game, $newUrl);
                SitemapHelper::update($game->uri, $newUrl);
            }

            if (!empty($data['avatarGrid'])) {
                if ($data['avatarGrid'] == 'remove')
                    $avatarGrid = $this->removeAvatarGeneralPreview($game->preview_grid);
                elseif ($data['avatarGrid'] != '')
                    $avatarGrid = $this->replaceAvatarGrid($game->preview_grid, $data['avatarGrid'], $newUrl);
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
                $gameSeries = Series::query()->firstOrCreate([
                    'name' => $data['series']
                ], [
                    'name' => $data['series'],
                    'url' => mb_strtolower($data['series'])
                ]);
            }

            $game->update([
                'name' => trim($data['gameName']),
                'uri'  => trim($newUrl),
                'series_id'    => !empty($gameSeries) ? $gameSeries->id : null,
                'date_release' => !empty($data['dateRelease']) ? $data['dateRelease'] : $game->date_release,
                'preview_grid' => $avatarGrid ?? $game->preview_grid,
                'is_russian_lang' => DetailHelper::checkRussianLanguage($data['summaryObject']),
                'is_waiting' => $data['checkboxes']['isWaiting'],
                'is_sponsor' => $data['checkboxes']['isSponsor'],
                'is_soft'    => $data['checkboxes']['isSoft'],
                'is_weak_pc' => $data['checkboxes']['isWeak'],
                'status'     => Game::STATUS_UNPUBLISHED,
            ]);

            if ($data['torrentsNew']) {
                foreach ($data['torrentsNew'] as $key => $torrent) {
                    $repacker = trim($torrent['repacker']);
                    $gameRepacks = null;
                    if ($repacker != 'null' && !empty($repacker)) {
                        $gameRepacks = Repacks::query()->firstOrCreate([
                            'label' => $repacker
                        ], [
                            'label' => $repacker,
                            'url' => mb_strtolower($repacker)
                        ]);
                    }

                    $version = $torrent['version'];
                    $additionalInfo = trim($torrent['additional_info']);
                    if (isset($torrent['sponsor_url'])) {
                        Torrents::query()->create([
                            'game_id' => $data['gameId'],
                            'name'    => "$newUrl-$version.link",
                            'repack_id' => $gameRepacks?->id,
                            'version'   => $version,
                            'size' => $torrent['size'],
                            'path' => $torrent['sponsor_url'],
                            'source'  => 'handle',
                            'is_link' => 1,
                            'additional_info' => !empty($additionalInfo) ? $additionalInfo : null,
                        ]);
                    } else {
                        $pathFile = $this->createTorrentFile($files[$key], $torrent, $newUrl);

                        Torrents::query()->create([
                            'game_id' => $data['gameId'],
                            'name'    => basename($pathFile),
                            'repack_id' => $gameRepacks?->id,
                            'version'   => $version,
                            'size' => $torrent['size'],
                            'path' => $pathFile,
                            'source'  => 'handle',
                            'is_link' => 0,
                            'additional_info' => !empty($additionalInfo) ? $additionalInfo : null,
                        ]);
                    }
                }
            }

            if ($data['torrentsOld']) {
                foreach ($data['torrentsOld'] as $key => $torrent) {
                    $repacker    = trim($data['torrentsOld'][$key]['repacker']);
                    $gameRepacks = null;

                    if ($repacker != 'null' && !empty($repacker)) {
                        $gameRepacks = Repacks::query()->firstOrCreate([
                            'label' => $repacker
                        ], [
                            'label' => $repacker,
                            'url'   => mb_strtolower($repacker)
                        ]);
                    }

                    $additionalInfo = trim($data['torrentsOld'][$key]['additional_info']);

                    $torrent = Torrents::withTrashed()->where('id', $key)->first();
                    $torrent->repack_id = $gameRepacks?->id;
                    $torrent->version   = $data['torrentsOld'][$key]['version'];

                    $version = $data['torrentsOld'][$key]['version'];
                    $versionString = !empty($version) && $version != 'v0.0' ? "-$version" : "";
                    $byRepacker    = !empty($torrent->repacks->label) ? " by {$torrent->repacks->label}" : "";
                    $extended = pathinfo(Storage::disk('public')->path($torrent->path), PATHINFO_EXTENSION);

                    $torrent->name = "{$newUrl}{$versionString}{$byRepacker}.{$extended}";
                    $torrent->size = $data['torrentsOld'][$key]['size'];

                    Storage::disk('public')->move($torrent->path,
                        "games/$newUrl/torrent/{$newUrl}{$versionString}{$byRepacker}.{$extended}");

                    $torrent->path = "games/$newUrl/torrent/{$newUrl}{$versionString}{$byRepacker}.{$extended}";
                    $torrent->additional_info = !empty($additionalInfo) ? $additionalInfo : null;
                    $torrent->save();
                }
            }

            $game->categories()->sync(DetailHelper::getIdsCategories($data['categories']));

            if (!empty($data['screenshotsNew'])) {
                $this->createScreenshots($game->id, $newUrl, $data['screenshotsNew']);
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

                $previewTrailer = "games/$newUrl/previewTrailer/" . Str::random(12) . ".png";
                Storage::disk('public')->copy($data['previewTrailer'], $previewTrailer);

                $pathAvatarWebp = "games/$newUrl/previewTrailer/" . Str::random(12) . ".webp";
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
            $pathAvatar = "games/$uri/previewGrid/" . Str::random(12) . ".$extension";

            Storage::disk('public')->put($pathAvatar, base64_decode($base64ImageWithoutPrefix));
        } elseif (!empty($oldAvatar) && empty($avatarGrid)) {
            $pathAvatar   = "games/$uri/previewGrid/" . basename($oldAvatar);
            Storage::disk('public')->copy($pathOldAvatar, $pathAvatar);
        } elseif (empty($oldAvatar) && !empty($avatarGrid)) {

            $imageContent = file_get_contents($avatarGrid);
            $imageType = exif_imagetype("data://image/jpeg;base64," . base64_encode($imageContent));
            $extension = image_type_to_extension($imageType, false);

            $pathAvatar = "games/$uri/previewGrid/" . Str::random(12) . ".$extension";
            $base64ImageWithoutPrefix = base64_encode($imageContent);
            Storage::disk('public')->put($pathAvatar, base64_decode($base64ImageWithoutPrefix));
        } elseif (!empty($oldAvatar) && !empty($avatarGrid)) {
            $pathAvatar   = "games/$uri/previewGrid/" . basename($oldAvatar);
            $imageContent = file_get_contents($avatarGrid);

            $base64ImageWithoutPrefix = base64_encode($imageContent);
            Storage::disk('public')->put($pathAvatar, base64_decode($base64ImageWithoutPrefix));
        }

        if (isset($pathAvatar)) {
            $pathAvatarWebp = "games/$uri/previewGrid/" . Str::random(12) . ".webp";
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
            $pathAvatar = "games/$uri/previewDetail/" . Str::random(12) . ".$extension";

            Storage::disk('public')->put($pathAvatar, base64_decode($base64ImageWithoutPrefix));
        } elseif (!empty($oldAvatar) && empty($avatarPreview)) {
            $pathAvatar = "games/$uri/previewDetail/" . basename($oldAvatar);
            Storage::disk('public')->copy($pathOldAvatar, $pathAvatar);
        } elseif (empty($oldAvatar) && !empty($avatarPreview)) {
            $imageContent = file_get_contents($avatarPreview);
            $imageType = exif_imagetype("data://image/jpeg;base64," . base64_encode($imageContent));
            $extension = image_type_to_extension($imageType, false);

            $pathAvatar = "games/$uri/previewDetail/" . Str::random(12) . ".$extension";
            $base64ImageWithoutPrefix = base64_encode($imageContent);
            Storage::disk('public')->put($pathAvatar, base64_decode($base64ImageWithoutPrefix));
        } elseif (!empty($oldAvatar) && !empty($avatarPreview)) {
            $pathAvatar   = "games/$uri/previewDetail/" . basename($oldAvatar);
            $imageContent = file_get_contents($avatarPreview);

            $base64ImageWithoutPrefix = base64_encode($imageContent);
            Storage::disk('public')->put($pathAvatar, base64_decode($base64ImageWithoutPrefix));
        }

        if (isset($pathAvatar)) {
            $pathAvatarWebp = "games/$uri/previewDetail/" . Str::random(12) . ".webp";
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
                $pathOldAvatar = "games/$uri/previewDetail/" . basename($oldAvatar);

            if (Storage::disk('public')->copy($pathNewAvatar, $pathOldAvatar)) {
                $pathAvatarWebp = "games/$uri/previewDetail/" . Str::random(12) . ".webp";
                ImageHelper::convertImageToWebp($pathOldAvatar, $pathAvatarWebp);

                return $pathAvatarWebp;
            }
        } else {
            $pathNonameImage = asset('images/730.png');
            $base64ImageWithoutPrefix = file_get_contents($pathNonameImage);
            $base64ImageWithoutPrefix = base64_encode($base64ImageWithoutPrefix);

            Storage::disk('public')->delete($pathOldAvatar);
            if (Storage::disk('public')->put($pathOldAvatar, base64_decode($base64ImageWithoutPrefix))) {
                $pathAvatarWebp = "games/$uri/previewDetail/" . Str::random(12) . ".webp";
                ImageHelper::convertImageToWebp($pathOldAvatar, $pathAvatarWebp);

                return $pathAvatarWebp;
            }
        }

        return '';
    }

    public function createScreenshots(int $gameId, string $uri, $screenshotsNew): void
    {
        foreach ($screenshotsNew as $image) {
            $screenshotPath = "games/$uri/screenshots/" . Str::random(12) . '.png';
            $base64ImageWithoutPrefix = substr($image, strpos($image, ',') + 1);

            if (Storage::disk('public')->put($screenshotPath, base64_decode($base64ImageWithoutPrefix)))
            {
                $pathScreenshotWebp = "games/$uri/screenshots/" . Str::random(12) . ".webp";
                ImageHelper::convertImageToWebp($screenshotPath, $pathScreenshotWebp);

                Screenshots::query()->create([
                    'game_id' => $gameId,
                    'path'    => $pathScreenshotWebp
                ]);
            }
        }
    }

    public function createTorrentFile($file, $torrent, string $uri): ?string
    {
        $pathNewTorrent = null;

        if ($file) {
            $version = $torrent['version'];
            $versionString = !empty(trim($version)) && $version != 'v0.0' ? "-$version" : "";
            $byRepacker    = !empty(trim($torrent['repacker'])) ? " by {$torrent['repacker']}" : "";

            $newFilePath = "games/$uri/torrent/{$uri}{$versionString}{$byRepacker}."
                . $file->getClientOriginalExtension();

            $counter = 1;
            while (Storage::disk('public')->exists($newFilePath)) {
                $newFilePath = "games/$uri/torrent/{$uri}{$versionString}{$byRepacker}."
                    . $file->getClientOriginalExtension();
                $counter++;
            }

            if (Storage::disk('public')->put($newFilePath, file_get_contents($file)))
                $pathNewTorrent = $newFilePath;
        }

        return $pathNewTorrent;
    }

    public function repathGameFolder($game, string $newUri): void
    {
        Storage::disk('public')->move("games/$game->uri", "games/$newUri");

        $game->preview_grid = "games/$newUri/previewGrid/".basename($game->preview_grid);
        $game->save();

        $detail = $game->detail;
        if (isset($detail->preview_trailer)) {
            $pathAvatarWebp = "games/$newUri/previewTrailer/" . Str::random(12) . ".webp";
            ImageHelper::convertImageToWebp("games/$newUri/previewTrailer/" . basename($detail->preview_trailer),
                $pathAvatarWebp);

            $detail->preview_trailer = $pathAvatarWebp;
            $detail->save();
        }

        if (isset($detail->preview_detail)) {
            $detail->preview_detail = "games/$newUri/previewDetail/" . basename($detail->preview_detail);
            $detail->save();
        }

        $screens = $detail->screenshots;
        $screens->each(function ($screenshot) use ($newUri) {
            $pathScreenshotsWebp = "games/$newUri/screenshots/" . Str::random(12) . ".webp";
            ImageHelper::convertImageToWebp("games/$newUri/screenshots/" . basename($screenshot->path),
                $pathScreenshotsWebp);

            $screenshot->path = $pathScreenshotsWebp;
            $screenshot->save();
        });

        $game->torrents()->each(function ($torrent) use ($newUri) {
            $versionString = !empty($torrent->version) && $torrent->version != 'v0.0' ? $torrent->version : "";
            $byRepacker = empty($torrent->repacks->label) ?: $torrent->repacks->label;
            $extended   = pathinfo(Storage::disk('public')->path($torrent->path), PATHINFO_EXTENSION);

            $fileName = "$newUri";
            if ($versionString)
                $fileName .= "-$versionString";
            if ($byRepacker)
                $fileName .= " by $byRepacker";
            $fileName .= ".$extended";

            Storage::disk('public')->move("games/$newUri/torrent/$torrent->name",
                "games/$newUri/torrent/$fileName");

            $torrent->name = $fileName;
            $torrent->path = "games/$newUri/torrent/$fileName";
            $torrent->save();
        });
    }
}
