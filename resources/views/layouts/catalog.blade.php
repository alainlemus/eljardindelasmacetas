<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#3a7f30">
    <title>@yield('title', 'El Jardín de las Macetas - Catálogo')</title>
    @php($metaDescription = trim($__env->yieldContent('description')) ?: 'Figuras Funko Pop convertidas en macetas artesanales. Mira el catálogo de El Jardín de las Macetas.')
    <meta name="description" content="{{ $metaDescription }}">
    <link rel="canonical" href="{{ url()->current() }}">

    <meta property="og:type" content="website">
    <meta property="og:title" content="@yield('title', 'El Jardín de las Macetas - Catálogo')">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="El Jardín de las Macetas">
    <meta property="og:locale" content="es_MX">
    <meta property="og:image" content="@yield('og_image', asset('images/og-image.jpg'))">
    <meta name="twitter:card" content="summary_large_image">

    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('images/favicon/favicon-96x96.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon/favicon-32x32.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/favicon/apple-touch-icon.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/catalog.js'])
    @stack('styles')
</head>

<body class="min-h-screen">
    <header class="sticky top-0 z-50 border-b border-cream-200 bg-cream-50/90 backdrop-blur">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4">
            <a href="{{ route('catalog') }}" class="flex items-center gap-3">
                <img src="{{ asset('images/logo.png') }}?v={{ filemtime(public_path('images/logo.png')) }}" alt="El Jardín de las Macetas" class="h-11 w-11 object-contain">
                <span class="font-display text-lg font-semibold leading-none text-leaf-700">El Jardín<br><span class="text-sm text-clay-500">de las Macetas</span></span>
            </a>
            <a href="{{ \App\Models\Figure::whatsappUrl('¡Mira el catálogo de El Jardín de las Macetas! '.route('catalog')) }}" target="_blank" rel="noopener"
                class="inline-flex min-h-10 items-center gap-2 rounded-full bg-leaf-500 px-4 text-sm font-bold text-white shadow-sm transition hover:bg-leaf-600">
                @include('catalog.partials.whatsapp-icon', ['class' => 'h-4 w-4'])
                <span class="hidden sm:inline">Compartir catálogo</span>
            </a>
        </div>
    </header>

    <main class="fx-layer">@yield('content')</main>

    <footer class="fx-layer mt-12 border-t border-cream-200 bg-cream-100 py-8 text-center text-sm text-clay-800/70">
        <img src="{{ asset('images/logo.png') }}" alt="" class="mx-auto mb-2 h-14 w-14 object-contain">
        <p class="font-display text-base font-semibold text-leaf-700">El Jardín de las Macetas</p>
        <p>Figuras Funko Pop convertidas en macetas artesanales</p>
        <p class="mt-1">© {{ date('Y') }} Todos los derechos reservados <span class="font-mono text-xs" title="Versión del sistema">· {{ \App\Support\AppVersion::label() }}</span></p>
    </footer>

    <button id="fx-toggle" type="button" aria-pressed="true" aria-label="Burbujas y confeti"
        class="fx-toggle fixed bottom-24 left-4 z-50 flex h-11 w-11 items-center justify-center rounded-full bg-white text-xl shadow-lg ring-1 ring-cream-200 md:bottom-5">✨</button>

    @stack('scripts')
</body>

</html>
