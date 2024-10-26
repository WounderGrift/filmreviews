<?php

namespace Modules\PublicationModule\Http\Controllers;

use App\Http\Helpers\FileHelper;
use App\Models\Film;
use Illuminate\Support\Facades\Session;

class UnpublishedPageController
{
    const TITLE = 'НЕОПУБЛИКОВАННЫЕ ФИЛЬМЫ';
    const IN_OWNER_PANEL  = true;
    const IS_UNPUBLISHED  = true;
    const IN_DETAIL_PAGE  = true;
    const PER_PAGE = 28;

    public function index()
    {
        $films = Film::query()->where('status', Film::STATUS_UNPUBLISHED)
            ->orderBy('film.is_sponsor', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->paginate(self::PER_PAGE);

        Session::put('previous_tab', url()->current());
        return view('publicationmodule::unpublish', [
            'title' => self:: TITLE,
            'inOwnerPanel'  => self::IN_OWNER_PANEL,
            'isUnpublished' => self::IS_UNPUBLISHED,
            'films' => $films
        ]);
    }

    public function detail($uri)
    {
        $film = Film::query()->where('uri', $uri)
            ->where('status', Film::STATUS_UNPUBLISHED)
            ->first();

        if (!isset($film))
            return view('detailPage::edit', ['film' => $film]);

        $filmOriginal = Film::query()
            ->where('film.name', 'like',  "%{$film->name}%")
            ->where('status', Film::STATUS_PUBLISHED)
            ->first();

        $title  = $film?->name;
        $detail = $film?->detail;
        $info   = json_decode($detail?->info);

        $mimeTypeImage = implode(', ', FileHelper::ACCESS_IMAGE_MIME_TYPE);
        $mimeTypeFile  = implode(', ', FileHelper::ACCESS_FILE_MIME_TYPE);

        return view('detailPage::edit', [
            'title'  => $title,
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
}
