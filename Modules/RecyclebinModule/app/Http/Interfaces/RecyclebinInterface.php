<?php

namespace Modules\RecyclebinModule\Http\Interfaces;

use Illuminate\Http\Request;

interface RecyclebinInterface
{
    public function trashedGameIndex();
    public function trashedScreenIndex();
    public function trashedTorrentIndex();

    public function removeGame(Request $request);
    public function emptyTrashGame(Request $request);
    public function emptyTrashScreenshots(Request $request);
    public function emptyTrashTorrents(Request $request);
    public function restoreGame(Request $request);
}
