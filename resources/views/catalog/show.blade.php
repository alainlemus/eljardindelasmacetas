@extends('layouts.catalog')

@section('title', $figure->name.' - El Jardín de las Macetas')
@section('description', \Illuminate\Support\Str::limit($figure->description ?: $figure->name.' - maceta artesanal de El Jardín de las Macetas', 150))
@if ($figure->image_url)
    @section('og_image', str_starts_with($figure->image_url, 'http') ? $figure->image_url : url($figure->image_url))
@endif

@section('content')
    @php($gallery = $figure->gallery)
    <div class="mx-auto max-w-6xl px-4 pb-24 pt-6 md:pb-0">
        <nav aria-label="Breadcrumb" class="mb-4 text-sm font-semibold">
            <ol class="flex flex-wrap items-center gap-2 text-clay-800/60">
                <li><a href="{{ route('catalog') }}" class="text-leaf-600 hover:underline">Catálogo</a></li>
                @if ($figure->category)
                    <li aria-hidden="true">/</li>
                    <li><a href="{{ route('catalog', ['category' => $figure->category->slug]) }}" class="text-leaf-600 hover:underline">{{ $figure->category->name }}</a></li>
                @endif
                <li aria-hidden="true">/</li>
                <li aria-current="page" class="truncate">{{ $figure->name }}</li>
            </ol>
        </nav>

        <div class="grid gap-8 rounded-[2rem] border border-cream-200 bg-white p-4 shadow-sm md:grid-cols-2 md:p-8">
            <div>
                <div class="relative aspect-square overflow-hidden rounded-3xl bg-cream-100">
                    @if ($gallery)
                        <img id="mainImage" src="{{ $gallery[0] }}" alt="{{ $figure->name }}" class="h-full w-full object-cover transition-opacity duration-150">
                    @else
                        <div class="flex h-full items-center justify-center"><img src="{{ asset('images/logo.png') }}" alt="" class="h-1/2 w-1/2 object-contain opacity-30 grayscale"></div>
                    @endif
                    @if ($figure->is_featured)
                        <span class="absolute left-3 top-3 rounded-full bg-sun-400 px-3 py-1 text-xs font-extrabold text-clay-800 shadow">★ Destacada</span>
                    @endif
                </div>
                @if (count($gallery) > 1)
                    <div class="hide-scrollbar mt-3 flex gap-2 overflow-x-auto">
                        @foreach ($gallery as $i => $url)
                            <button type="button" data-url="{{ $url }}" aria-label="Ver foto {{ $i + 1 }} de {{ count($gallery) }}"
                                class="thumb h-16 w-16 shrink-0 overflow-hidden rounded-2xl border-2 {{ $i === 0 ? 'border-leaf-500' : 'border-transparent' }} md:h-20 md:w-20">
                                <img src="{{ $url }}" alt="" loading="lazy" class="h-full w-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="flex flex-col">
                @if ($figure->category)
                    <span class="text-sm font-bold uppercase tracking-wide text-leaf-600">{{ $figure->category->name }}</span>
                @endif
                <h1 class="mt-1 text-3xl font-semibold leading-tight md:text-4xl">{{ $figure->name }}</h1>
                <p class="mt-1 text-sm text-clay-800/50">SKU: {{ $figure->sku }}</p>

                <p class="mt-5 font-display text-5xl font-semibold text-berry-600 tabular-nums">{{ $figure->formatted_price }}</p>

                <div class="mt-4">
                    @if ($figure->stock > 0)
                        <span class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-bold {{ $figure->is_low_stock ? 'bg-sun-400/25 text-clay-500' : 'bg-leaf-100 text-leaf-700' }}">
                            ✓ {{ $figure->is_low_stock ? '¡Últimas disponibles!' : 'Disponible' }} · {{ $figure->stock }} pza(s)
                        </span>
                    @else
                        <span class="inline-flex rounded-full bg-sun-400/25 px-4 py-2 text-sm font-bold text-clay-500">Sobre pedido</span>
                    @endif
                </div>

                @if ($figure->description)
                    <div class="mt-6">
                        <h2 class="mb-1 text-lg font-semibold">Descripción</h2>
                        <p class="whitespace-pre-line text-clay-800/80">{{ $figure->description }}</p>
                    </div>
                @endif

                <div class="mt-8 hidden space-y-3 md:block">
                    @include('catalog.partials.actions')
                </div>
            </div>
        </div>

        @if ($relatedFigures->isNotEmpty())
            <section class="mt-12">
                <h2 class="mb-4 text-2xl font-semibold text-leaf-700">También te puede gustar</h2>
                <div class="grid grid-cols-2 gap-3 md:grid-cols-4 md:gap-5">
                    @foreach ($relatedFigures as $related)
                        @include('catalog.partials.card', ['figure' => $related])
                    @endforeach
                </div>
            </section>
        @endif
    </div>

    {{-- Barra fija móvil --}}
    <div class="fixed inset-x-0 bottom-0 z-50 space-y-2 border-t border-cream-200 bg-cream-50/95 p-3 backdrop-blur md:hidden" style="padding-bottom:max(env(safe-area-inset-bottom),.75rem)">
        @include('catalog.partials.actions')
    </div>
@endsection

@push('scripts')
    <script>
        const main = document.getElementById('mainImage');
        document.querySelectorAll('.thumb').forEach(btn => btn.addEventListener('click', () => {
            if (!main) return;
            main.style.opacity = 0;
            setTimeout(() => { main.src = btn.dataset.url; main.style.opacity = 1; }, 150);
            document.querySelectorAll('.thumb').forEach(b => {
                b.classList.toggle('border-leaf-500', b === btn);
                b.classList.toggle('border-transparent', b !== btn);
            });
        }));
    </script>
@endpush
