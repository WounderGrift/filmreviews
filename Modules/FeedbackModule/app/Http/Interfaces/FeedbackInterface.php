<?php

namespace Modules\FeedbackModule\Http\Interfaces;

use Illuminate\Http\Request;

interface FeedbackInterface
{
    public function index();
    public function sendFeedback(Request $request);
}
