<?php

namespace Modules\DetailModule\Http\Interfaces;

interface DetailAbstractInterface
{
    public function updateGame($data, $files, $game, $detail);
    public function removeAvatarGeneralPreview(?string $oldAvatar);
    public function replaceAvatarGrid(?string $oldAvatar, ?string $avatarGrid, string $uri);
    public function replaceAvatarPreview(?string $oldAvatar, ?string $avatarPreview, string $uri);
    public function getAvatarPreviewFromScreen(?string $oldAvatar, ?string $avatarPreview, string $uri);
    public function createScreenshots(int $gameId, string $uri, $screenshotsNew);
    public function createTorrentFile($file, $torrent, string $uri);
    public function repathGameFolder($game, string $newUri);
}
