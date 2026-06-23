<?php

declare(strict_types=1);

namespace JeffersonGoncalves\PwaFavicon;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Vite;
use JeffersonGoncalves\PwaFavicon\Http\Controllers\BrowserConfigController;
use JeffersonGoncalves\PwaFavicon\Http\Controllers\FaviconController;
use JeffersonGoncalves\PwaFavicon\Http\Controllers\ManifestController;

abstract class PwaFavicon
{
    public static function routes(): void
    {
        if (config('pwa-favicon.enabled', false)) {
            // Invokable controllers (not closures) so the consuming app can
            // run `php artisan route:cache` in production — closures throw a
            // "Unable to prepare route for serialization" error there.
            Route::get('manifest.json', ManifestController::class);
            Route::get('browserconfig.xml', BrowserConfigController::class);
            if (! empty(config('pwa-favicon.favicon'))) {
                Route::get('favicon.ico', FaviconController::class);
            }
        }
    }

    /**
     * Apple touch icons for the iOS Safari "Add to Home Screen" PWA install
     * path. iOS ignores the standard manifest icons array, so these
     * `apple-touch-icon` `<link>` tags must be present in <head>.
     *
     * @return array<int, array{rel: string, sizes: string, href: string}>
     */
    public static function appleHeadLinks(): array
    {
        $sizes = ['57x57', '60x60', '72x72', '76x76', '114x114', '120x120', '144x144', '152x152', '180x180'];

        $links = [];

        foreach ($sizes as $size) {
            $links[] = [
                'rel' => 'apple-touch-icon',
                'sizes' => $size,
                'href' => Vite::asset("resources/favicon/apple-icon-{$size}.png"),
            ];
        }

        return $links;
    }

    /**
     * Standard `<link rel="icon">` PNG tags (favicon shown in the browser tab
     * and bookmarks). Covers the Android 192 master plus the 32/96/16 desktop
     * sizes from the realfavicongenerator asset set.
     *
     * @return array<int, array{rel: string, type: string, sizes: string, href: string}>
     */
    public static function iconHeadLinks(): array
    {
        return [
            ['rel' => 'icon', 'type' => 'image/png', 'sizes' => '192x192', 'href' => Vite::asset('resources/favicon/android-icon-192x192.png')],
            ['rel' => 'icon', 'type' => 'image/png', 'sizes' => '32x32', 'href' => Vite::asset('resources/favicon/favicon-32x32.png')],
            ['rel' => 'icon', 'type' => 'image/png', 'sizes' => '96x96', 'href' => Vite::asset('resources/favicon/favicon-96x96.png')],
            ['rel' => 'icon', 'type' => 'image/png', 'sizes' => '16x16', 'href' => Vite::asset('resources/favicon/favicon-16x16.png')],
        ];
    }

    /**
     * Legacy Windows pinned-tile metas (`msapplication-*`).
     *
     * @return array<int, array{name: string, content: string}>
     */
    public static function msApplicationMeta(): array
    {
        return [
            ['name' => 'msapplication-TileColor', 'content' => self::tileColor()],
            ['name' => 'msapplication-TileImage', 'content' => Vite::asset('resources/favicon/ms-icon-144x144.png')],
        ];
    }

    /**
     * Mobile web-app capability metas for the Android + iOS "Add to Home
     * Screen" install path. `$title` overrides the home-screen label; when
     * null it falls back to the manifest short_name/name.
     *
     * @return array<int, array{name: string, content: string}>
     */
    public static function webAppMeta(?string $title = null): array
    {
        // `config(key, default)` only returns the default when the key is
        // absent — an explicit `null`/empty value comes back as-is, so chain
        // the fallbacks with `?:` to skip blanks down to app.name.
        $title ??= (string) (config('pwa-favicon.manifest.short_name')
            ?: config('pwa-favicon.manifest.name')
            ?: config('app.name', 'Laravel'));

        return [
            ['name' => 'mobile-web-app-capable', 'content' => 'yes'],
            ['name' => 'apple-mobile-web-app-capable', 'content' => 'yes'],
            ['name' => 'apple-mobile-web-app-title', 'content' => $title],
            ['name' => 'apple-mobile-web-app-status-bar-style', 'content' => (string) config('pwa-favicon.apple_status_bar_style', 'black-translucent')],
        ];
    }

    /**
     * Default PWA theme-color (the top browser-chrome tint — Android address
     * bar / iOS status bar). Consumers that switch it per theme should pass
     * their own value to the `pwa-favicon::head` view instead.
     */
    public static function themeColor(): string
    {
        return (string) config('pwa-favicon.manifest.theme_color', '#ffffff');
    }

    private static function tileColor(): string
    {
        return (string) config('pwa-favicon.tile_color', '#ffffff');
    }

    public static function getManifestJson(): JsonResponse
    {
        $manifest = config('pwa-favicon.manifest', []);

        // The PHP-keyed `icons` map ('192' => '4.0') only carries Android
        // density hints; the spec-shaped icon array (with 512 + maskable +
        // proper MIME types) is built here so the manifest endpoint always
        // emits a fully PWA-installable response regardless of what's in
        // the legacy config key.
        $manifest['icons'] = self::pwaIcons();

        // `id` locks the manifest identity so the browser doesn't treat a
        // future `start_url` tweak as a brand new installable app. Falls
        // back to `/` which is stable across releases.
        $manifest['id'] = $manifest['id'] ?? '/';

        // The W3C-blessed media type is `application/manifest+json`, not
        // the generic `application/json` that `response()->json()` ships
        // by default. Lighthouse and Chrome both accept either, but the
        // strict type makes the audit clean and lines up with the spec.
        return response()->json($manifest)
            ->header('Content-Type', 'application/manifest+json');
    }

    /**
     * Build the full `icons[]` array for `/manifest.json`. Covers every
     * Android size we keep on disk, the 512 master (required for the
     * Chrome install prompt), and a maskable 512 (required for proper
     * adaptive-icon cropping on Android 8+).
     *
     * @return array<int, array{src: string, sizes: string, type: string, purpose?: string, density?: string}>
     */
    private static function pwaIcons(): array
    {
        $densities = config('pwa-favicon.manifest.icons', []);
        $icons = [];

        foreach ($densities as $size => $density) {
            $icons[] = [
                'src' => Vite::asset('resources/favicon/android-icon-'.$size.'x'.$size.'.png'),
                'sizes' => $size.'x'.$size,
                'type' => 'image/png',
                'density' => (string) $density,
            ];
        }

        // 512px master + maskable variant — both required for a Lighthouse
        // PWA score above 90 and for the OS to render a proper adaptive
        // icon rather than padding the largest legacy size.
        $icons[] = [
            'src' => Vite::asset('resources/favicon/icon-512x512.png'),
            'sizes' => '512x512',
            'type' => 'image/png',
            'purpose' => 'any',
        ];

        $icons[] = [
            'src' => Vite::asset('resources/favicon/icon-512x512-maskable.png'),
            'sizes' => '512x512',
            'type' => 'image/png',
            'purpose' => 'maskable',
        ];

        return $icons;
    }

    public static function getBrowserConfigXml(): Response
    {
        // Every value is interpolated into XML, so escape with ENT_XML1 to
        // keep a stray `&`/`<`/`"` in a Vite URL or a configured tile colour
        // from producing a malformed document.
        $esc = static fn (string $v): string => htmlspecialchars($v, ENT_XML1 | ENT_QUOTES, 'UTF-8');

        $square70x70logo = $esc(Vite::asset('resources/favicon/ms-icon-70x70.png'));
        $square150x150logo = $esc(Vite::asset('resources/favicon/ms-icon-150x150.png'));
        $square310x310logo = $esc(Vite::asset('resources/favicon/ms-icon-310x310.png'));
        $tileColor = $esc(self::tileColor());
        $xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>
<browserconfig>
    <msapplication>
        <tile>
            <square70x70logo src=\"{$square70x70logo}\"/>
            <square150x150logo src=\"{$square150x150logo}\"/>
            <square310x310logo src=\"{$square310x310logo}\"/>
            <TileColor>{$tileColor}</TileColor>
        </tile>
    </msapplication>
</browserconfig>";

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    public static function getFavicon(): Response
    {
        return response(Vite::content((string) config('pwa-favicon.favicon')), 200, ['Content-Type' => 'image/x-icon']);
    }
}
