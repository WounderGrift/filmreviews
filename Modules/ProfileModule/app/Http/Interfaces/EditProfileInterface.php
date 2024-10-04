<?php

namespace Modules\ProfileModule\Http\Interfaces;

use Illuminate\Http\Request;

interface EditProfileInterface
{
    public function index($cid);
    public function update(Request $request);
}
