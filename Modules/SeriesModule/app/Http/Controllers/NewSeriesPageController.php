<?php

namespace Modules\SeriesModule\Http\Controllers;

use App\Http\Helpers\SitemapHelper;
use App\Http\Helpers\TelegramLogHelper;
use App\Http\Helpers\UriHelper;
use App\Http\Helpers\FileHelper;
use App\Models\Series;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Modules\SeriesModule\Http\Abstractes\SeriesAbstract;
use Modules\SeriesModule\Http\Interfaces\NewSeriesInterface;

class NewSeriesPageController extends SeriesAbstract implements NewSeriesInterface
{
    const TITLE = "СОЗДАТЬ СЕРИЮ";
    const IN_OWNER_PANEL = true;

    public function index()
    {
        $mimeTypeImage = implode(', ', FileHelper::ACCESS_IMAGE_MIME_TYPE);
        return view('seriesmodule::new', [
           'title' => self::TITLE,
           'inOwnerPanel'  => self::IN_OWNER_PANEL,
           'mimeTypeImage' => $mimeTypeImage
        ]);
    }

    public function create(Request $request): JsonResponse
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'seriesName'  => ['required', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        if (!empty($data['avatarPreview']) && !FileHelper::checkImageMimeType($data['avatarPreview']))
            return response()->json(['message' =>
                'Добавьте нормальную картинку вместо этого файла в Обложке Серии'], 401);

        if ($validator->fails())
            return response()->json(['message' => $validator->errors()->first()], 403);

        $uri  = UriHelper::convertToUriSeriesWhileUnique($data['seriesName']);
        $path = "series/$uri";

        if (!Storage::disk('public')->exists($path))
            Storage::disk('public')->makeDirectory($path);

        $previewSeries = parent::replaceSeriesPreview(null, $data['avatarPreview'], $uri);

        try {
            DB::beginTransaction();
            $series = Series::query()->create([
                'name' => $data['seriesName'],
                'uri'  => $uri,
                'preview'     => $previewSeries,
                'description' => $data['description'],
            ]);

            $series->delete();
            DB::commit();

            if (!$request->user()->checkOwner())
                TelegramLogHelper::reportCreateSeries($series, $request->user(), true);

            SitemapHelper::add("series/$uri");
            return response()->json(['redirect_url' => route('series.list')]);
        } catch (\Exception $error) {
            DB::rollback();
            return response()->json(['message' => $error->getMessage()], 400);
        }
    }
}
