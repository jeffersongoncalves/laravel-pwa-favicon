<div class="filament-hidden">

![Laravel PWA Favicon](https://raw.githubusercontent.com/jeffersongoncalves/laravel-pwa-favicon/master/art/jeffersongoncalves-laravel-pwa-favicon.png)

</div>

# Laravel PWA Favicon

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jeffersongoncalves/laravel-pwa-favicon.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/laravel-pwa-favicon)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/jeffersongoncalves/laravel-pwa-favicon/run-tests.yml?branch=master&label=tests&style=flat-square)](https://github.com/jeffersongoncalves/laravel-pwa-favicon/actions?query=workflow%3Arun-tests+branch%3Amaster)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/jeffersongoncalves/laravel-pwa-favicon/fix-php-code-style-issues.yml?branch=master&label=code%20style&style=flat-square)](https://github.com/jeffersongoncalves/laravel-pwa-favicon/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/jeffersongoncalves/laravel-pwa-favicon.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/laravel-pwa-favicon)

This Laravel package serves a spec-shaped PWA `manifest.json` (with Android density icons, a 512px master icon, and a maskable variant), a `browserconfig.xml` for Windows tiles, a `favicon.ico` route, and Apple touch icon head links. It turns any Laravel application into an installable Progressive Web App with sensible, publishable defaults.

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
    'favicon' => 'resources/favicon/favicon.ico',
];
```

## Usage

Once installed, the package registers the following routes at the application root (when `pwa-favicon.enabled` is `true`):

- `GET /manifest.json` — the Web App Manifest (`application/manifest+json`)
- `GET /browserconfig.xml` — Windows tile configuration (`application/xml`)
- `GET /favicon.ico` — the favicon (registered only when `pwa-favicon.favicon` is set)

### Icon assets

Icon URLs in the manifest and tile config are resolved through Vite (`Vite::asset(...)`), so the PNGs must live in your application under `resources/favicon/` and be part of your Vite build. The package expects these files:

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
  apple-icon-57x57.png ... apple-icon-180x180.png
  ms-icon-70x70.png
  ms-icon-150x150.png
  ms-icon-310x310.png
  favicon.ico
```

### Apple touch icons

iOS Safari ignores the manifest `icons` array, so add the Apple touch icon `<link>` tags to your `<head>`:

```php
use JeffersonGoncalves\PwaFavicon\PwaFavicon;

@foreach (PwaFavicon::appleHeadLinks() as $link)
    <link rel="{{ $link['rel'] }}" sizes="{{ $link['sizes'] }}" href="{{ $link['href'] }}">
@endforeach
```

And reference the manifest + tile config in your `<head>`:

```html
<link rel="manifest" href="/manifest.json">
<meta name="msapplication-config" content="/browserconfig.xml">
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
