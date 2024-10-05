<?php

namespace Modules\SeriesModule\Http\Controllers;

use App\Http\Helpers\SitemapHelper;
use App\Http\Helpers\TelegramLogHelper;
use App\Http\Helpers\UriHelper;
use App\Http\Helpers\FileHelper;
use App\Models\Series;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\SeriesModule\Http\Abstractes\SeriesAbstract;
use Modules\SeriesModule\Http\Interfaces\EditSeriesInterface;

class EditSeriesPageController extends SeriesAbstract implements EditSeriesInterface
{
    const TITLE = "РЕДАКТИРОВАТЬ СЕРИЮ";
    const IN_OWNER_PANEL = true;
    const PER_PAGE = 28;

    public function index()
    {
        $series = Series::withTrashed()->orderBy('created_at', 'DESC')
            ->paginate(self::PER_PAGE);

        return view('seriesmodule::series-list', [
            'series' => $series,
            'inOwnerPanel' => self::IN_OWNER_PANEL
        ]);
    }

    public function indexSeriesDetail($uri)
    {
        $series = Series::withTrashed()->where('uri', $uri)->first();
        $mimeTypeImage = implode(', ', FileHelper::ACCESS_IMAGE_MIME_TYPE);

        return view('seriesmodule::edit', [
            'series' => $series,
            'inOwnerPanel'  => self::IN_OWNER_PANEL,
            'mimeTypeImage' => $mimeTypeImage,
        ]);
    }

    public function indexView($uri)
    {
        $series = Series::query()->where('name', 'like', "%$uri%")
            ->orWhere('uri', 'like', "%$uri%")
            ->orderBy('created_at', 'DESC')->paginate(self::PER_PAGE);

        return view('seriesmodule::series-list', [
            'series' => $series,
            'inOwnerPanel' => self::IN_OWNER_PANEL
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $data = $request->all();

            if (!empty($data['avatarPreview']) && !FileHelper::checkImageMimeType($data['avatarPreview']))
                return response()->json(['message' =>
                    'Добавьте нормальную картинку вместо этого файла в Обложке Серии'], 401);

            $series = Series::query()->find(base64_decode($data['seriesId']));
            $newUrl = UriHelper::convertToUriSeriesWhileUnique($data['seriesName'], $series->id);

            if ($newUrl != $series->uri) {
                parent::repathSeriesFolder($series, $newUrl);
                SitemapHelper::update("series/$series->uri",
                    config('app.url') . "/series/$newUrl");
            }

            if ($data['avatarPreview'] == 'remove')
                $previewSeries = parent::removePreview($series->preview);
            else
                $previewSeries = parent::replaceSeriesPreview($series->preview, $data['avatarPreview'], $newUrl);

            $series->update([
                'name' => $data['seriesName'],
                'uri'  => $newUrl,
                'preview' => $previewSeries,
                'description' => $data['description'],
            ]);
            DB::commit();

            if (!$request->user()->checkOwner())
                TelegramLogHelper::reportCreateSeries($series, $request->user());

            return response()->json(['redirect_url' => route('series.list')]);
        } catch(\Exception $error) {
            DB::rollback();
            return response()->json(['message' => $error->getMessage()], 400);
        }
    }

    public function delete(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $data = $request->all();

            $series = Series::withTrashed()->find(base64_decode($data['id']));
            if ($series->trashed())
                $series->restore();
            elseif (!$series->trashed())
                $series->delete();

            DB::commit();

            return response()->json(['success' => true]);
        } catch(\Exception $error) {
            DB::rollback();
            return response()->json(['message' => $error->getMessage()], 400);
        }
    }

    public function setPreviewFromExists(Request $request): JsonResponse
    {
        $data = $request->validate([
            'fileName' => ['string'],
            'oldUri'   => ['string'],
            'seriesId' => ['integer'],
        ]);

        $basePath = explode('/', $data['oldUri']);
        array_pop($basePath);
        $needPath = implode('/', $basePath) . "/{$data["fileName"]}";

        $series = Series::query()->find($data['seriesId']);
        $series->update(['preview' => $needPath]);

        return response()->json(['success' => true, 'path' => Storage::url($needPath)]);
    }

    public function setPreviewRemoveExists(Request $request): JsonResponse
    {
        $data = $request->validate([
            'fileName' => ['string'],
            'seriesId' => ['integer'],
        ]);

        $series = Series::query()->find($data['seriesId']);
        $needPath = "series/$series->uri/{$data["fileName"]}";

        if (!isset($needPath))
            return response()->json(['success' => false]);

        Storage::disk('public')->delete($needPath);
        return response()->json(['success' => true]);
    }
}
