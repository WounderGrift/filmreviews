<?php

namespace Modules\PublicationModule\Http\Interfaces;

use Illuminate\Http\Request;

interface PublicationPageInterface
{
    public function indexPreview(string $uri);
    public function indexDetail(string $uri);
    public function publish(Request $request);
    public function removeGame(Request $request);
}
