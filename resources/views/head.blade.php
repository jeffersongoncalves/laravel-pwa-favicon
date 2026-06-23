@php
    use JeffersonGoncalves\PwaFavicon\PwaFavicon;

    // All params are optional. Consumers (a Filament panel head hook, a public
    // site layout) render this single view so the PWA <head> stays identical
    // across surfaces — one source of truth, no duplicated tags.
    $themeColor = $themeColor ?? PwaFavicon::themeColor();
    $manifestUrl = $manifestUrl ?? '/manifest.json';
    // URL of the IE/legacy-Edge tile config document served by
    // BrowserConfigController. Overridable via config so a consumer that
    // mounts the route under a prefix can point the meta at the right path.
    $browserConfigUrl = $browserConfigUrl ?? config('pwa-favicon.browserconfig_url', '/browserconfig.xml');
    // The id lets client JS retarget the theme-color on a live light/dark
    // toggle; pass an empty string to omit it.
    $themeColorId = $themeColorId ?? 'theme-color-meta';
    $title = $title ?? null;
@endphp
@foreach (PwaFavicon::iconHeadLinks() as $link)
    <link rel="{{ $link['rel'] }}" type="{{ $link['type'] }}" sizes="{{ $link['sizes'] }}" href="{{ $link['href'] }}">
@endforeach
@foreach (PwaFavicon::appleHeadLinks() as $link)
    <link rel="{{ $link['rel'] }}" sizes="{{ $link['sizes'] }}" href="{{ $link['href'] }}">
@endforeach
<link rel="manifest" href="{{ $manifestUrl }}">
@foreach (PwaFavicon::msApplicationMeta() as $meta)
    <meta name="{{ $meta['name'] }}" content="{{ $meta['content'] }}">
@endforeach
<meta name="msapplication-config" content="{{ $browserConfigUrl }}">
<meta name="theme-color"@if (! empty($themeColorId)) id="{{ $themeColorId }}"@endif content="{{ $themeColor }}">
@foreach (PwaFavicon::webAppMeta($title) as $meta)
    <meta name="{{ $meta['name'] }}" content="{{ $meta['content'] }}">
@endforeach
