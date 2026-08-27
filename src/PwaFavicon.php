<?php

declare(strict_types=1);

namespace JeffersonGoncalves\PwaFavicon;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Vite;
use JeffersonGoncalves\PwaFavicon\Http\Controllers\ManifestController;

abstract class PwaFavicon
{
    public static function routes(): void
    {
        if (config('pwa-favicon.enabled', false)) {
            // Invokable controller (not a closure) so the consuming app can
            // run `php artisan route:cache` in production — closures throw a
            // "Unable to prepare route for serialization" error there.
            Route::get('manifest.json', ManifestController::class);
        }
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
}
