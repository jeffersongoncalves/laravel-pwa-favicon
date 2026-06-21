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

it('builds standard png icon head links', function () {
    $links = PwaFavicon::iconHeadLinks();

    expect($links)->toHaveCount(4);
    expect($links[0])->toMatchArray([
        'rel' => 'icon',
        'type' => 'image/png',
        'sizes' => '192x192',
        'href' => 'https://cdn.test/resources/favicon/android-icon-192x192.png',
    ]);
});

it('builds msapplication tile metas using the configured tile color', function () {
    config()->set('pwa-favicon.tile_color', '#0B0A09');

    $metas = PwaFavicon::msApplicationMeta();

    expect($metas)->toContain(['name' => 'msapplication-TileColor', 'content' => '#0B0A09']);
    expect($metas)->toContain([
        'name' => 'msapplication-TileImage',
        'content' => 'https://cdn.test/resources/favicon/ms-icon-144x144.png',
    ]);
});

it('builds web-app metas with the manifest title and configured status bar style', function () {
    config()->set('pwa-favicon.manifest.short_name', 'My App');
    config()->set('pwa-favicon.apple_status_bar_style', 'black-translucent');

    $metas = PwaFavicon::webAppMeta();

    expect($metas)->toContain(['name' => 'apple-mobile-web-app-title', 'content' => 'My App']);
    expect($metas)->toContain(['name' => 'apple-mobile-web-app-status-bar-style', 'content' => 'black-translucent']);
    expect($metas)->toContain(['name' => 'mobile-web-app-capable', 'content' => 'yes']);
});

it('lets a caller override the web-app title', function () {
    expect(PwaFavicon::webAppMeta('Custom'))
        ->toContain(['name' => 'apple-mobile-web-app-title', 'content' => 'Custom']);
});

it('exposes the manifest theme color', function () {
    config()->set('pwa-favicon.manifest.theme_color', '#123456');

    expect(PwaFavicon::themeColor())->toBe('#123456');
});

it('renders the head view with a custom theme color and theme-color id', function () {
    $html = view('pwa-favicon::head', ['themeColor' => '#0B0A09'])->render();

    expect($html)
        ->toContain('<link rel="manifest" href="/manifest.json">')
        ->toContain('rel="apple-touch-icon"')
        ->toContain('rel="icon"')
        ->toContain('name="msapplication-TileColor"')
        ->toContain('name="mobile-web-app-capable"')
        ->toContain('id="theme-color-meta"')
        ->toContain('content="#0B0A09"');
});

it('omits the theme-color id when an empty id is passed', function () {
    $html = view('pwa-favicon::head', ['themeColorId' => ''])->render();

    expect($html)->not->toContain('id="theme-color-meta"');
});
