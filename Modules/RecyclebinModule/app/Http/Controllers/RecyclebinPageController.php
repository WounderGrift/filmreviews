<?php

namespace Modules\RecyclebinModule\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Screenshots;
use App\Models\Torrents;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RecyclebinPageController extends Controller
{
    const IN_OWNER_PANEL = true;
    const PER_PAGE = 28;

    public function trashedGameIndex()
    {
        $title = "Корзина, игры";
        $games = Game::onlyTrashed()->paginate(self::PER_PAGE);

        return view('recyclebinmodule::games', [
            'title' => $title,
            'inOwnerPanel' => self::IN_OWNER_PANEL,
            'games' => $games
        ]);
    }

    public function trashedScreenIndex()
    {
        $title = 'Корзина, скриншоты';
        $games = Game::query()->has('trashedScreenshots')->paginate(self::PER_PAGE);

        return view('recyclebinmodule::screenshots', [
            'title' => $title,
            'inOwnerPanel' => self::IN_OWNER_PANEL,
            'games' => $games
        ]);
    }

    public function trashedTorrentIndex()
    {
        $title = 'Корзина, файлы';
        $games = Game::query()->has('trashedTorrents')->paginate(self::PER_PAGE);

        return view('recyclebinmodule::files', [
            'title' => $title,
            'inOwnerPanel' => self::IN_OWNER_PANEL,
            'games' => $games
        ]);
    }

    public function removeGame(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id' => ['integer'],
        ]);

        try {
            DB::beginTransaction();
            $game = Game::onlyTrashed()->find($data['id']);
            $game->categories()->detach();

            $game->likes()->forceDelete();
            $game->newsletters()->forceDelete();
            $game->wishlist()->forceDelete();
            $game->harvester()->forceDelete();

            if (isset($game?->detail?->torrents)) {
                $game->torrents()->withTrashed()->each(function ($torrent) {
                    $torrent->downloadStatistic()->each(function ($file) {
                        $file->forceDelete();
                    });

                    $torrent->forceDelete();
                });
            }

            $game->comments()->each(function ($comment) {
                $comment->likes()->forceDelete();
            });
            $game->comments()->forceDelete();

            if (isset($game?->detail?->screenshots)) {
                $game->detail->screenshots()->withTrashed()->each(function ($screenshot) {
                    $screenshot->forceDelete();
                });
            }

            if (isset($game?->detail))
                $game->detail->forceDelete();

            if ($game->uri && Storage::disk('public')->exists("/games/$game->uri"))
                Storage::disk('public')->deleteDirectory("/games/$game->uri");

            $game->forceDelete();
            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $error) {
            DB::rollback();
            return response()->json(['message' => $error->getMessage()], 400);
        }
    }

    public function emptyTrashGame(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $games = Game::onlyTrashed()->get();

            foreach ($games as $game) {
                $game->categories()->detach();
                $game->likes()->forceDelete();
                $game->newsletters()->forceDelete();
                $game->wishlist()->forceDelete();
                $game->harvester()->forceDelete();

                if (isset($game?->detail?->torrents)) {
                    $game->torrents()->withTrashed()->each(function ($torrent) {
                        $torrent->downloadStatistic()->each(function ($file) {
                            $file->forceDelete();
                        });
                        $torrent->forceDelete();
                    });
                }

                $game->comments()->each(function ($comment) {
                    $comment->likes()->forceDelete();
                });
                $game->comments()->forceDelete();

                if (isset($game?->detail?->screenshots)) {
                    $game->detail->screenshots()->withTrashed()->each(function ($screenshot) {
                        $screenshot->forceDelete();
                    });
                }

                if (isset($game?->detail))
                    $game->detail->forceDelete();

                if ($game->uri && Storage::disk('public')->exists("/games/$game->uri"))
                    Storage::disk('public')->deleteDirectory("/games/$game->uri");

                $game->forceDelete();
            }
            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $error) {
            DB::rollback();
            return response()->json(['message' => $error->getMessage()], 400);
        }
    }

    public function emptyTrashScreenshots(Request $request): JsonResponse
    {
        $screenshots = Screenshots::onlyTrashed()->get();
        foreach ($screenshots as $screenshot) {
            $screenshot->forceDelete();
        }

        return response()->json(['success' => true]);
    }

    public function emptyTrashTorrents(Request $request): JsonResponse
    {
        $torrents = Torrents::onlyTrashed()->get();
        foreach ($torrents as $torrent) {
            $torrent->downloadStatistic()->each(function ($file) {
                $file->forceDelete();
            });
            $torrent->forceDelete();
        }

        return response()->json(['success' => true]);
    }

    public function restoreGame(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id' => ['integer']
        ]);

        $game = Game::onlyTrashed()
            ->where('id', $data['id'])->first();

        $game->update([
            'status' => Game::STATUS_PUBLISHED
        ]);

        $game->restore();
        return response()->json(['success' => true]);
    }
}
