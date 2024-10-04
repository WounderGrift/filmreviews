<?php

namespace Modules\DetailModule\Http\Interfaces;

use Illuminate\Http\Request;

interface EditDetailsInterface
{
    public function index($uri);
    public function release(Request $request);
    public function setPreviewFromExists(Request $request);
    public function setPreviewRemoveExists(Request $request);
    public function removeTorrentSoftly (Request $request);
    public function removeTorrentForced (Request $request);
    public function removeScreenSoftly (Request $request);
    public function removeScreenForced (Request $request);
    public function removeGame(Request $request);
}
