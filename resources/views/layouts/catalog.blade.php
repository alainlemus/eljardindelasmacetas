<!DOCTYPE html>
<html lang="es-MX">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#3a7f30">
    @php
        $pageTitle = trim($__env->yieldContent('title')) ?: config('seo.site_name').' - Catálogo';
        $metaDescription = trim($__env->yieldContent('description')) ?: config('seo.description');
        $canonical = trim($__env->yieldContent('canonical')) ?: route('home');
        $ogImage = trim($__env->yieldContent('og_image')) ?: asset('images/og-image.jpg');
        $ogType = trim($__env->yieldContent('og_type')) ?: 'website';
        $noindex = ! config('seo.indexable') || trim($__env->yieldContent('robots')) === 'noindex';
    @endphp
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <meta name="robots" content="{{ $noindex ? 'noindex, nofollow' : 'index, follow, max-image-preview:large' }}">
    <link rel="canonical" href="{{ $canonical }}">

    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:site_name" content="{{ config('seo.site_name') }}">
    <meta property="og:locale" content="{{ config('seo.locale') }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:alt" content="{{ $pageTitle }}">
    @if ($ogImage === asset('images/og-image.jpg'))
        <meta property="og:image:type" content="image/jpeg">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
    @endif
    @yield('og_extra')
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('images/favicon/favicon-96x96.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon/favicon-32x32.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/favicon/apple-touch-icon.png') }}">

    <link rel="preload" href="{{ asset('fonts/site/Nunito-400.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ asset('fonts/site/Fredoka-600.woff2') }}" as="font" type="font/woff2" crossorigin>
    @vite(['resources/css/app.css', 'resources/js/catalog.js'])
    @stack('styles')
    @stack('jsonld')
</head>

<body class="min-h-screen">
    <a href="#contenido" class="sr-only z-[80] rounded-full bg-white px-4 py-2 font-bold text-leaf-700 shadow focus:not-sr-only focus:fixed focus:left-4 focus:top-4">Saltar al contenido</a>

    <header class="sticky top-0 z-50 border-b border-cream-200 bg-cream-50/90 backdrop-blur">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <img src="{{ asset('images/logo.png') }}?v={{ filemtime(public_path('images/logo.png')) }}" alt="El Jardín de las Macetas" width="44" height="44" class="h-11 w-11 object-contain">
                <span class="font-display text-lg font-semibold leading-none text-leaf-700">El Jardín<br><span class="text-sm text-clay-500">de las Macetas</span></span>
            </a>
            <a href="{{ \App\Models\Figure::whatsappUrl('¡Mira el catálogo de El Jardín de las Macetas! '.route('home')) }}" target="_blank" rel="noopener"
                class="inline-flex min-h-10 items-center gap-2 rounded-full bg-leaf-500 px-4 text-sm font-bold text-white shadow-sm transition hover:bg-leaf-600">
                @include('catalog.partials.whatsapp-icon', ['class' => 'h-4 w-4'])
                <span class="hidden sm:inline">Compartir catálogo</span>
            </a>
        </div>
    </header>

    <main id="contenido" class="fx-layer">@yield('content')</main>

    <footer class="fx-layer mt-12 border-t border-cream-200 bg-cream-100 py-8 text-center text-sm text-clay-800/70">
        <img src="{{ asset('images/logo.png') }}" alt="" width="56" height="56" loading="lazy" class="mx-auto mb-2 h-14 w-14 object-contain">
        <p class="font-display text-base font-semibold text-leaf-700">El Jardín de las Macetas</p>
        <p>Figuras Funko Pop convertidas en macetas artesanales</p>
        <p class="mt-1">© {{ date('Y') }} Todos los derechos reservados <span class="font-mono text-xs" title="Versión del sistema">· {{ \App\Support\AppVersion::label() }}</span></p>
    </footer>

    <button id="fx-toggle" type="button" aria-pressed="true" aria-label="Burbujas y confeti"
        class="fx-toggle fixed left-4 z-50 {{ trim($__env->yieldContent('toggle_pos')) ?: 'bottom-5' }} flex h-11 w-11 items-center justify-center rounded-full bg-white text-xl shadow-lg ring-1 ring-cream-200 md:bottom-5!">✨</button>

    @stack('scripts')
</body>

</html>
