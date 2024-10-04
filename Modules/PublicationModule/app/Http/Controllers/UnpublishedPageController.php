<?php

namespace Modules\PublicationModule\Http\Controllers;

use App\Http\Helpers\FileHelper;
use App\Models\Game;
use Illuminate\Support\Facades\Session;
use Modules\PublicationModule\Http\Interfaces\UnpublishedPageInterface;

class UnpublishedPageController implements UnpublishedPageInterface
{
    const TITLE = 'НЕОПУБЛИКОВАННЫЕ ИГРЫ';
    const IN_OWNER_PANEL  = true;
    const IS_UNPUBLISHED  = true;
    const IN_DETAIL_PAGE  = true;
    const PER_PAGE = 28;

    public function index()
    {
        $games = Game::query()->where('status', Game::STATUS_UNPUBLISHED)
            ->orderBy('game.is_sponsor', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->paginate(self::PER_PAGE);

        Session::put('previous_tab', url()->current());
        return view('publicationmodule::unpublish', [
            'title' => self:: TITLE,
            'inOwnerPanel'  => self::IN_OWNER_PANEL,
            'isUnpublished' => self::IS_UNPUBLISHED,
            'games' => $games
        ]);
    }

    public function detail($uri)
    {
        $game = Game::query()->where('uri', $uri)
            ->where('status', Game::STATUS_UNPUBLISHED)
            ->first();

        if (!isset($game))
            return view('detailPage::edit', ['game' => $game]);

        $gameOriginal = Game::query()
            ->where('game.name', 'like',  "%{$game->name}%")
            ->where('status', Game::STATUS_PUBLISHED)
            ->first();

        $title  = $game?->name;
        $detail = $game?->detail;
        $info   = json_decode($detail?->info);

        $mimeTypeImage = implode(', ', FileHelper::ACCESS_IMAGE_MIME_TYPE);
        $mimeTypeFile  = implode(', ', FileHelper::ACCESS_FILE_MIME_TYPE);

        return view('detailPage::edit', [
            'title'  => $title,
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
}
