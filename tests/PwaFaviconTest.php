<?php

use JeffersonGoncalves\PwaFavicon\PwaFavicon;

it('serves the manifest as application/manifest+json with 512 any and maskable icons', function () {
    config()->set('pwa-favicon.enabled', true);

    PwaFavicon::routes();

    $response = $this->get('/manifest.json');

    $response->assertOk();

    expect($response->headers->get('Content-Type'))
        ->toContain('application/manifest+json');

    $icons = $response->json('icons');

    expect($icons)
        ->toContain([
            'src' => 'https://cdn.test/resources/favicon/icon-512x512.png',
            'sizes' => '512x512',
            'type' => 'image/png',
            'purpose' => 'any',
        ])
        ->toContain([
            'src' => 'https://cdn.test/resources/favicon/icon-512x512-maskable.png',
            'sizes' => '512x512',
            'type' => 'image/png',
            'purpose' => 'maskable',
        ]);
});

it('serves browserconfig.xml as application/xml', function () {
    config()->set('pwa-favicon.enabled', true);

    PwaFavicon::routes();

    $response = $this->get('/browserconfig.xml');

    $response->assertOk();

    expect($response->headers->get('Content-Type'))
        ->toContain('application/xml');

    expect($response->getContent())
        ->toContain('<browserconfig>')
        ->toContain('square150x150logo');
});

it('does not register the routes when disabled', function () {
    config()->set('pwa-favicon.enabled', false);

    $this->get('/manifest.json')->assertNotFound();
    $this->get('/browserconfig.xml')->assertNotFound();
    $this->get('/favicon.ico')->assertNotFound();
});

it('serves the favicon.ico when a favicon path is configured', function () {
    config()->set('pwa-favicon.enabled', true);
    config()->set('pwa-favicon.favicon', 'resources/favicon/favicon.ico');

    PwaFavicon::routes();

    $response = $this->get('/favicon.ico');

    $response->assertOk();

    expect($response->headers->get('Content-Type'))->toContain('image/x-icon');
});

it('builds apple touch icon head links for every iOS size', function () {
    $links = PwaFavicon::appleHeadLinks();

    expect($links)->toHaveCount(9);

    expect($links[0])->toMatchArray([
        'rel' => 'apple-touch-icon',
        'sizes' => '57x57',
        'href' => 'https://cdn.test/resources/favicon/apple-icon-57x57.png',
    ]);
});
