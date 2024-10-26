<?php

namespace Modules\PublicationModule\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Helpers\DetailHelper;
use App\Http\Helpers\FileHelper;
use App\Http\Helpers\QueueHelper;
use App\Models\Film;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class PublicationPageController extends Controller
{
    const TITLE = 'Публикация';
    const IN_PUBLISH_PAGE = true;
    const IN_OWNER_PANEL  = true;
    const IN_DETAIL_PAGE  = true;

    public function indexPreview(string $uri)
    {
        $film = Film::query()
            ->where('uri', $uri)
            ->where('status', Film::STATUS_UNPUBLISHED)
            ->first();

        $title = $film?->name ? "Опубликовать $film->name?" : self::TITLE;

        return view('publicationmodule::publish', [
            'title' => $title,
            'inPublishPage' => self::IN_PUBLISH_PAGE,
            'inOwnerPanel'  => self::IN_OWNER_PANEL,
            'film'  => $film,
        ]);
    }

    public function indexDetail(string $uri)
    {
        $film = Film::withTrashed()->where('uri', $uri)
            ->where('status', Film::STATUS_UNPUBLISHED)
            ->first();

        $detail = $film?->detail;
        $info   = json_decode($detail?->info);
        $mimeTypeImage = implode(', ', FileHelper::ACCESS_IMAGE_MIME_TYPE);
        $mimeTypeFile  = implode(', ', FileHelper::ACCESS_FILE_MIME_TYPE);

        if (!isset($film)) {
            return view('detailPage::detail', [
                'inOwnerPanel' => self::IN_OWNER_PANEL,
                'uri'    => $uri,
                'film'   => $film,
                'detail' => $detail,
                'info'   => $info,
                'mimeTypeImage' => $mimeTypeImage,
                'mimeTypeFile'  => $mimeTypeFile,
            ]);
        }

        $filmOriginal = Film::query()
            ->where('film.name', 'like',  "%{$film->name}%")
            ->where('film.status', Film::STATUS_PUBLISHED)
            ->first();

        return view('detailPage::edit', [
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

    public function publish(Request $request): JsonResponse
    {
        $filmId = $request->input('id');
        $film   = Film::query()->find($filmId);
        $route  = Session::get('previous_tab');

        try {
            DB::beginTransaction();

            $film->update([
                'uri' => $film->uri,
                'status' => Film::STATUS_PUBLISHED
            ]);

            if (!$film->is_soft && !$film->is_waiting)
                DetailHelper::addYear($film->date_release);

            $sendEmail = $request->input('typeEmailToChanel');
            if ($sendEmail == 'publish')
                QueueHelper::QueueSendEmailAboutPublicfilm($film);
            elseif ($sendEmail == 'update') {
                if (!QueueHelper::QueueSendEmailAboutUpdatefilm($film))
                    return response()->json(['message' =>
                        'Для отправки письма, нужен торрент файл с указанной версией'
                    ], 400);
            }

            DB::commit();
            return response()->json(['redirect_url' => $route]);
        } catch (\Exception $error) {
            DB::rollback();
            return response()->json(['message' => $error->getMessage()], 403);
        }
    }

    public function removefilm(Request $request): JsonResponse
    {
        $filmId = $request->integer('id');
        $route  = Session::get('previous_tab');
        $film = Film::withTrashed()->find($filmId);

        $film->delete();
        return response()->json(['redirect_url' => $route]);
    }
}
