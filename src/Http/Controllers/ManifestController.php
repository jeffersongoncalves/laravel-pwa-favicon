<?php

declare(strict_types=1);

namespace JeffersonGoncalves\PwaFavicon\Http\Controllers;

use Illuminate\Http\JsonResponse;
use JeffersonGoncalves\PwaFavicon\PwaFavicon;

class ManifestController
{
    public function __invoke(): JsonResponse
    {
        return PwaFavicon::getManifestJson();
    }
}
