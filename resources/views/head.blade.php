@php
    use JeffersonGoncalves\PwaFavicon\PwaFavicon;

    // All params are optional. Consumers (a Filament panel head hook, a public
    // site layout) render this single view so the PWA <head> stays identical
    // across surfaces — one source of truth, no duplicated tags.
    $themeColor = $themeColor ?? PwaFavicon::themeColor();
    $manifestUrl = $manifestUrl ?? '/manifest.json';
    // The id lets client JS retarget the theme-color on a live light/dark
    // toggle; pass an empty string to omit it.
    $themeColorId = $themeColorId ?? 'theme-color-meta';
    $title = $title ?? null;
@endphp
{{-- Icon links, apple-touch-icon links, msapplication-* tile metas, and the
     msapplication-config meta all live in laravel-favicon's head partial. --}}
@include('favicon::head')
<link rel="manifest" href="{{ $manifestUrl }}">
<meta name="theme-color"@if (! empty($themeColorId)) id="{{ $themeColorId }}"@endif content="{{ $themeColor }}">
@foreach (PwaFavicon::webAppMeta($title) as $meta)
    <meta name="{{ $meta['name'] }}" content="{{ $meta['content'] }}">
@endforeach
