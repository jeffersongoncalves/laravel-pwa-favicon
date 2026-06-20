## Laravel PWA Favicon

### Overview
Laravel PWA Favicon turns a Laravel application into an installable Progressive Web App. It registers root-level routes that serve a spec-shaped `manifest.json`, a `browserconfig.xml` for Windows tiles, and a `favicon.ico`, plus a helper for the Apple touch icon `<link>` tags iOS requires.

**Namespace:** `JeffersonGoncalves\PwaFavicon`
**Service Provider:** `PwaFaviconServiceProvider` (auto-discovered)

### Key Concepts
- **Config-driven:** Everything is read from `config('pwa-favicon.*')` (published from `config/pwa-favicon.php`).
- **Enable flag:** `pwa-favicon.enabled` is a master switch — when false, no routes are registered.
- **Routes in packageBooted():** The provider calls `PwaFavicon::routes()` from `packageBooted()`, guarded by the enabled flag.
- **Spec-shaped icons:** The manifest `icons[]` array is built at request time from the `manifest.icons` density map, always appending a 512 `any` master and a 512 `maskable` variant.
- **Vite assets:** Icon URLs are resolved via `Vite::asset(...)`, so the PNGs must live under the consuming app's `resources/favicon/` and be part of its Vite build.

### Registered Routes

| Route | Content-Type | Registered when |
|-------|--------------|-----------------|
| `GET /manifest.json` | `application/manifest+json` | `pwa-favicon.enabled` is true |
| `GET /browserconfig.xml` | `application/xml` | `pwa-favicon.enabled` is true |
| `GET /favicon.ico` | `image/x-icon` | enabled AND `pwa-favicon.favicon` is set |

### Configuration
- `enabled` — master switch for all routes.
- `manifest` — the Web App Manifest payload (name, short_name, description, start_url, scope, display, orientation, theme_color, background_color, lang, dir, categories).
- `manifest.icons` — a `size => density` map; each entry becomes one `android-icon-{size}x{size}.png` manifest icon.
- `favicon` — Vite-resolvable path to the `favicon.ico`; empty/null skips the route.

### Apple Touch Icons

@verbatim
<code-snippet name="apple-head-links" lang="php">
use JeffersonGoncalves\PwaFavicon\PwaFavicon;

@foreach (PwaFavicon::appleHeadLinks() as $link)
    <link rel="{{ $link['rel'] }}" sizes="{{ $link['sizes'] }}" href="{{ $link['href'] }}">
@endforeach
</code-snippet>
@endverbatim

### Conventions
- Read all settings via `config('pwa-favicon.*')`, never hard-code icon paths.
- The `manifest.icons` config carries only Android density hints; never hand-write the full `icons[]` array — `PwaFavicon::pwaIcons()` builds it (512 + maskable included).
- `PwaFavicon` is an abstract class of static methods (`routes()`, `appleHeadLinks()`); it is not instantiated.
- Icon PNGs are the consuming app's responsibility under `resources/favicon/`.
