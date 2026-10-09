@extends('layouts.catalog')

@section('title', 'Catálogo de El Jardín de las Macetas')

@section('content')
    {{-- Hero --}}
    <section class="mx-auto max-w-6xl px-4 pt-6">
        <div class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-leaf-500 to-leaf-700 px-6 py-8 text-white shadow-lg md:px-12 md:py-12">
            <div class="absolute -right-10 -top-10 h-56 w-56 rounded-full bg-white/10"></div>
            <div class="absolute -bottom-16 right-24 h-40 w-40 rounded-full bg-sun-400/20"></div>
            <div class="relative flex flex-col items-center gap-6 text-center md:flex-row md:text-left">
                <img src="{{ asset('images/logo.png') }}?v={{ filemtime(public_path('images/logo.png')) }}" alt="El Jardín de las Macetas"
                    class="h-36 w-36 shrink-0 rounded-full bg-cream-50 object-contain p-2 shadow-xl md:h-44 md:w-44">
                <div>
                    <h1 class="text-3xl font-semibold leading-tight md:text-5xl">Tus personajes favoritos,<br class="hidden md:block"> ahora con plantitas 🌱</h1>
                    <p class="mt-3 max-w-xl text-white/90 md:text-lg">Figuras Funko Pop convertidas en macetas artesanales. Elige la tuya y pídela por WhatsApp.</p>
                    <a href="#catalogo" class="mt-5 inline-flex min-h-11 items-center rounded-full bg-sun-400 px-6 font-bold text-clay-800 shadow transition hover:bg-white">Ver figuras</a>
                </div>
            </div>
        </div>
    </section>

    {{-- Destacadas --}}
    @if ($featured->isNotEmpty())
        <section class="mx-auto mt-10 max-w-6xl px-4">
            <h2 class="mb-4 text-2xl font-semibold text-leaf-700">★ Destacadas</h2>
            <div class="hide-scrollbar -mx-4 flex snap-x gap-4 overflow-x-auto px-4 pb-2">
                @foreach ($featured as $figure)
                    <div class="w-44 shrink-0 snap-start sm:w-52">@include('catalog.partials.card')</div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Buscador y categorías --}}
    <section id="catalogo" class="sticky top-16 z-40 mt-10 border-y border-cream-200 bg-cream-50/95 py-3 backdrop-blur">
        <div class="mx-auto max-w-6xl px-4">
            <form action="{{ route('catalog') }}" method="GET" class="flex gap-2">
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
                <a href="{{ route('catalog', array_filter(['search' => request('search')])) }}"
                    class="{{ $chip }} {{ request('category') ? 'bg-white text-clay-800 ring-1 ring-cream-200 hover:ring-leaf-500' : 'bg-leaf-500 text-white' }}">Todas</a>
                @foreach ($categories as $category)
                    <a href="{{ route('catalog', array_filter(['category' => $category->slug, 'search' => request('search')])) }}"
                        class="{{ $chip }} {{ request('category') === $category->slug ? 'bg-leaf-500 text-white' : 'bg-white text-clay-800 ring-1 ring-cream-200 hover:ring-leaf-500' }}">{{ $category->name }}</a>
                @endforeach
            </nav>
        </div>
    </section>

    {{-- Figuras --}}
    <section class="mx-auto max-w-6xl px-4 pt-6">
        @if (request('search'))
            <p class="mb-4 text-sm text-clay-800/70">
                {{ $figures->total() }} resultado(s) para “{{ request('search') }}”
                <a href="{{ route('catalog', array_filter(['category' => request('category')])) }}" class="ml-2 font-bold text-leaf-600 underline">Limpiar</a>
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
                <a href="{{ route('catalog') }}" class="mt-5 inline-flex min-h-11 items-center rounded-full bg-leaf-500 px-6 text-sm font-bold text-white hover:bg-leaf-600">Ver todas</a>
            </div>
        @endif
    </section>
@endsection
