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

it('does not register the manifest route when disabled', function () {
    config()->set('pwa-favicon.enabled', false);

    $this->get('/manifest.json')->assertNotFound();
});

it('exposes the manifest theme color', function () {
    config()->set('pwa-favicon.manifest.theme_color', '#123456');

    expect(PwaFavicon::themeColor())->toBe('#123456');
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

it('falls back to app.name for the web-app title when no manifest name is set', function () {
    config()->set('app.name', 'Fallback App');
    config()->set('pwa-favicon.manifest.short_name', null);
    config()->set('pwa-favicon.manifest.name', null);

    expect(PwaFavicon::webAppMeta())
        ->toContain(['name' => 'apple-mobile-web-app-title', 'content' => 'Fallback App']);
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

it('emits the msapplication-config meta pointing at browserconfig.xml via the favicon package', function () {
    $html = view('pwa-favicon::head')->render();

    expect($html)->toContain('<meta name="msapplication-config" content="/browserconfig.xml">');
});

it('lets the browserconfig url be overridden via the favicon package config', function () {
    config()->set('favicon.browserconfig_url', '/pwa/browserconfig.xml');

    $html = view('pwa-favicon::head')->render();

    expect($html)->toContain('<meta name="msapplication-config" content="/pwa/browserconfig.xml">');
});

it('serves the manifest default fields including the fallback id', function () {
    config()->set('pwa-favicon.enabled', true);

    PwaFavicon::routes();

    $response = $this->get('/manifest.json');

    $response->assertOk();

    expect($response->json('name'))->toBe(config('pwa-favicon.manifest.name'));
    expect($response->json('start_url'))->toBe('/?source=pwa');
    expect($response->json('theme_color'))->toBe('#ffffff');
    // No id configured, so it falls back to '/'.
    expect($response->json('id'))->toBe('/');
});

it('keeps a configured manifest id instead of the fallback', function () {
    config()->set('pwa-favicon.enabled', true);
    config()->set('pwa-favicon.manifest.id', '/app');

    PwaFavicon::routes();

    expect($this->get('/manifest.json')->json('id'))->toBe('/app');
});

it('builds android density icons from the manifest icons map', function () {
    config()->set('pwa-favicon.enabled', true);

    PwaFavicon::routes();

    $icons = $this->get('/manifest.json')->json('icons');

    expect($icons)->toContain([
        'src' => 'https://cdn.test/resources/favicon/android-icon-192x192.png',
        'sizes' => '192x192',
        'type' => 'image/png',
        'density' => '4.0',
    ]);
});
