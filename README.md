<div class="filament-hidden">

![Laravel PWA Favicon](https://raw.githubusercontent.com/jeffersongoncalves/laravel-pwa-favicon/master/art/jeffersongoncalves-laravel-pwa-favicon.png)

</div>

# Laravel PWA Favicon

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jeffersongoncalves/laravel-pwa-favicon.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/laravel-pwa-favicon)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/jeffersongoncalves/laravel-pwa-favicon/run-tests.yml?branch=master&label=tests&style=flat-square)](https://github.com/jeffersongoncalves/laravel-pwa-favicon/actions?query=workflow%3Arun-tests+branch%3Amaster)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/jeffersongoncalves/laravel-pwa-favicon/fix-php-code-style-issues.yml?branch=master&label=code%20style&style=flat-square)](https://github.com/jeffersongoncalves/laravel-pwa-favicon/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/jeffersongoncalves/laravel-pwa-favicon.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/laravel-pwa-favicon)

This Laravel package serves a spec-shaped PWA `manifest.json` (with Android density icons, a 512px master icon, and a maskable variant) on top of [`jeffersongoncalves/laravel-favicon`](https://github.com/jeffersongoncalves/laravel-favicon)'s `favicon.ico` route, `browserconfig.xml`, and Apple touch icon head links. It turns any Laravel application into an installable Progressive Web App with sensible, publishable defaults.

## Installation

You can install the package via composer:

```bash
composer require jeffersongoncalves/laravel-pwa-favicon
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="pwa-favicon-config"
```

This is the contents of the published config file:

```php
return [
    'enabled' => true,
    'manifest' => [
        'name' => env('APP_NAME', 'Laravel'),
        'short_name' => env('APP_NAME', 'Laravel'),
        'description' => 'A Progressive Web App built with Laravel.',
        'start_url' => '/?source=pwa',
        'scope' => '/',
        'display' => 'standalone',
        'orientation' => 'any',
        'theme_color' => '#ffffff',
        'background_color' => '#ffffff',
        'lang' => 'en',
        'dir' => 'ltr',
        'categories' => ['productivity'],
        'icons' => [
            '36' => '0.75',
            '48' => '1.0',
            '72' => '1.5',
            '96' => '2.0',
            '144' => '3.0',
            '192' => '4.0',
        ],
    ],
    'apple_status_bar_style' => 'black-translucent',
];
```

`jeffersongoncalves/laravel-favicon` is a dependency and is installed automatically. Its own config (`config/favicon.php`) controls the `/favicon.ico` route, `/browserconfig.xml`, and the tile color — see [its README](https://github.com/jeffersongoncalves/laravel-favicon) for details.

## Usage

Once installed, the package registers the following route at the application root (when `pwa-favicon.enabled` is `true`):

- `GET /manifest.json` — the Web App Manifest (`application/manifest+json`)

`laravel-favicon` independently registers `GET /browserconfig.xml` and `GET /favicon.ico` (when its own `favicon.enabled`/`favicon.favicon` config allows it).

### The `pwa-favicon::head` Blade view

The headline feature is a single Blade view that renders every PWA `<head>` tag —
icon links, Apple touch icons, and `msapplication-*` tile metas (via `favicon::head`),
plus the manifest link, `theme-color` meta, and web-app capability metas — in one
place. Include it once in your layout's `<head>` so the markup stays identical
across surfaces (a public site layout, a Filament panel head hook, etc.):

```blade
<head>
    {{-- ... --}}
    @include('pwa-favicon::head')
</head>
```

All parameters are optional:

| Param          | Default                          | Purpose                                                                 |
| -------------- | -------------------------------- | ----------------------------------------------------------------------- |
| `themeColor`   | `PwaFavicon::themeColor()`       | The `theme-color` meta value (browser-chrome tint).                     |
| `manifestUrl`  | `/manifest.json`                 | `href` of the `<link rel="manifest">` tag.                              |
| `themeColorId` | `theme-color-meta`               | `id` on the `theme-color` meta so client JS can retarget it on a live light/dark toggle. Pass an empty string to omit the `id`. |
| `title`        | manifest `short_name` / `name`   | Overrides the `apple-mobile-web-app-title` home-screen label.           |

```blade
@include('pwa-favicon::head', [
    'themeColor' => '#0B0A09',
    'manifestUrl' => '/manifest.json',
    'themeColorId' => 'theme-color-meta',
    'title' => 'My App',
])
```

### Icon assets

Manifest icon URLs are resolved through Vite (`Vite::asset(...)`), so the PNGs must live in your application under `resources/favicon/` and be part of your Vite build. The manifest icons need:

```
resources/favicon/
  android-icon-36x36.png    (and each size in the `manifest.icons` map)
  android-icon-48x48.png
  android-icon-72x72.png
  android-icon-96x96.png
  android-icon-144x144.png
  android-icon-192x192.png
  icon-512x512.png
  icon-512x512-maskable.png
```

`laravel-favicon` needs the Apple touch icons, desktop favicon PNGs, MS tile icons, and `favicon.ico` — see [its README](https://github.com/jeffersongoncalves/laravel-favicon#icon-assets) for the full list.

Reference the manifest in your `<head>` (or just use the `pwa-favicon::head` view above, which already includes it):

```html
<link rel="manifest" href="/manifest.json">
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Jèfferson Gonçalves](https://github.com/jeffersongoncalves)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
