---
name: pwa-favicon-development
description: Development guide for laravel-pwa-favicon, a package that serves a spec-shaped PWA manifest.json, browserconfig.xml, favicon.ico, and Apple touch icon head links from config-driven, Vite-resolved icon assets.
---

# PWA Favicon Development Skill

## When to use this skill

- When developing or extending the laravel-pwa-favicon package
- When changing the shape of the generated `manifest.json` (icons, fields)
- When adding or modifying the registered routes
- When adjusting the Apple touch icon or Windows tile output
- When writing tests for the manifest / browserconfig / favicon endpoints
- When debugging Vite asset resolution for the icon paths

## Setup

### Requirements
- PHP 8.2+
- Laravel 11, 12, or 13
- `spatie/laravel-package-tools` ^1.14

### Installation

```bash
composer require jeffersongoncalves/laravel-pwa-favicon
```

Publish the config:

```bash
php artisan vendor:publish --tag="pwa-favicon-config"
```

## Package Structure

```
src/
  PwaFaviconServiceProvider.php   # configurePackage()->name('laravel-pwa-favicon')->hasConfigFile()
                                  # packageBooted() registers routes when enabled
  PwaFavicon.php                  # abstract class of static methods:
                                  #   routes()          -> registers the 3 routes
                                  #   appleHeadLinks()  -> apple-touch-icon <link> data
                                  #   getManifestJson() -> /manifest.json (private)
                                  #   pwaIcons()        -> spec-shaped icons[] (private)
                                  #   getBrowserConfigXml() -> /browserconfig.xml (private)
                                  #   getFavicon()      -> /favicon.ico (private)
config/
  pwa-favicon.php                 # enabled, manifest{...}, favicon
```

## How It Works

### Service Provider

`hasConfigFile()` (no argument) resolves to the package short name — `laravel-pwa-favicon` with the `laravel-` prefix stripped — so the file is `config/pwa-favicon.php` and the config key is `pwa-favicon`.

Routes are registered from `packageBooted()`, guarded by the enabled flag:

```php
public function packageBooted(): void
{
    if (config('pwa-favicon.enabled', false)) {
        PwaFavicon::routes();
    }
}
```

`PwaFavicon::routes()` also re-checks the flag, so it is safe to call directly.

### Manifest icon builder

The `manifest.icons` config is a `size => density` map carrying only Android density hints. The full, installable `icons[]` array is built at request time by `pwaIcons()`:

- one `android-icon-{size}x{size}.png` entry per density-map size,
- a `512x512` master with `purpose: any` (required for the Chrome install prompt),
- a `512x512` with `purpose: maskable` (required for Android 8+ adaptive cropping).

Every `src` goes through `Vite::asset(...)`, so the PNGs must exist under the consuming app's `resources/favicon/` and be part of its Vite build.

### Content types

- `/manifest.json` → `application/manifest+json` (the W3C type, not generic `application/json`).
- `/browserconfig.xml` → `application/xml`.
- `/favicon.ico` → `image/x-icon` (served via `Vite::content(...)`).

## Testing Patterns

The icon paths flow through `Vite::asset()`, which has no compiled manifest in tests — swap the Vite resolver for a fake.

```php
use Illuminate\Foundation\Vite as FoundationVite;
use Illuminate\Support\Facades\Vite;

beforeEach(function () {
    $vite = Mockery::mock(FoundationVite::class);
    $vite->shouldReceive('asset')->andReturnUsing(fn (string $p) => 'https://cdn.test/'.$p);
    $vite->shouldReceive('content')->andReturnUsing(fn (string $p) => 'fake');
    Vite::swap($vite);
});
```

Keep the routes off at boot (set `pwa-favicon.enabled` false in `getEnvironmentSetUp`) and opt in per test:

```php
use JeffersonGoncalves\PwaFavicon\PwaFavicon;

it('serves the manifest with a 512 any and maskable icon', function () {
    config()->set('pwa-favicon.enabled', true);
    PwaFavicon::routes();

    $response = $this->get('/manifest.json');

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('application/manifest+json');

    $icons = $response->json('icons');
    expect($icons)->toContain([
        'src' => 'https://cdn.test/resources/favicon/icon-512x512.png',
        'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any',
    ])->toContain([
        'src' => 'https://cdn.test/resources/favicon/icon-512x512-maskable.png',
        'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable',
    ]);
});

it('404s every route when disabled', function () {
    config()->set('pwa-favicon.enabled', false);
    $this->get('/manifest.json')->assertNotFound();
    $this->get('/browserconfig.xml')->assertNotFound();
});
```

### Running Tests

```bash
# Run all tests
vendor/bin/pest

# Run with coverage
vendor/bin/pest --coverage

# Static analysis
vendor/bin/phpstan analyse

# Code formatting
vendor/bin/pint
```

## Adding a New Manifest Field

1. Add the key under `manifest` in `config/pwa-favicon.php` (with a sensible default).
2. It is emitted automatically — `getManifestJson()` returns `config('pwa-favicon.manifest')` verbatim before overwriting `icons` and defaulting `id`.
3. Add an assertion in the manifest test for the new field.

## Adding a New Icon Variant

1. To add another Android density, add a `size => density` pair to `manifest.icons` and ship `resources/favicon/android-icon-{size}x{size}.png`.
2. To add a new purpose (e.g. `monochrome`), append an entry in `PwaFavicon::pwaIcons()` and ship the asset.
3. Cover the new entry in the manifest test via `toContain([...])`.
```
