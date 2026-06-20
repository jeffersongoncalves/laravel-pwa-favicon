<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Vite as FoundationVite;
use Illuminate\Support\Facades\Vite;
use JeffersonGoncalves\PwaFavicon\Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class)->in(__DIR__);

// The package builds icon URLs through Vite::asset(); there is no compiled
// manifest in the test environment, so swap the Vite resolver for a fake that
// echoes a deterministic URL/content for any asset path.
beforeEach(function () {
    $vite = Mockery::mock(FoundationVite::class);
    $vite->shouldReceive('asset')->andReturnUsing(fn (string $path) => 'https://cdn.test/'.$path);
    $vite->shouldReceive('content')->andReturnUsing(fn (string $path) => 'fake-favicon-content');

    Vite::swap($vite);
});
