<?php

namespace Modules\PublicationModule\Http\Interfaces;

interface UnpublishedPageInterface
{
    public function index();
    public function detail($uri);
}
