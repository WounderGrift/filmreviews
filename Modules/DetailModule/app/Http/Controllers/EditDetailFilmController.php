<?php

namespace Modules\DetailModule\Http\Controllers;

use App\Http\Helpers\FileHelper;
use App\Http\Services\EditDetailService;
use App\Models\Detail;
use App\Models\File;
use App\Models\Film;
use App\Models\Screenshots;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class EditDetailFilmController
{
    const IN_OWNER_PANEL = true;
    const IN_DETAIL_PAGE = true;

    protected EditDetailService $detailService;

    public function __construct(EditDetailService $detailService)
    {
        $this->detailService = $detailService;
    }

    public function index($uri)
    {
        $film = Film::query()->where('uri', $uri)->first();

        if (!$film)
            return view('detailmodule::edit', ['film' => $film]);

        $detail = $film?->detail;
        $info   = json_decode($detail?->info);

        $mimeTypeImage = implode(', ', FileHelper::ACCESS_IMAGE_MIME_TYPE);
        $mimeTypeFile  = implode(', ', FileHelper::ACCESS_FILE_MIME_TYPE);

        if (!isset($film)) {
            return view('detail.index.uri', [
                'uri'    => $uri,
                'film'   => $film,
                'detail' => $detail,
                'inOwnerPanel' => self::IN_OWNER_PANEL,
                'info'   => $info,
                'mimeTypeImage' => $mimeTypeImage,
                'mimeTypeFile'  => $mimeTypeFile
            ]);
        }

        $filmOriginal = Film::query()
            ->where('film.name', 'LIKE', "%{$film->name}%")
            ->where('id', '!=', $film->id)
            ->first();

        return view('detailmodule::edit', [
            'film'   => $film,
            'detail' => $detail,
            'info'   => $info,
            'inOwnerPanel'  => self::IN_OWNER_PANEL,
            'inDetailPage'  => self::IN_DETAIL_PAGE,
            'filmOriginal'  => $filmOriginal,
            'mimeTypeImage' => $mimeTypeImage,
            'mimeTypeFile'  => $mimeTypeFile
        ]);
    }

    public function release(Request $request): JsonResponse
    {
        $data  = $request->all('detail');
        $data  = json_decode($data['detail'], true);
        $files = $request->all('filesNew');
        $files = $files['filesNew'];

        $data['filmId'] = base64_decode($data['filmId']);
        if (empty($data['filmName']))
            return response()->json(['message' => 'Добавьте название игры'], 400);
        if (!empty($data['avatarGrid']) && !FileHelper::checkImageMimeType($data['avatarGrid']))
            return response()->json(['message' =>
                'Добавьте нормальную картинку вместо этого файла в Обложке Сетки'], 401);
        if (!empty($data['avatarDetail']) && !FileHelper::checkImageMimeType($data['avatarDetail']))
            return response()->json(['message' =>
                'Добавьте нормальную картинку вместо этого файла в Обложке'], 401);

        $film   = Film::query()->find($data['filmId']);
        $detail = Detail::query()->find($data['filmId']);

        try {
            DB::beginTransaction();
            $result = $this->detailService->updateFilm($data, $files, $film, $detail);

            if (!$result->getData()->success)
                throw new \Exception($result->getData()->message);

            DB::commit();

            if ($request->user()->checkOwner()) {
                return response()->json(['redirect_url' => route('publish.uri', ['uri' => $film->uri])]);
            } else {
                return response()->json(['redirect_url' => route('detail.index.uri', ['uri' => $film->uri])]);
            }
        } catch (\Exception $error) {
            DB::rollback();
            return response()->json(['message' => $error->getMessage()], 403);
        }
    }

    public function setPreviewFromExists(Request $request): JsonResponse
    {
        $data = $request->validate([
            'fileName' => ['string'],
            'oldUri' => ['string'],
            'filmId' => ['string'],
            'whatPreview' => ['string']
        ]);

        $data['filmId'] = base64_decode($data['filmId']);
        $basePath = explode('/', $data['oldUri']);
        array_pop($basePath);
        $needPath = implode('/', $basePath) . "/" . $data["fileName"];

        if ($data['whatPreview'] == 'grid') {
            $film = Film::query()->find($data['filmId']);
            $film->update(['preview_grid' => $needPath]);
        } elseif ($data['whatPreview'] == 'detail') {
            $detail = Detail::query()->find($data['filmId']);
            $detail->update(['preview_detail' => $needPath]);
        } elseif ($data['whatPreview'] == 'trailer') {
            $detail = Detail::query()->find($data['filmId']);
            $detail->update(['preview_trailer' => $needPath]);
        }

        return response()->json(['success' => true, 'path' => Storage::url($needPath)]);
    }

    public function setPreviewRemoveExists(Request $request): JsonResponse
    {
        $data = $request->validate([
            'fileName'    => ['string'],
            'filmId'      => ['string'],
            'whatPreview' => ['string']
        ]);

        $data['filmId'] = base64_decode($data['filmId']);
        $film = Film::query()->find($data['filmId']);
        if ($data['whatPreview'] == 'grid')
            $needPath = "films/$film->uri/previewGrid/" . $data['fileName'];
        elseif ($data['whatPreview'] == 'detail')
            $needPath = "films/$film->uri/previewDetail/" . $data['fileName'];
        elseif ($data['whatPreview'] == 'trailer')
            $needPath = "films/$film->uri/previewTrailer/" . $data['fileName'];

        if (!isset($needPath))
            return response()->json(['success' => false]);

        Storage::disk('public')->delete($needPath);
        return response()->json(['success' => true]);
    }

    public function removeFileSoftly (Request $request): JsonResponse
    {
        $fileId  = $request->input('id');
        $file = File::withTrashed()->find($fileId);

        if ($file->trashed()) {
            $restored = $file->restore();
            return response()->json(['success' => $restored]);
        } elseif (!$file->trashed()) {
            $deleted = $file->delete();
            return response()->json(['success' => $deleted]);
        }

        return response()->json(['success' => false]);
    }

    public function removeFileForced (Request $request): JsonResponse
    {
        $fileId = $request->input('id');
        if ($fileId) {
            $file = File::withTrashed()->find($fileId);

            if (!empty($file->path) && Storage::disk('public')->exists($file->path))
                Storage::disk('public')->delete($file->path);

            $file->downloadStatistic()->each(function ($file) {
                $file->forceDelete();
            });

            $file->forceDelete();
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

    public function removeFilm(Request $request): JsonResponse
    {
        $filmId = $request->input('id');
        $route  = Session::get('previous_tab');
        $film   = Film::withTrashed()->find($filmId);

        $film->delete();
        return response()->json(['redirect_url' => $route]);
    }
}
