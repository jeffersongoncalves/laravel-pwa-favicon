# Changelog

All notable changes to `laravel-pwa-favicon` will be documented in this file.

## v2.0.0 - 2026-08-27

### Breaking Changes

Favicon, browserconfig.xml, and icon head-link logic moved out to the new
[jeffersongoncalves/laravel-favicon](https://github.com/jeffersongoncalves/laravel-favicon)
package, now a dependency of this one.

- `PwaFavicon::appleHeadLinks()`, `iconHeadLinks()`, `msApplicationMeta()`,
  `getBrowserConfigXml()`, `getFavicon()` removed — use
  `JeffersonGoncalves\Favicon\Favicon` instead.
- `pwa-favicon.favicon`, `pwa-favicon.tile_color`, `pwa-favicon.browserconfig_url`
  config keys moved to `config/favicon.php` (the favicon package).
- The `pwa-favicon::head` view now includes `favicon::head` for icon markup
  instead of rendering it inline — no template changes needed for consumers.
- This package now only registers `GET /manifest.json`; `/favicon.ico` and
  `/browserconfig.xml` are registered independently by `laravel-favicon`.

Run `composer update` after upgrading; `jeffersongoncalves/laravel-favicon`
installs automatically as a new dependency.

## v1.0.1 - 2026-06-21

### What's Changed

* build(deps): Bump actions/checkout from 6 to 7 by @dependabot[bot] in https://github.com/jeffersongoncalves/laravel-pwa-favicon/pull/1

### New Contributors

* @dependabot[bot] made their first contribution in https://github.com/jeffersongoncalves/laravel-pwa-favicon/pull/1

**Full Changelog**: https://github.com/jeffersongoncalves/laravel-pwa-favicon/compare/v1.0.0...v1.0.1

## v1.0.0 - 2026-06-20

Initial release.
