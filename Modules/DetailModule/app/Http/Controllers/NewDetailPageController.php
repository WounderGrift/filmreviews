<?php

namespace Modules\DetailModule\Http\Controllers;

use App\Http\Helpers\DateHelper;
use App\Http\Helpers\DetailHelper;
use App\Http\Helpers\FileHelper;
use App\Http\Helpers\TelegramLogHelper;
use App\Http\Helpers\UriHelper;

use App\Models\Detail;
use App\Models\Game;
use App\Models\Repacks;
use App\Models\Series;
use App\Models\Torrents;

use DateTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Modules\DetailModule\Http\Abstractes\DetailAbstract;
use Modules\DetailModule\Http\Interfaces\NewDetailsInterface;

class NewDetailPageController extends DetailAbstract implements NewDetailsInterface
{
    const IN_OWNER_PANEL = true;

    public function index()
    {
        $today = new DateTime();
        $today = $today->format('Y-m-d');

        $mimeTypeImage = implode(', ', FileHelper::ACCESS_IMAGE_MIME_TYPE);
        $mimeTypeFile  = implode(', ', FileHelper::ACCESS_FILE_MIME_TYPE);

        return view('detailmodule::new', [
            'inOwnerPanel'  => self::IN_OWNER_PANEL,
            'mimeTypeImage' => $mimeTypeImage,
            'mimeTypeFile'  => $mimeTypeFile,
            'today' => $today,
        ]);
    }

    public function create(Request $request): JsonResponse
    {
        $data  = $request->all('detail');
        $data  = json_decode($data['detail'], true);
        $files = $request->all('torrentsNew');
        $files = $files['torrentsNew'];

        if (empty($data['gameName']))
            return response()->json(['message' => 'Добавьте название игры'], 401);
        if (!empty($data['avatarGrid']) && !FileHelper::checkImageMimeType($data['avatarGrid']))
            return response()->json(['message' =>
                'Добавьте нормальную картинку вместо этого файла в Обложке Сетки'], 401);
        if (!empty($data['avatarDetail']) && !FileHelper::checkImageMimeType($data['avatarDetail']))
            return response()->json(['message' =>
                'Добавьте нормальную картинку вместо этого файла в Обложке Игры'], 401);

        $uri = UriHelper::convertToUriWhileUnique($data['gameName']);
        $avatarGrid = parent::replaceAvatarGrid(null, $data['avatarGrid'], $uri);
        $avatarPreview = parent::replaceAvatarPreview(null, $data['avatarPreview'], $uri);

        if (!empty($data['series']) && $data['series'] != 'null') {
            $gameSeries = Series::withTrashed()->firstOrCreate([
                'name' => $data['series']
            ], [
                'name' => $data['series'],
                'url' => mb_strtolower($data['series'])
            ]);
        }

        $gameAdd = [
            'name' => trim($data['gameName']),
            'uri'  => $uri,
            'series_id'    => !empty($gameSeries) ? $gameSeries->id : null,
            'date_release' => DateHelper::dateFormatterForDateReleaseView($data['dateRelease']),
            'preview_grid' => $avatarGrid,
            'is_russian_lang' => DetailHelper::checkRussianLanguage($data['summaryObject']),
            'is_waiting' => $data['checkboxes']['isWaiting'],
            'is_sponsor' => $data['checkboxes']['isSponsor'],
            'is_soft'    => $data['checkboxes']['isSoft'],
            'status'     => Game::STATUS_UNPUBLISHED,
            'is_weak_pc' => $data['checkboxes']['isWeak'],
        ];

        $game = null;

        try {
            DB::beginTransaction();
            $game = Game::query()->create($gameAdd);

            if (!empty($data['dateRelease']) && !$data['checkboxes']['isWaiting'])
                DetailHelper::addYear($data['dateRelease']);

            if (!empty($files)) {
                foreach ($files as $key => $file) {
                    $repacker = trim($data['torrentsNew'][$key]['repacker']);
                    $gameRepacks = null;
                    if ($repacker != 'null' && !empty($repacker)) {
                        $gameRepacks = Repacks::query()->firstOrCreate([
                            'label' => $repacker
                        ], [
                            'label' => $repacker,
                            'url' => mb_strtolower($repacker)
                        ]);
                    }

                    $pathFile = parent::createTorrentFile($file, $data['torrentsNew'][$key], $uri);

                    $additional_info = trim($data['torrentsNew'][$key]['additional_info']);
                    Torrents::query()->create([
                        'game_id' => $game->id,
                        'name' => basename($pathFile),
                        'repack_id' => $gameRepacks?->id,
                        'version' => $data['torrentsNew'][$key]['version'],
                        'size'    => $data['torrentsNew'][$key]['size'],
                        'path'    => $pathFile,
                        'source'  => 'handle',
                        'is_link' => 0,
                        'additional_info' => !empty($additional_info) ? $additional_info : null,
                    ]);
                }
            }

            $game->categories()->sync(DetailHelper::getIdsCategories($data['categories']));

            if (!empty($data['screenshotsNew']))
                parent::createScreenshots($game->id, $uri, $data['screenshotsNew']);

            $info = [
                'summary' => $data['summaryObject'],
                'system'  => $data['requireObject'],
                'description' => $data['description'],
            ];

            $previewTrailer = !empty($data['previewTrailer']) ? $data['previewTrailer'] : asset('images/730.png');

            $detailAdd = [
                'id'   => $game->id,
                'info' => json_encode($info, JSON_UNESCAPED_UNICODE),
                'preview_detail'  => $avatarPreview,
                'preview_trailer' => $previewTrailer,
                'trailer_detail'  => $data['trailer'],
            ];

            Detail::query()->create($detailAdd);
            DB::commit();

            if (!$request->user()->checkOwner())
                TelegramLogHelper::reportPublishAndUpdateGame($game, $request->user(), true);

            if ($request->user()->checkOwner()) {
                return response()->json(['redirect_url' => route('publish.uri', ['uri' => $uri])]);
            } else {
                return response()->json(['redirect_url' => route('detail.index.uri', ['uri' => $game->uri])]);
            }
        } catch (\Exception $error) {
            DB::rollback();
            TelegramLogHelper::reportCantUpdateGame($game, $error->getMessage());
            return response()->json(['message' => $error->getMessage()], 400);
        }
    }
}
