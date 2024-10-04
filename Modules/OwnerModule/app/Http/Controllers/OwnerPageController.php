<?php

namespace Modules\OwnerModule\Http\Controllers;

use App\Http\Controllers\Controller;

class OwnerPageController extends Controller
{
    public function index()
    {
        $inOwnerPanel = true;

        return view('ownermodule::index', [
            'inOwnerPanel' => $inOwnerPanel
        ]);
    }
}
