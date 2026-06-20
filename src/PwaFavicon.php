<?php

declare(strict_types=1);

namespace JeffersonGoncalves\PwaFavicon;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Vite;

abstract class PwaFavicon
{
    public static function routes(): void
    {
        if (config('pwa-favicon.enabled', false)) {
            Route::any('manifest.json', fn () => self::getManifestJson());
            Route::any('browserconfig.xml', fn () => self::getBrowserConfigXml());
            if (! empty(config('pwa-favicon.favicon'))) {
                Route::any('favicon.ico', fn () => self::getFavicon());
            }
        }
    }

    /**
     * Apple touch icons + startup splash images for the iOS Safari "Add to
     * Home Screen" PWA install path. iOS ignores the standard manifest
     * icons array, so these `<link>` tags must be present in <head>.
     *
     * @return array<int, array{rel: string, sizes?: string, href: string, media?: string}>
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

    private static function getManifestJson(): JsonResponse
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

    private static function getBrowserConfigXml(): Response
    {
        $square70x70logo = Vite::asset('resources/favicon/ms-icon-70x70.png');
        $square150x150logo = Vite::asset('resources/favicon/ms-icon-150x150.png');
        $square310x310logo = Vite::asset('resources/favicon/ms-icon-310x310.png');
        $xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>
<browserconfig>
    <msapplication>
        <tile>
            <square70x70logo src=\"{$square70x70logo}\"/>
            <square150x150logo src=\"{$square150x150logo}\"/>
            <square310x310logo src=\"{$square310x310logo}\"/>
            <TileColor>#ffffff</TileColor>
        </tile>
    </msapplication>
</browserconfig>";

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    private static function getFavicon(): Response
    {
        return response(Vite::content(config('pwa-favicon.favicon')), 200, ['Content-Type' => 'image/x-icon']);
    }
}
