<?php

namespace Modules\DetailModule\Http\Controllers;

use App\Http\Services\DetailService;
use App\Models\Comments;
use App\Models\DownloadStatistics;
use App\Models\File;
use App\Models\Film;
use App\Models\Likes;
use App\Models\Newsletter;
use App\Models\Users;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DetailFilmController
{
    const IN_DETAIL_PAGE = true;

    protected DetailService $detailService;

    public function __construct(DetailService $detailService)
    {
        $this->detailService = $detailService;
    }

    public function index($uri)
    {
        $film = $this->detailService->getFilmDetail($uri);

        $detail   = $film?->detail;
        $info     = json_decode($detail?->info);
        $comments = $detail?->comments;

        if ($comments) {
            $comments = $this->detailService->getCommentPaginate($comments);
        }

        $showSeries = $this->detailService->showSeries($film);

        return view('detailmodule::detail', [
            'inDetailPage'  => self::IN_DETAIL_PAGE,
            'film'   => $film,
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
            'film_id' => ['string'],
            'quote'   => ['nullable', 'string'],
            'comment' => ['required', 'string', 'max:150'],
        ]);

        $data['film_id'] = base64_decode($data['film_id']);
        if (!Film::withTrashed()->where('id', $data['film_id'])->exists())
            return response()->json(['message' => 'Недопустимый ID фильма'], 403);

        if (isset($data['whom_id']))
            $data['whom_id'] = Comments::query()->find(base64_decode($data['whom_id']))->user->id;

        $quote = $data['quote'];
        $text  = $data['comment'];

        $data['from_id'] = $request->user()->id;
        $data['comment'] = json_encode([
            'quote'   => $quote ?? '',
            'comment' => $text,
        ], JSON_UNESCAPED_UNICODE);

        Comments::query()->create($data);
        Film::withTrashed()->find($data['film_id'])->uri;

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

        if ($data['hard']) {
            $comment->likes()->forceDelete();
            $comment->forceDelete();
        } else {
            $comment->delete();
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
            'film_id'    => ['string'],
            'toggleLike' => ['boolean'],
            'comment_id' => ['nullable', 'string']
        ]);

        $data['film_id'] = base64_decode($data['film_id']);
        if (isset($data['comment_id']))
            $data['comment_id'] = base64_decode($data['comment_id']);

        if (!Film::withTrashed()->where('id', $data['film_id'])->exists())
            return response()->json(['message' => 'Недопустимый ID фильма'], 403);

        $data['user_id'] = $request->user()->id;

        $like = Likes::query()->firstOrcreate([
            'film_id' => $data['film_id'],
            'comment_id' => $data['comment_id'] ?? null,
            'user_id' => $data['user_id']
        ]);

        if (!$data['toggleLike']) {
            $like->delete();
        }

        return response()->json(['bool' => $data['toggleLike']]);
    }

    public function download(Request $request): JsonResponse
    {
        $data = $request->validate([
            'file_id' => ['string'],
        ]);

        $data['file_id'] = base64_decode($data['file_id']);
        $file = File::withTrashed()->find($data['file_id']);
        $user    = $request->user();
        $fileUrl = null;

        if ($file->film->is_sponsor) {
            if (str_contains($file->path, 'http://') || str_contains($file->path, 'https://'))
                $fileUrl = $file->path;
        } else
            $fileUrl = Storage::url($file->path);

        if (!!$fileUrl) {
            $result = [
                'file_url'  => $fileUrl,
                'file_name' => $file->name
            ];

            DownloadStatistics::query()->create([
                'user_id' => $user->id ?? null,
                'file_id' => $data['file_id'],
                'is_link' => $file->film->is_sponsor
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
            'film_id' => ['string'],
        ]);

        $data['film_id'] = base64_decode($data['film_id']);
        if (!Film::withTrashed()->where('id', $data['film_id'])->exists())
            return response()->json(['message' => 'Недопустимый ID фильма'], 403);

        $user = $request->user() ?? Users::where('email', $data['email'])->first();
        if ($user) {
            $data['user_id'] = $user->id;
            $data['email']   = $user->email;
        } else {
            if (!isset($data['email']))
                return response()->json(['message' => 'Введите нормальный емейл'], 403);
        }

        Newsletter::query()->firstOrCreate($data);
        Film::withTrashed()->find($data['film_id']);
        return response()->json(['success' => true]);
    }

    public function unsubscribe(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['nullable', 'email', 'regex:/^[\w\.~-]+@([a-zA-Z-]+\.)+[a-zA-Z-]{2,4}$/i',
                'string', 'max:255'],
            'film_id' => ['string'],
        ]);

        $data['film_id'] = base64_decode($data['film_id']);
        if (!Film::withTrashed()->where('id', $data['film_id'])->exists())
            return response()->json(['message' => 'Недопустимый ID фильма'], 403);

        $user = $request->user();
        if ($user) {
            $data['user_id'] = $user->id;
            $data['email']   = $user->email;
        }

        $newsletter = Newsletter::query()->where('email', $data['email'])
            ->where('user_id', $data['user_id'])
            ->where('film_id', $data['film_id']);

        if ($user) {
            Film::withTrashed()->find($data['film_id']);
            $newsletter->delete();
        }

        return response()->json(['success' => true]);
    }

    public function sendReportError(Request $request): JsonResponse
    {
        $data = $request->validate([
            'text' => ['string', 'max:150'],
            'film_id' => ['string'],
        ]);

        Film::query()->find(base64_decode($data['film_id']));
        return response()->json(['success' => true]);
    }
}
