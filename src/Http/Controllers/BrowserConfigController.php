<?php

declare(strict_types=1);

namespace JeffersonGoncalves\PwaFavicon\Http\Controllers;

use Illuminate\Http\Response;
use JeffersonGoncalves\PwaFavicon\PwaFavicon;

class BrowserConfigController
{
    public function __invoke(): Response
    {
        return PwaFavicon::getBrowserConfigXml();
    }
}
