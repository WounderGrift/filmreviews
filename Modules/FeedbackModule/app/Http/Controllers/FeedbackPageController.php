<?php

namespace Modules\FeedbackModule\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Helpers\TelegramLogHelper;
use App\Models\LockIpFeedback;
use App\Models\PrivateMessages;
use App\Models\Users;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\FeedbackModule\Http\Interfaces\FeedbackInterface;

class FeedbackPageController extends Controller implements FeedbackInterface
{
    public function index()
    {
        return view('feedbackmodule::feedback');
    }

    public function sendFeedback(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['email', 'regex:/^[\w\.~-]+@([a-zA-Z-]+\.)+[a-zA-Z-]{2,4}$/i',
                'string', 'max:255'],
            'letter' => ['required', 'string', 'max:300'],
        ]);

        if ($request->user())
            $data['email'] = $request->user()->email;
        elseif (!isset($data['email']))
            return response()->json(['message' => 'Произошла ошибка при отправке сообщения'], 400);

        $user = Users::query()->where('email', $data['email'])
                ->where('is_banned', 0)
                ->where('is_verify', 1)->first();

        try {
            $data['letter']  = json_encode($data['letter'], JSON_UNESCAPED_UNICODE);

            if ($ownerId = Users::query()->where('role', Users::ROLE_OWNER)->first()?->id) {
                $data['from_id'] = $user?->id;
                $data['whom_id'] = $ownerId;
                PrivateMessages::query()->create($data);
            }

            if (!$user) {
                $ipAddress = $request->ip();
                $lockIp = LockIpFeedback::query()->firstOrCreate([
                    'ip' => $ipAddress
                ], [
                    'ip' => $ipAddress,
                    'count' => 1
                ]);;

                if ($count = $lockIp->count) {
                    if ($count < 6)
                        TelegramLogHelper::reportFeedback($user, $data['email'], $data['letter']);
                    else {
                        return response()->json([
                            'message' => 'Превышен лимит отправки сообщений, авторизуйтесь
                             и пройдите верификацию, чтобы снять лимит'
                        ], 400);
                    }

                    $lockIp->count = $count + 1;
                    $lockIp->save();
                }
            } else
                TelegramLogHelper::reportFeedback($user, $data['email'], $data['letter']);

            return response()->json(['message' => 'Сообщение отправлено']);
        } catch (\Exception $e) {
            TelegramLogHelper::reportFeedbackError($request->user(), $data['email'],
                $data['letter'], $e->getMessage(), $e->getCode());
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
