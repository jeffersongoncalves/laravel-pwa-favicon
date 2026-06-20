<?php

namespace JeffersonGoncalves\PwaFavicon\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Vite as FoundationVite;
use Illuminate\Support\Facades\Vite;
use JeffersonGoncalves\PwaFavicon\PwaFaviconServiceProvider;
use Mockery;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'JeffersonGoncalves\\PwaFavicon\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );

        // The package builds icon URLs through Vite::asset(); there is no
        // compiled manifest in the test environment, so swap the Vite resolver
        // for a fake that echoes a deterministic URL/content for any asset path.
        $vite = Mockery::mock(FoundationVite::class);
        $vite->shouldReceive('asset')->andReturnUsing(fn (string $path) => 'https://cdn.test/'.$path);
        $vite->shouldReceive('content')->andReturnUsing(fn (string $path) => 'fake-favicon-content');

        Vite::swap($vite);
    }

    protected function getPackageProviders($app)
    {
        return [
            PwaFaviconServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        // Start from a clean slate so the provider's packageBooted() does not
        // register routes at boot. Each test opts in by enabling the config
        // and calling PwaFavicon::routes() explicitly. mergeConfigFrom keeps
        // this value (existing keys win), while still pulling the manifest /
        // icons defaults from the package config file.
        config()->set('pwa-favicon.enabled', false);
    }
}
