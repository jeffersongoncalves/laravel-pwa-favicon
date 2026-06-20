<?php

declare(strict_types=1);

namespace JeffersonGoncalves\PwaFavicon;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class PwaFaviconServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-pwa-favicon')
            ->hasConfigFile();
    }

    public function packageBooted(): void
    {
        if (config('pwa-favicon.enabled', false)) {
            PwaFavicon::routes();
        }
    }
}
