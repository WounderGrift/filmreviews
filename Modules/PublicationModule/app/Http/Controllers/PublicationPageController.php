<?php

namespace Modules\PublicationModule\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Helpers\DetailHelper;
use App\Http\Helpers\FileHelper;
use App\Http\Helpers\QueueHelper;
use App\Http\Helpers\RssHelper;
use App\Http\Helpers\SitemapHelper;
use App\Http\Helpers\TelegramLogHelper;
use App\Models\Game;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Modules\PublicationModule\Http\Interfaces\PublicationPageInterface;

class PublicationPageController extends Controller implements PublicationPageInterface
{
    const TITLE = 'Публикация';
    const IN_PUBLISH_PAGE = true;
    const IN_OWNER_PANEL  = true;
    const IN_DETAIL_PAGE  = true;

    public function indexPreview(string $uri)
    {
        $game = Game::query()
            ->where('uri', $uri)
            ->where('status', Game::STATUS_UNPUBLISHED)
            ->first();

        $title = $game?->name ? "Опубликовать $game->name?" : self::TITLE;

        return view('publicationmodule::publish', [
            'title' => $title,
            'inPublishPage' => self::IN_PUBLISH_PAGE,
            'inOwnerPanel'  => self::IN_OWNER_PANEL,
            'game'  => $game,
        ]);
    }

    public function indexDetail(string $uri)
    {
        $game = Game::withTrashed()->where('uri', $uri)
            ->where('status', Game::STATUS_UNPUBLISHED)
            ->first();

        $detail = $game?->detail;
        $info   = json_decode($detail?->info);
        $mimeTypeImage = implode(', ', FileHelper::ACCESS_IMAGE_MIME_TYPE);
        $mimeTypeFile  = implode(', ', FileHelper::ACCESS_FILE_MIME_TYPE);

        if (!isset($game)) {
            return view('detailPage::detail', [
                'inOwnerPanel' => self::IN_OWNER_PANEL,
                'uri'    => $uri,
                'game'   => $game,
                'detail' => $detail,
                'info'   => $info,
                'mimeTypeImage' => $mimeTypeImage,
                'mimeTypeFile'  => $mimeTypeFile,
            ]);
        }

        $gameOriginal = Game::query()
            ->where('game.name', 'like',  "%{$game->name}%")
            ->where('game.status', Game::STATUS_PUBLISHED)
            ->first();

        return view('detailPage::edit', [
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

    public function publish(Request $request): JsonResponse
    {
        $gameId = $request->input('id');
        $game   = Game::query()->find($gameId);
        $route  = Session::get('previous_tab');

        try {
            DB::beginTransaction();
            RssHelper::create();

            $game->update([
                'uri' => $game->uri,
                'status' => Game::STATUS_PUBLISHED
            ]);

            if (!$game->is_soft && !$game->is_waiting)
                DetailHelper::addYear($game->date_release);
            SitemapHelper::add($game->uri);

            $sendEmail = $request->input('typeEmailToChanel');
            if ($sendEmail == 'publish')
                QueueHelper::QueueSendEmailAboutPublicGame($game);
            elseif ($sendEmail == 'update') {
                if (!QueueHelper::QueueSendEmailAboutUpdateGame($game))
                    return response()->json(['message' =>
                        'Для отправки письма, нужен торрент файл с указанной версией'
                    ], 400);
            }

            $sendMessageToChannel = $request->input('typeMessageToChanel');
            if ($sendMessageToChannel == 'publish')
                TelegramLogHelper::whatSendMessageToChannel($game, true);
            elseif ($sendMessageToChannel == 'update')
                TelegramLogHelper::whatSendMessageToChannel($game, false);

            if (!$request->user()->checkOwner())
                TelegramLogHelper::reportPublishAndUpdateGame($game, $request->user(),true);

            DB::commit();
            return response()->json(['redirect_url' => $route]);
        } catch (\Exception $error) {
            DB::rollback();
            TelegramLogHelper::reportCantPublishGame($game, $error->getMessage());
            return response()->json(['message' => $error->getMessage()], 403);
        }
    }

    public function removeGame(Request $request): JsonResponse
    {
        $gameId = $request->integer('id');
        $route  = Session::get('previous_tab');

        $game = Game::withTrashed()->find($gameId);
        SitemapHelper::delete($game->uri);
        RssHelper::create();

        $game->delete();
        return response()->json(['redirect_url' => $route]);
    }
}
