<?php

namespace Modules\ProfileModule\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Helpers\DateHelper;
use App\Http\Helpers\MailHelper;
use App\Http\Helpers\TelegramLogHelper;
use App\Models\OneTimeToken;
use App\Models\Users;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Modules\ProfileModule\Http\Interfaces\ProfileInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProfilePageController extends Controller implements ProfileInterface
{
    const IN_PROFILE_PAGE = true;

    public function index($cid = null)
    {
        if (!$cid)
            $profile = Auth::user();
        else {
            $profile = Users::query()->where('cid', $cid)->first();
            if (!$profile)
                $profile = null;
        }

        return view('profilemodule::profile', [
            'inProfilePage' => self::IN_PROFILE_PAGE,
            'profile' => $profile
        ]);
    }

    public function create(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'regex:/^[\w\.~-]+@([a-zA-Z-]+\.)+[a-zA-Z-]{2,4}$/i',
                Rule::unique('users'), 'string', 'max:255'],
            'password' => ['required', 'min:6', 'max:255'],
            'remember' => ['boolean'],
            'get_letter_release' => ['boolean'],
            'timezone' => ['string']
        ]);

        if (!Users::latest()->exists())
            $data['role'] = Users::ROLE_OWNER;
        else
            $data['role'] = Users::ROLE_FREQUENTER;
        $data['cid']  = null;

        if (DateHelper::isValidTimeZone($data['timezone']))
            $timezone = $data['timezone'];
        else {
            $timezone = config('app.timezone');
            TelegramLogHelper::reportWarningTimezone($request->user(), $data['timezone'], $timezone);
        }

        try {
            DB::beginTransaction();

            $data['timezone'] = $timezone;
            $user = Users::query()->create(Arr::only($data, ['cid', 'name', 'email', 'role', 'password',
                'get_letter_release', 'timezone']));

            if ($user) {
                $name  = $user->name;
                $email = $user->email;

                Auth::loginUsingId($user->id, $data['remember']);
                $token = $request->session()->getId();
                $template = view('mail.verify', compact('name', 'token'))->render();
                MailHelper::compose($template, $email, 'Confirm Email');

                TelegramLogHelper::reportCreateProfile($user, false);

                if ($data['get_letter_release'])
                    TelegramLogHelper::reportToggleSubscribeToPublicGame($user, $data['get_letter_release']);

                DB::commit();
                return response()->json(['reload' => true]);
            }

            TelegramLogHelper::reportCreateProfile($user, true);
            throw new \Exception('Не получается создать ваш профиль', 400);
        } catch (\Exception $e) {
            DB::rollback();
            TelegramLogHelper::reportCreateProfileError($e->getMessage(), $e->getCode());
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email'    => ['required', 'email', 'regex:/^[\w\.~-]+@([a-zA-Z-]+\.)+[a-zA-Z-]{2,4}$/i', 'string'],
            'password' => ['required', 'min:6'],
            'remember' => ['boolean'],
        ]);

        $user = Users::where('email', $data['email'])->first();

        try {
            if ($user) {
                if (DateHelper::isValidTimeZone($request->input('timezone')))
                    $timezone = $request->input('timezone');
                else {
                    $timezone = config('app.timezone');
                    TelegramLogHelper::reportWarningTimezone($user, $request->input('timezone'), $timezone);
                }

                $user->update(['timezone' => $timezone]);
                if (Hash::check($data['password'], $user->password)) {
                    if ($user->oneTimeToken)
                        $user->oneTimeToken->delete();

                    Auth::loginUsingId($user->id, $data['remember']);
                    return response()->json(['reload' => true]);
                }

                if ($user->oneTimeToken) {
                    $expired = Carbon::now()->gt($user->oneTimeToken->updated_at->addMinute(30));

                    if (!$expired && Hash::check($data['password'], $user->oneTimeToken->token)) {
                        $user->oneTimeToken->delete();
                        Auth::loginUsingId($user->id, $data['remember']);
                        return response()->json(['reload' => true]);
                    }
                }
            }

            return response()->json(['message' => 'Неверные мыло и пароль'], 400);
        } catch (\Exception $e) {
            TelegramLogHelper::reportLoginError($user, $e->getMessage(), $e->getCode());
            return response()->json(['message' => 'Произошла ошибка при входе в профиль'], 400);
        }
    }

//    TODO: СЛОМАЛСЯ SMPT, Google хуесосы, надо найти что-то попроще
    public function restore(Request $request): JsonResponse|bool
    {
        $data = $request->validate([
            'name'  => ['required', 'string'],
            'email' => ['required', 'email', 'regex:/^[\w\.~-]+@([a-zA-Z-]+\.)+[a-zA-Z-]{2,4}$/i',
                Rule::exists('users', 'email'), 'string'],
        ]);

        try {
            DB::beginTransaction();
            $user = Users::where('email', $data['email'])
                ->where('name', $data['name'])
                ->first();

            if ($user) {
                $generatedToken = Str::random(12);

                $oneTimeToken = OneTimeToken::updateOrCreate([
                    'user_id' => $user->id
                ], [
                    'user_id' => $user->id,
                    'token' => Hash::make($generatedToken)
                ]);

                if ($oneTimeToken) {
                    $name = $user->name;
                    $template = view(
                        'mail.restore',
                        compact('name', 'generatedToken')
                    )->render();
                    $result   = MailHelper::compose($template, $data['email'], 'Restore access');

                    if ($result->getData()->success) {
                        DB::commit();
                        return response()->json(['message' => 'Письмо успешно отправлено']);
                    } else {
                        DB::rollback();
                        throw new \Exception('Извините, SMTP сломался', 400);
                    }

                } else {
                    DB::rollback();
                    TelegramLogHelper::reportCantGenerateToken($user);
                    return response()->json(['message' => 'Не удалось сгенерировать разовый токен'], 401);
                }
            }
        } catch (\Exception $e) {
            DB::rollback();
            TelegramLogHelper::reportCantSendEmailForRestoreProfile($e->getMessage(), $e->getCode());
            return response()->json(['message' => 'Извините, SMTP сломался'], 400);
        }

        return response()->json(['message' => 'Не получается найти вашу запись'], 401);
    }

    public function sendEmailVerify(Request $request): JsonResponse|bool
    {
        if (!$request->user())
            return response()->json(['message' => 'Forbidden'], 403);

        $token = $request->session()->getId();
        $data  = $request->validate([
            'name'  => ['required', 'string'],
            'email' => ['required', 'email', 'regex:/^[\w\.~-]+@([a-zA-Z-]+\.)+[a-zA-Z-]{2,4}$/i',
                Rule::exists('users', 'email'), 'string'],
        ]);

        $name = $data['name'];
        $template = view(
            'mail.verify',
            compact('name', 'token')
        )->render();

        try {
            $result = MailHelper::compose($template, $data['email'], 'Confirm Email');
            return $result->getData()->success
                ? response()->json(['message' => 'Письмо успешно отправлено'])
                : throw new \Exception('Не удалось отправить письмо, попробуйте позже', 400);
        } catch (\Exception $e) {
            TelegramLogHelper::reportCantSendEmail($request->user(), $e->getMessage(), $e->getCode());
            return response()->json(['message' => 'Не удалось отправить письмо, попробуйте позже'], 400);
        }
    }

    public function verify($token): RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();
            if (session()->getId() === $token) {
                $user->is_verify = true;

                TelegramLogHelper::reportVerifyProfile($user, !$user->save());
            } else {
                throw new NotFoundHttpException();
            }
        }

        return response()->redirectTo('/profile');
    }

    public function logout(): RedirectResponse
    {
        Auth::logout();
        return redirect()->route('main.index');
    }

    public function profileChart(Request $request): JsonResponse
    {
        $code    = $request->input("code");
        $profile = Users::find(base64_decode($code));

        return response()->json(['data' => [
            'downloads' => $profile->downloadStatistic()->where('is_link', 0)->count(),
            'support'   => $profile->downloadStatistic()->where('is_link', 1)->count()
                + $profile->bannerStatistic()->count(),
            'comments'  => $profile->comments()->count(),
            'likesToGames'    => $profile->likesToGames()->count(),
            'likesToComments' => $profile->likesToComments()->count(),
            'wishlist'    => $profile->wishlist()->count(),
            'newsletters' => $profile->newsletters()->count()
        ]]);
    }

    public function banned(Request $request): JsonResponse
    {
        $code   = $request->input('profileEncodeId');
        $userId = base64_decode($code);

        $user     = Users::find($userId);
        $isBanned = !$user->is_banned;
        $user->update([
            'is_banned' => $isBanned
        ]);

        TelegramLogHelper::reportBannedUser($user, $request->user(), $isBanned);
        return response()->json(['redirect_url' => route('profile.index.cid', ['cid' => $user->cid])]);
    }
}
