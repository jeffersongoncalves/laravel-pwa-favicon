## Laravel PWA Favicon

### Overview
Laravel PWA Favicon turns a Laravel application into an installable Progressive Web App. It registers a root-level route that serves a spec-shaped `manifest.json`, and depends on [`jeffersongoncalves/laravel-favicon`](https://github.com/jeffersongoncalves/laravel-favicon) for the `browserconfig.xml`, `favicon.ico`, and Apple touch icon `<link>` tags iOS requires.

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
| `GET /browserconfig.xml` | `application/xml` | `favicon.enabled` is true (laravel-favicon) |
| `GET /favicon.ico` | `image/x-icon` | `favicon.enabled` AND `favicon.favicon` is set (laravel-favicon) |

### Configuration
- `enabled` — master switch for the manifest route.
- `manifest` — the Web App Manifest payload (name, short_name, description, start_url, scope, display, orientation, theme_color, background_color, lang, dir, categories).
- `manifest.icons` — a `size => density` map; each entry becomes one `android-icon-{size}x{size}.png` manifest icon.
- Favicon/browserconfig/tile-color config lives in `config/favicon.php`, owned by the `laravel-favicon` dependency.

### Apple Touch Icons

Apple touch icons, PNG icon links, and msapplication tile metas live in `laravel-favicon`'s `Favicon` class (`appleHeadLinks()`, `iconHeadLinks()`, `msApplicationMeta()`) — `pwa-favicon::head` includes its `favicon::head` view rather than duplicating them.

### Conventions
- Read all settings via `config('pwa-favicon.*')`, never hard-code icon paths.
- The `manifest.icons` config carries only Android density hints; never hand-write the full `icons[]` array — `PwaFavicon::pwaIcons()` builds it (512 + maskable included).
- `PwaFavicon` is an abstract class of static methods (`routes()`, `webAppMeta()`, `themeColor()`); it is not instantiated.
- Icon PNGs are the consuming app's responsibility under `resources/favicon/`.
- Don't reimplement favicon/browserconfig/apple-touch-icon logic here — that belongs in `laravel-favicon`.
