<?php

namespace Modules\ProfileModule\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Helpers\FileHelper;
use App\Http\Helpers\ImageHelper;
use App\Http\Helpers\MailHelper;
use App\Http\Helpers\TelegramLogHelper;
use App\Models\Users;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Modules\ProfileModule\Http\Interfaces\EditProfileInterface;

class EditProfilePageController extends Controller implements EditProfileInterface
{
    const TITLE = 'ПРОФИЛЬ';
    const IN_PROFILE_PAGE = true;

    public function index($cid)
    {
        if (!$cid) {
            $profile = Auth::user();
        } else {
            $profile = Users::query()->where('cid', $cid)->first();
            if (!$profile)
                $profile = Auth::user();
        }

        if (($profile->id !== Auth::user()->id && !$profile->is_banned
            || $profile->role == $profile::ROLE_OWNER && Auth::user()->checkAdmin()) && !Auth::user()->checkOwner())
            return redirect()->route('profile.index.cid', ['cid' => $profile->cid]);

        $mimeTypeImage = implode(', ', FileHelper::ACCESS_IMAGE_MIME_TYPE);

        return view('profilemodule::edit', [
            'title' => self::TITLE,
            'inProfilePage' => self::IN_PROFILE_PAGE,
            'profile' => $profile,
            'mimeTypeImage' => $mimeTypeImage
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $profileId = $request->input("profileEncodeId");
        $userOld   = Users::find(base64_decode($profileId));

        $data = array_filter($request->validate([
            'email' => ['nullable', 'email', 'regex:/^[\w\.~-]+@([a-zA-Z-]+\.)+[a-zA-Z-]{2,4}$/i',
                Rule::unique('users', 'email')->ignore($profileId),
                'string', 'max:255'],
            'password' => ['nullable', 'min:6', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'cid'  => ['nullable', Rule::unique('users', 'cid')->ignore($profileId),
                'string', 'max:255', 'regex:/^[A-Za-z0-9_-]+$/'],
            'role' => ['nullable'],
            'avatar_name' => ['string', 'nullable', 'max:255'],
            'status'   => ['string', 'nullable', 'max:255'],
            'about_me' => ['string', 'nullable', 'max:255'],
            'get_letter_release'  => ['boolean', 'nullable'],
        ]));

        $data['status']   = $data['status']   ?? null;
        $data['about_me'] = $data['about_me'] ?? null;

        $userNew = clone $userOld;
        $avatar  = $request->input('avatar');

        if ($userOld->avatar_path) {
            $pathAvatar = str_replace('/storage', '', $userNew->avatar_path);
            if (FileHelper::checkImageMimeType($avatar) && Storage::disk('public')->exists($pathAvatar))
                Storage::disk('public')->delete($pathAvatar);
        }

        if (isset($data['cid']) && $data['cid'] != $userOld->cid) {
            $nameImage = $data['cid'];
            if ($userOld->avatar_path) {
                $pathAvatar = $data['avatar_path'] = "avatars/$nameImage.png";
                Storage::disk('public')->move($userOld->avatar_path, $data['avatar_path']);
            } else
                $pathAvatar = "avatars/$userNew->cid.png";
        } else {
            if ($userOld->avatar_path && $data['avatar_name'] == "Аватар не выбран") {
                Storage::disk('public')->delete($userOld->avatar_path);
            }
            $pathAvatar = "avatars/$userNew->cid.png";
        }

        if ($avatar) {
            if (!FileHelper::checkImageMimeType($avatar))
                return response()->json(['message' => 'Добавьте нормальную картинку вместо этого файла'], 401);

            $base64ImageWithoutPrefix = substr($avatar, strpos($avatar, ',') + 1);
            if (Storage::disk('public')->put($pathAvatar, base64_decode($base64ImageWithoutPrefix))) {
                $pathAvatarWebp = str_replace('png', 'webp', $pathAvatar);
                ImageHelper::convertImageToWebp($pathAvatar, $pathAvatarWebp);

                $data['avatar_path'] = $pathAvatarWebp;
            }
        }

        if ($data['avatar_name'] == 'Аватар не выбран')
        {
            $data['avatar_path'] = null;
            $data['avatar_name'] = null;
        }

        if (isset($data['email']))
            $data['is_verify'] = false;

        $isCheck = $userNew->update($data);
        if (isset($data['email']))
        {
            $name  = $userNew->name;
            $email = $userNew->email;

            $token = $request->session()->getId();
            $template = view('mail.verify', compact('name', 'token'))->render();
            MailHelper::compose($template, $email, 'Confirm Email');
        }

        if (isset($data['get_letter_release']))
            TelegramLogHelper::reportToggleSubscribeToPublicGame($userNew, $data['get_letter_release']);

        TelegramLogHelper::reportChangeUser($request->user(), $userOld, $userNew, !$isCheck);
        return response()->json(['redirect_url' => route('profile.index.cid', ['cid' => $userNew->cid])]);
    }
}
