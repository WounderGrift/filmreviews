<?php

namespace Modules\DetailModule\Http\Controllers;

use App\Http\Helpers\FileHelper;
use App\Http\Helpers\RssHelper;
use App\Http\Helpers\SitemapHelper;
use App\Http\Helpers\TelegramLogHelper;
use App\Models\Detail;
use App\Models\Game;
use App\Models\Screenshots;
use App\Models\Torrents;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Modules\DetailModule\Http\Abstractes\DetailAbstract;
use Modules\DetailModule\Http\Interfaces\EditDetailsInterface;

class EditDetailsPageController extends DetailAbstract implements EditDetailsInterface
{
    const IN_OWNER_PANEL = true;
    const IN_DETAIL_PAGE = true;

    public function index($uri)
    {
        $game = Game::query()->where('uri', $uri)->first();

        if (!$game)
            return view('detailmodule::edit', ['game' => $game]);

        $detail = $game?->detail;
        $info   = json_decode($detail?->info);

        $mimeTypeImage = implode(', ', FileHelper::ACCESS_IMAGE_MIME_TYPE);
        $mimeTypeFile  = implode(', ', FileHelper::ACCESS_FILE_MIME_TYPE);

        if (!isset($game)) {
            return view('detail.index.uri', [
                'uri'    => $uri,
                'game'   => $game,
                'detail' => $detail,
                'inOwnerPanel' => self::IN_OWNER_PANEL,
                'info'   => $info,
                'mimeTypeImage' => $mimeTypeImage,
                'mimeTypeFile'  => $mimeTypeFile
            ]);
        }

        $gameOriginal = Game::query()
            ->where('game.name', 'LIKE', "%{$game->name}%")
            ->where('id', '!=', $game->id)
            ->first();

        return view('detailmodule::edit', [
            'game'   => $game,
            'detail' => $detail,
            'info'   => $info,
            'inOwnerPanel'  => self::IN_OWNER_PANEL,
            'inDetailPage'  => self::IN_DETAIL_PAGE,
            'gameOriginal'  => $gameOriginal,
            'mimeTypeImage' => $mimeTypeImage,
            'mimeTypeFile'  => $mimeTypeFile
        ]);
    }

    public function release(Request $request): JsonResponse
    {
        $data  = $request->all('detail');
        $data  = json_decode($data['detail'], true);
        $files = $request->all('torrentsNew');
        $files = $files['torrentsNew'];

        $data['gameId'] = base64_decode($data['gameId']);
        if (empty($data['gameName']))
            return response()->json(['message' => 'Добавьте название игры'], 400);
        if (!empty($data['avatarGrid']) && !FileHelper::checkImageMimeType($data['avatarGrid']))
            return response()->json(['message' =>
                'Добавьте нормальную картинку вместо этого файла в Обложке Сетки'], 401);
        if (!empty($data['avatarDetail']) && !FileHelper::checkImageMimeType($data['avatarDetail']))
            return response()->json(['message' =>
                'Добавьте нормальную картинку вместо этого файла в Обложке Игры'], 401);

        $game   = Game::query()->find($data['gameId']);
        $detail = Detail::query()->find($data['gameId']);

        try {
            DB::beginTransaction();
            $result = parent::updateGame($data, $files, $game, $detail);

            if (!$result->getData()->success)
                throw new \Exception($result->getData()->message);

            DB::commit();

            if (!$request->user()->checkOwner())
                TelegramLogHelper::reportPublishAndUpdateGame($game, $request->user());

            if ($request->user()->checkOwner()) {
                return response()->json(['redirect_url' => route('publish.uri', ['uri' => $game->uri])]);
            } else {
                return response()->json(['redirect_url' => route('detail.index.uri', ['uri' => $game->uri])]);
            }
        } catch (\Exception $error) {
            DB::rollback();
            TelegramLogHelper::reportCantUpdateGame($game, $error->getMessage());
            return response()->json(['message' => $error->getMessage()], 403);
        }
    }

    public function setPreviewFromExists(Request $request): JsonResponse
    {
        $data = $request->validate([
            'fileName' => ['string'],
            'oldUri' => ['string'],
            'gameId' => ['string'],
            'whatPreview' => ['string']
        ]);

        $data['gameId'] = base64_decode($data['gameId']);
        $basePath = explode('/', $data['oldUri']);
        array_pop($basePath);
        $needPath = implode('/', $basePath) . "/" . $data["fileName"];

        if ($data['whatPreview'] == 'grid') {
            $game = Game::query()->find($data['gameId']);
            $game->update(['preview_grid' => $needPath]);
        } elseif ($data['whatPreview'] == 'detail') {
            $detail = Detail::query()->find($data['gameId']);
            $detail->update(['preview_detail' => $needPath]);
        } elseif ($data['whatPreview'] == 'trailer') {
            $detail = Detail::query()->find($data['gameId']);
            $detail->update(['preview_trailer' => $needPath]);
        }

        return response()->json(['success' => true, 'path' => Storage::url($needPath)]);
    }

    public function setPreviewRemoveExists(Request $request): JsonResponse
    {
        $data = $request->validate([
            'fileName'    => ['string'],
            'gameId'      => ['string'],
            'whatPreview' => ['string']
        ]);

        $data['gameId'] = base64_decode($data['gameId']);
        $game = Game::query()->find($data['gameId']);
        if ($data['whatPreview'] == 'grid')
            $needPath = "games/$game->uri/previewGrid/" . $data['fileName'];
        elseif ($data['whatPreview'] == 'detail')
            $needPath = "games/$game->uri/previewDetail/" . $data['fileName'];
        elseif ($data['whatPreview'] == 'trailer')
            $needPath = "games/$game->uri/previewTrailer/" . $data['fileName'];

        if (!isset($needPath))
            return response()->json(['success' => false]);

        Storage::disk('public')->delete($needPath);
        return response()->json(['success' => true]);
    }

    public function removeTorrentSoftly (Request $request): JsonResponse
    {
        $fileId  = $request->input('id');
        $torrent = Torrents::withTrashed()->find($fileId);

        if ($torrent->trashed()) {
            $restored = $torrent->restore();
            return response()->json(['success' => $restored]);
        } elseif (!$torrent->trashed()) {
            $deleted = $torrent->delete();
            return response()->json(['success' => $deleted]);
        }

        return response()->json(['success' => false]);
    }

    public function removeTorrentForced (Request $request): JsonResponse
    {
        $fileId = $request->input('id');
        if ($fileId) {
            $torrent = Torrents::withTrashed()->find($fileId);

            if (!empty($torrent->path) && Storage::disk('public')->exists($torrent->path))
                Storage::disk('public')->delete($torrent->path);

            $torrent->downloadStatistic()->each(function ($file) {
                $file->forceDelete();
            });

            $torrent->forceDelete();
        }

        $fileUrl = $request->input('fileUrl');
        $fileUrl = str_replace("/storage", "", $fileUrl);
        if ($fileUrl) {
            Storage::disk('public')->delete($fileUrl);
        }

        return response()->json(['success' => true]);
    }

    public function removeScreenSoftly (Request $request): JsonResponse
    {
        $screenId = $request->input('id');
        $screen   = Screenshots::withTrashed()->find($screenId);

        if ($screen->trashed())
            $screen->restore();
        elseif (!$screen->trashed())
            $screen->delete();

        return response()->json(['success' => true]);
    }

    public function removeScreenForced (Request $request): JsonResponse
    {
        $screenUrl = $request->input('url');
        $screenUrl = str_replace("/storage", "", $screenUrl);

        $screenId = $request->input('id');
        if ($screenId) {
            $screen = Screenshots::withTrashed()->find($screenId);
            $screen->forceDelete();
        }

        if (Storage::disk('public')->delete($screenUrl)) {
            return response()->json(['success' => true]);
        } else
            return response()->json(['success' => false]);
    }

    public function removeGame(Request $request): JsonResponse
    {
        $gameId = $request->input('id');
        $route  = Session::get('previous_tab');

        $game   = Game::withTrashed()->find($gameId);
        SitemapHelper::delete($game->uri);
        RssHelper::create();

        $game->delete();
        return response()->json(['redirect_url' => $route]);
    }
}
