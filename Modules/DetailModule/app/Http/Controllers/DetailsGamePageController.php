<?php

namespace Modules\DetailModule\Http\Controllers;

use App\Http\Helpers\TelegramLogHelper;
use App\Models\Comments;
use App\Models\DownloadStatistics;
use App\Models\Game;
use App\Models\Likes;
use App\Models\Newsletter;
use App\Models\Torrents;
use App\Models\Users;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Modules\DetailModule\Http\Interfaces\DetailGameInterface;

class DetailsGamePageController implements DetailGameInterface
{
    const IN_DETAIL_PAGE = true;
    const PER_PAGE = 7;

    public function index($uri)
    {
        $gameQuery = Game::query()
            ->when(
                Auth::check() && Auth::user()->checkOwnerOrAdmin(),
                fn($query) => $query->withTrashed(),
                fn($query) => $query->where('status', Game::STATUS_PUBLISHED)
            );

        $game = $gameQuery->where('uri', $uri)->first();

        $detail   = $game?->detail;
        $info     = json_decode($detail?->info);
        $comments = $detail?->comments;

        if ($comments) {
            $comments = $comments->sortByDesc('created_at');
            $total    = $comments->count();

            $currentPage = request()->query('page', ceil($total / self::PER_PAGE));
            $comments    = $comments->forPage($currentPage, self::PER_PAGE);

            $comments = new LengthAwarePaginator($comments, $total, self::PER_PAGE, $currentPage, [
                'path'     => LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]);
        }

        $showSeries = false;
        if (isset($game->series)) {
            $showSeries = Game::query()->select('game.*')
                    ->where('game.is_soft', 0)
                    ->where('game.is_waiting', 0)
                    ->where('game.status', Game::STATUS_PUBLISHED)
                    ->where('game.series_id', $game->series_id)
                    ->count() > 1;
        }

        return view('detailmodule::detail', [
            'inDetailPage'  => self::IN_DETAIL_PAGE,
            'game'   => $game,
            'detail' => $detail,
            'info'   => $info,
            'comments'   => $comments,
            'showSeries' => $showSeries,
        ]);
    }

    public function sendComment(Request $request): JsonResponse
    {
        $data = $request->validate([
            'whom_id' => ['nullable', 'string'],
            'game_id' => ['string'],
            'quote'   => ['nullable', 'string'],
            'comment' => ['required', 'string', 'max:150'],
        ]);

        $data['game_id'] = base64_decode($data['game_id']);
        if (!Game::withTrashed()->where('id', $data['game_id'])->exists())
            return response()->json(['message' => 'Недопустимый ID игры'], 403);

        if (isset($data['whom_id']))
            $data['whom_id'] = Comments::find(base64_decode($data['whom_id']))->user->id;

        $quote = $data['quote'];
        $text  = $data['comment'];

        $data['from_id'] = $request->user()->id;
        $data['comment'] = json_encode([
            'quote'   => $quote ?? '',
            'comment' => $text,
        ], JSON_UNESCAPED_UNICODE);

        $comment = Comments::query()->create($data);
        $gameUrl = Game::withTrashed()->find($data['game_id'])->uri;

        TelegramLogHelper::reportComment($request->user(), $quote, $text, $gameUrl, !$comment->exists);
        return response()->json(['success' => true]);
    }

    public function removeComment(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id'   => ['string'],
            'hard' => ['string']
        ]);

        $data['hard'] = filter_var($data['hard'], FILTER_VALIDATE_BOOLEAN);
        $data['id']   = base64_decode($data['id']);

        if (!Comments::withTrashed()->where('id', $data['id'])->exists())
            return response()->json(['message' => 'Недопустимый ID комментария'], 403);

        $comment = Comments::withTrashed()->find($data['id']);

        if (Auth::check() && ($comment->from_id !== Auth::user()->id || !Auth::user()->checkOwnerOrAdmin()))
            return response()->json(['message' => 'Нельзя удалять чужие комментарии'], 403);

        $json = json_decode($comment->comment);
        $uri  = $comment->game->uri;

        if ($data['hard']) {
            $comment->likes()->forceDelete();
            $comment->forceDelete();
        } else {
            $delete = $comment->delete();
            TelegramLogHelper::reportDeleteComment($request->user(), $json->quote , $json->comment, $uri, !$delete);
        }

        return response()->json(['success' => true]);
    }

    public function resetComment(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id' => ['string'],
        ]);

        $comment = Comments::onlyTrashed()->find(base64_decode($data['id']));
        $comment->restore();
        return response()->json(['refresh' => true]);
    }

    public function toggleLike(Request $request): JsonResponse
    {
        if (!$request->user())
            return response()->json(['message' => 'Forbidden'], 403);

        $data = $request->validate([
            'game_id'    => ['string'],
            'toggleLike' => ['boolean'],
            'comment_id' => ['nullable', 'string']
        ]);

        $data['game_id'] = base64_decode($data['game_id']);
        if (isset($data['comment_id']))
            $data['comment_id'] = base64_decode($data['comment_id']);

        if (!Game::withTrashed()->where('id', $data['game_id'])->exists())
            return response()->json(['message' => 'Недопустимый ID игры'], 403);

        $gameUrl = Game::withTrashed()->find($data['game_id'])->uri;
        $data['user_id'] = $request->user()->id;

        $like = Likes::query()->firstOrcreate([
            'game_id' => $data['game_id'],
            'comment_id' => $data['comment_id'] ?? null,
            'user_id' => $data['user_id']
        ]);

        if (!$data['toggleLike']) {
            $like->delete();
        }

        if (isset($data['comment_id'])) {
            $reportVar = [
                'whomCid' => $like->user->cid,
                'comment' => json_decode($like->comments->comment)->comment,
            ];

            TelegramLogHelper::reportToggleLikeForComment($request->user(), $gameUrl, $reportVar, $data['toggleLike']);
        } else
            TelegramLogHelper::reportToggleLikeForGame($request->user(), $gameUrl, $data['toggleLike']);

        return response()->json(['bool' => $data['toggleLike']]);
    }

    public function download(Request $request): JsonResponse
    {
        $data = $request->validate([
            'torrent_id' => ['string'],
        ]);

        $data['torrent_id'] = base64_decode($data['torrent_id']);
        $torrent = Torrents::withTrashed()->find($data['torrent_id']);
        $user    = $request->user();
        $fileUrl = null;

        if ($torrent->game->is_sponsor) {
            if (str_contains($torrent->path, 'http://') || str_contains($torrent->path, 'https://'))
                $fileUrl = $torrent->path;
        } else
            $fileUrl = Storage::url($torrent->path);

        if (!!$fileUrl) {
            $result = [
                'file_url'  => $fileUrl,
                'file_name' => $torrent->name
            ];

            DownloadStatistics::query()->create([
                'user_id' => $user->id ?? null,
                'torrent_id' => $data['torrent_id'],
                'is_link' => $torrent->game->is_sponsor
            ]);
        } else {
            return response()->json(['message' => 'Не является ссылкой, не является файлом'], 403);
        }

        return response()->json($result);
    }

    public function subscribe(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['nullable', 'email', 'regex:/^[\w\.~-]+@([a-zA-Z-]+\.)+[a-zA-Z-]{2,4}$/i',
                'string', 'max:255'],
            'game_id' => ['string'],
        ]);

        $data['game_id'] = base64_decode($data['game_id']);
        if (!Game::withTrashed()->where('id', $data['game_id'])->exists())
            return response()->json(['message' => 'Недопустимый ID игры'], 403);

        $user = $request->user() ?? Users::where('email', $data['email'])->first();
        if ($user) {
            $data['user_id'] = $user->id;
            $data['email']   = $user->email;
        } else {
            if (!isset($data['email']))
                return response()->json(['message' => 'Введите нормальный емейл'], 403);
        }

        $newsletter = Newsletter::query()->firstOrCreate($data);
        $game = Game::withTrashed()->find($data['game_id']);

        if ($user)
            TelegramLogHelper::reportUserToggleNewsletter($user, $game, $newsletter->wasRecentlyCreated);
        else
            TelegramLogHelper::reportAnonToggleNewsletter($game, $newsletter->wasRecentlyCreated);
        return response()->json(['success' => true]);
    }

    public function unsubscribe(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['nullable', 'email', 'regex:/^[\w\.~-]+@([a-zA-Z-]+\.)+[a-zA-Z-]{2,4}$/i',
                'string', 'max:255'],
            'game_id' => ['string'],
        ]);

        $data['game_id'] = base64_decode($data['game_id']);
        if (!Game::withTrashed()->where('id', $data['game_id'])->exists())
            return response()->json(['message' => 'Недопустимый ID игры'], 403);

        $user = $request->user();
        if ($user) {
            $data['user_id'] = $user->id;
            $data['email']   = $user->email;
        }

        $newsletter = Newsletter::where('email', $data['email'])
            ->where('user_id', $data['user_id'])
            ->where('game_id', $data['game_id']);

        if ($user) {
            $game = Game::withTrashed()->find($data['game_id']);
            TelegramLogHelper::reportUserToggleNewsletter($user, $game, $newsletter->delete());
        }

        return response()->json(['success' => true]);
    }

    public function sendReportError(Request $request): JsonResponse
    {
        $data = $request->validate([
            'text' => ['string', 'max:150'],
            'game_id' => ['string'],
        ]);

        $game = Game::find(base64_decode($data['game_id']));
        TelegramLogHelper::reportCustomerError($game, $data['text']);

        return response()->json(['success' => true]);
    }
}
