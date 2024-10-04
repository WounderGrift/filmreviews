<?php

namespace Modules\DetailModule\Http\Interfaces;

use Illuminate\Http\Request;

interface DetailGameInterface
{
    public function index($uri);
    public function sendComment(Request $request);
    public function removeComment(Request $request);
    public function resetComment(Request $request);
    public function toggleLike(Request $request);
    public function download(Request $request);
    public function unsubscribe(Request $request);
    public function sendReportError(Request $request);
}
