<?php

namespace Modules\DetailModule\Http\Interfaces;

use Illuminate\Http\Request;

interface NewDetailsInterface
{
    public function index();
    public function create(Request $request);
}
