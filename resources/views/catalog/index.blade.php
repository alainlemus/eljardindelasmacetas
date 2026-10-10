@extends('layouts.catalog')

@php
    $activeCategory = request('category') ? $categories->firstWhere('slug', request('category')) : null;
    $pageNumber = $figures->currentPage();
    $canonicalParams = array_filter(['category' => $activeCategory?->slug, 'page' => $pageNumber > 1 ? $pageNumber : null]);
    $title = ($activeCategory ? 'Figuras de '.$activeCategory->name.' | ' : 'Catálogo de macetas Funko Pop | ')
        .config('seo.site_name').($pageNumber > 1 ? ' - Página '.$pageNumber : '');
    $description = $activeCategory
        ? 'Macetas artesanales de '.$activeCategory->name.': figuras Funko Pop convertidas en macetas. Elige la tuya y pídela por WhatsApp.'
        : config('seo.description');
@endphp

@section('title', $title)
@section('description', $description)
@section('canonical', route('home', $canonicalParams))
@if (request()->filled('search'))
    @section('robots', 'noindex')
@endif

@push('jsonld')
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Organization',
                    '@id' => url('/').'#organization',
                    'name' => config('seo.site_name'),
                    'url' => url('/'),
                    'logo' => asset('images/logo.png'),
                    'description' => config('seo.description'),
                ],
                [
                    '@type' => 'WebSite',
                    '@id' => url('/').'#website',
                    'url' => url('/'),
                    'name' => config('seo.site_name'),
                    'inLanguage' => 'es-MX',
                    'publisher' => ['@id' => url('/').'#organization'],
                    'potentialAction' => [
                        '@type' => 'SearchAction',
                        'target' => ['@type' => 'EntryPoint', 'urlTemplate' => url('/').'?search={search_term_string}'],
                        'query-input' => 'required name=search_term_string',
                    ],
                ],
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}
    </script>
@endpush

@section('content')
    {{-- Hero --}}
    <section class="mx-auto max-w-6xl px-4 pt-6" data-welcome>
        <div data-parallax class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-leaf-500 to-leaf-700 px-6 py-8 text-white shadow-lg md:px-12 md:py-12">
            {{-- Círculos que se mueven dentro de la tarjeta (y siguen al cursor) --}}
            <div data-depth="26" class="absolute -right-10 -top-10"><div class="hero-blob h-56 w-56 rounded-full bg-white/10"></div></div>
            <div data-depth="40" class="absolute -bottom-16 right-24"><div class="hero-blob-slow h-40 w-40 rounded-full bg-sun-400/25"></div></div>
            <div data-depth="18" class="absolute left-[38%] top-4"><div class="hero-blob-slow h-16 w-16 rounded-full bg-white/10" style="animation-delay:-6s"></div></div>
            <div data-depth="32" class="absolute -left-8 bottom-6"><div class="hero-blob h-28 w-28 rounded-full bg-berry-500/20" style="animation-delay:-3s"></div></div>

            {{-- Burbujitas que suben --}}
            @foreach ([[8, '9s', '0s', 14, 0.45], [22, '11s', '-4s', 10, 0.35], [47, '8s', '-2s', 18, 0.5], [63, '12s', '-7s', 8, 0.4], [78, '10s', '-5s', 12, 0.45], [90, '9s', '-1s', 16, 0.35]] as [$left, $dur, $delay, $size, $op])
                <span class="hero-bubble absolute bottom-2 rounded-full border border-white/50 bg-white/20"
                    style="left: {{ $left }}%; width: {{ $size }}px; height: {{ $size }}px; --d: {{ $dur }}; --delay: {{ $delay }}; --o: {{ $op }}; --sway: {{ $size }}px"></span>
            @endforeach

            {{-- Hojitas que se mecen --}}
            <span class="hero-leaf pointer-events-none absolute right-8 bottom-6 text-3xl" style="--d:5s" aria-hidden="true">🌿</span>
            <span class="hero-leaf pointer-events-none absolute right-40 top-6 hidden text-2xl md:block" style="--d:7s;--delay:-2s" aria-hidden="true">🌱</span>
            <span class="hero-leaf pointer-events-none absolute left-[46%] bottom-4 hidden text-2xl md:block" style="--d:6s;--delay:-3s" aria-hidden="true">🌸</span>

            <div class="relative flex flex-col items-center gap-6 text-center md:flex-row md:text-left">
                <div data-depth="-14">
                    <img src="{{ asset('images/logo.png') }}?v={{ filemtime(public_path('images/logo.png')) }}" alt="El Jardín de las Macetas" width="176" height="176" fetchpriority="high" data-confetti="big" title="¡Tócame!"
                        class="logo-bob h-36 w-36 shrink-0 rounded-full bg-cream-50 object-contain p-2 shadow-xl md:h-44 md:w-44">
                </div>
                <div>
                    @php($words = explode(' ', 'Tus personajes favoritos, ahora con plantitas'))
                    <h1 class="text-3xl font-semibold leading-tight md:text-5xl" aria-label="Tus personajes favoritos, ahora con plantitas">
                        @foreach ($words as $i => $word)
                            <span class="word" style="--i: {{ $i }}" aria-hidden="true">{{ $word }}</span>
                        @endforeach
                        <span class="word" style="--i: {{ count($words) }}" aria-hidden="true">🌱</span>
                    </h1>
                    <p class="mt-3 max-w-xl text-white/90 md:text-lg word" style="--i: {{ count($words) + 1 }}">Figuras Funko Pop convertidas en macetas artesanales. Elige la tuya y pídela por WhatsApp.</p>
                    <a href="#catalogo" data-confetti="big"
                        class="cta-pulse word mt-5 inline-flex min-h-11 items-center gap-2 rounded-full bg-sun-400 px-6 font-bold text-clay-800 shadow transition hover:-translate-y-0.5 hover:bg-white active:scale-95"
                        style="--i: {{ count($words) + 2 }}">Ver figuras <span class="cta-arrow" aria-hidden="true">↓</span></a>
                </div>
            </div>
        </div>
    </section>

    {{-- Destacadas --}}
    @if ($featured->isNotEmpty())
        <section class="mx-auto mt-10 max-w-6xl px-4">
            <h2 data-reveal class="mb-4 text-2xl font-semibold text-leaf-700">★ Destacadas</h2>
            <div class="hide-scrollbar -mx-4 flex snap-x gap-4 overflow-x-auto px-4 pb-2">
                @foreach ($featured as $figure)
                    <div class="w-44 shrink-0 snap-start sm:w-52" style="--i: {{ $loop->index }}">@include('catalog.partials.card')</div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Buscador y categorías --}}
    <section id="catalogo" class="sticky top-16 z-40 mt-10 border-y border-cream-200 bg-cream-50/95 py-3 backdrop-blur">
        <div class="mx-auto max-w-6xl px-4">
            <form action="{{ route('home') }}" method="GET" class="flex gap-2">
                @if (request('category'))
                    <input type="hidden" name="category" value="{{ request('category') }}">
                @endif
                <label class="sr-only" for="search">Buscar figuras</label>
                <input id="search" type="search" name="search" value="{{ request('search') }}" placeholder="Buscar figuras…"
                    class="min-h-11 flex-1 rounded-full border border-cream-200 bg-white px-5 text-sm shadow-sm focus:border-leaf-500 focus:outline-none focus:ring-2 focus:ring-leaf-500/30">
                <button type="submit" class="min-h-11 rounded-full bg-leaf-500 px-6 text-sm font-bold text-white transition hover:bg-leaf-600">Buscar</button>
            </form>

            <nav class="hide-scrollbar -mx-4 mt-3 flex gap-2 overflow-x-auto px-4" aria-label="Categorías">
                @php($chip = 'shrink-0 rounded-full px-4 py-2 text-sm font-bold transition')
                <a href="{{ route('home', array_filter(['search' => request('search')])) }}"
                    class="{{ $chip }} active:scale-95 {{ request('category') ? 'bg-white text-clay-800 ring-1 ring-cream-200 hover:ring-leaf-500' : 'bg-leaf-500 text-white' }}">Todas</a>
                @foreach ($categories as $category)
                    <a href="{{ route('home', array_filter(['category' => $category->slug, 'search' => request('search')])) }}"
                        class="{{ $chip }} active:scale-95 {{ request('category') === $category->slug ? 'bg-leaf-500 text-white' : 'bg-white text-clay-800 ring-1 ring-cream-200 hover:ring-leaf-500' }}">{{ $category->name }}</a>
                @endforeach
            </nav>
        </div>
    </section>

    {{-- Figuras --}}
    <section class="mx-auto max-w-6xl px-4 pt-6">
        <h2 class="sr-only">{{ $activeCategory ? 'Figuras de '.$activeCategory->name : 'Todas las figuras del catálogo' }}</h2>
        @if (request('search'))
            <p class="mb-4 text-sm text-clay-800/70">
                {{ $figures->total() }} resultado(s) para “{{ request('search') }}”
                <a href="{{ route('home', array_filter(['category' => request('category')])) }}" class="ml-2 font-bold text-leaf-600 underline">Limpiar</a>
            </p>
        @endif

        @if ($figures->isNotEmpty())
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:gap-5 lg:grid-cols-4">
                @foreach ($figures as $figure)
                    @include('catalog.partials.card')
                @endforeach
            </div>

            @if ($figures->hasPages())
                <div class="mt-8">{{ $figures->withQueryString()->links('pagination.catalog') }}</div>
            @endif
        @else
            <div class="rounded-3xl bg-cream-100 px-6 py-16 text-center">
                <img src="{{ asset('images/logo.png') }}" alt="" class="mx-auto mb-4 h-24 w-24 object-contain opacity-60">
                <h2 class="text-xl font-semibold">No encontramos figuras</h2>
                <p class="mt-1 text-sm text-clay-800/70">Prueba con otra palabra o categoría.</p>
                <a href="{{ route('home') }}" class="mt-5 inline-flex min-h-11 items-center rounded-full bg-leaf-500 px-6 text-sm font-bold text-white hover:bg-leaf-600">Ver todas</a>
            </div>
        @endif
    </section>
@endsection
