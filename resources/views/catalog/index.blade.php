@extends('layouts.catalog')

@section('title', 'Catálogo de El Jardín de las Macetas')

@section('content')
    <div class="min-h-screen pb-20 md:pb-0">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-primary to-purple-700 text-white py-10 px-4">
            <div class="max-w-7xl mx-auto flex items-center gap-5">
                <div class="bg-white rounded-2xl p-2 shadow-lg flex-shrink-0">
                    <img src="{{ asset('images/logo.png') . '?v=' . filemtime(public_path('images/logo.png')) }}"
                        alt="El Jardín de las Macetas"
                        class="w-20 h-20 md:w-24 md:h-24 object-contain">
                </div>
                <div class="flex-1">
                    <h1 class="text-2xl md:text-3xl font-bold mb-1">El Jardín de las Macetas</h1>
                    <p class="text-white/95 text-sm md:text-base">Figuras Funko Pop convertidas en macetas artesanales</p>
                </div>
            </div>
        </div>

        {{-- Featured Products --}}
        @if ($featured->count() > 0)
            <section class="py-5">
                <div class="max-w-7xl mx-auto px-4">
                    <h2 class="flex items-center gap-1.5 text-lg font-bold text-dark mb-3">
                        <svg class="w-5 h-5 text-accent" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M12.963 2.286a.75.75 0 00-1.071-.136 9.742 9.742 0 00-3.539 6.176 7.547 7.547 0 01-1.705-1.715.75.75 0 00-1.152-.082A9 9 0 1015.68 4.534a7.46 7.46 0 01-2.717-2.248zM15.75 14.25a3.75 3.75 0 11-7.313-1.172c.628.465 1.35.81 2.133 1a5.99 5.99 0 011.925-3.545 3.75 3.75 0 013.255 3.717z" />
                        </svg>
                        Destacados
                    </h2>
                    <div class="flex gap-3 overflow-x-auto pb-2 -mx-4 px-4 [mask-image:linear-gradient(to_right,black_92%,transparent)]">
                        @foreach ($featured as $product)
                            @php
                                $productImages = array_filter([$product->image, ...($product->images ?? [])]);
                            @endphp
                            <a href="{{ route('catalog.product', $product->slug) }}"
                                class="flex-shrink-0 w-36 group focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 rounded-xl">
                                <div class="bg-white rounded-xl overflow-hidden shadow-md border border-gray-100 transition-shadow group-hover:shadow-lg">
                                    <div class="aspect-square bg-gray-100 relative overflow-hidden">
                                        @if (count($productImages) > 0)
                                            <div class="flex overflow-x-auto snap-x snap-mandatory h-full" style="scrollbar-width: none;">
                                                @foreach ($productImages as $img)
                                                    <img src="{{ \Storage::url($img) }}" alt="{{ $product->name }}"
                                                        class="w-full h-full object-cover flex-shrink-0 snap-start transition-transform duration-300 group-hover:scale-105">
                                                @endforeach
                                            </div>
                                            @if (count($productImages) > 1)
                                                <span class="absolute bottom-1 right-1 flex items-center gap-0.5 bg-black/60 text-white text-[10px] px-1.5 py-0.5 rounded font-medium">
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                                            d="M1 8a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 018.07 3h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0016.07 6H17a2 2 0 012 2v7a2 2 0 01-2 2H3a2 2 0 01-2-2V8zm9 6a3 3 0 100-6 3 3 0 000 6z" />
                                                    </svg>
                                                    {{ count($productImages) }}
                                                </span>
                                            @endif
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-gray-300">
                                                <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                        d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="p-2">
                                        <p class="text-dark font-semibold text-xs truncate">{{ $product->name }}</p>
                                        <p class="text-primary font-bold text-sm tabular-nums">${{ number_format($product->price, 2) }}
                                        </p>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        {{-- Search and Filter --}}
        <section class="py-4 px-4 sticky top-20 bg-gray-50/95 backdrop-blur-sm z-40 border-b border-gray-100">
            <div class="max-w-7xl mx-auto">
                <form action="{{ route('catalog') }}" method="GET" class="flex gap-2">
                    @if (request()->has('category'))
                        <input type="hidden" name="category" value="{{ request()->get('category') }}">
                    @endif
                    <div class="flex-1 relative">
                        <input type="text" name="search" placeholder="Buscar productos..."
                            value="{{ request()->get('search') }}"
                            class="w-full px-4 py-3 pl-10 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                        <svg class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <button type="submit"
                        class="px-4 py-3 bg-primary hover:bg-primary/90 text-white rounded-xl flex items-center justify-center gap-1.5 text-sm font-medium transition-colors cursor-pointer min-w-[44px]">
                        <svg class="w-4 h-4 sm:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <span class="hidden sm:inline">Buscar</span>
                    </button>
                </form>

                {{-- Category Pills - Mobile Horizontal Scroll --}}
                <div class="flex gap-2 mt-3 overflow-x-auto pb-2 -mx-4 px-4 [mask-image:linear-gradient(to_right,black_94%,transparent)]">
                    <a href="{{ route('catalog') }}"
                        class="flex-shrink-0 px-3 py-1.5 rounded-full text-xs font-medium transition-colors {{ !request()->has('category') ? 'bg-primary text-white' : 'bg-white text-gray-600 border border-gray-300 hover:border-primary hover:text-primary' }}">
                        Todas
                    </a>
                    @foreach ($categories as $category)
                        <a href="{{ route('catalog', ['category' => $category->slug]) }}"
                            class="flex-shrink-0 px-3 py-1.5 rounded-full text-xs font-medium transition-colors {{ request()->get('category') == $category->slug ? 'bg-primary text-white' : 'bg-white text-gray-600 border border-gray-300 hover:border-primary hover:text-primary' }}">
                            {{ $category->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Products --}}
        <section class="py-4 px-4">
            <div class="max-w-7xl mx-auto">
                @if (request()->has('search'))
                    <p class="text-gray-500 text-sm mb-3">
                        Resultados para "{{ request()->get('search') }}" ({{ $products->total() }})
                        <a href="{{ route('catalog', ['category' => request()->get('category')]) }}"
                            class="text-primary ml-2">Limpiar</a>
                    </p>
                @endif

                @if ($products->count() > 0)
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 md:gap-4">
                        @foreach ($products as $product)
                            @php
                                $productImages = array_filter([$product->image, ...($product->images ?? [])]);
                            @endphp
                            <div class="group bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden transition-all hover:shadow-lg hover:-translate-y-0.5">
                                <a href="{{ route('catalog.product', $product->slug) }}"
                                    class="block focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-inset">
                                    <div class="aspect-square bg-gray-100 relative overflow-hidden">
                                        @if (count($productImages) > 0)
                                            <div class="flex overflow-x-auto snap-x snap-mandatory h-full" style="scrollbar-width: none;">
                                                @foreach ($productImages as $img)
                                                    <img src="{{ \Storage::url($img) }}" alt="{{ $product->name }}"
                                                        loading="lazy" decoding="async"
                                                        class="w-full h-full object-cover flex-shrink-0 snap-start transition-transform duration-300 group-hover:scale-105">
                                                @endforeach
                                            </div>
                                            @if (count($productImages) > 1)
                                                <span class="absolute bottom-1.5 right-1.5 flex items-center gap-1 bg-black/60 text-white text-xs px-1.5 py-0.5 rounded font-medium">
                                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                                            d="M1 8a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 018.07 3h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0016.07 6H17a2 2 0 012 2v7a2 2 0 01-2 2H3a2 2 0 01-2-2V8zm9 6a3 3 0 100-6 3 3 0 000 6z" />
                                                    </svg>
                                                    {{ count($productImages) }}
                                                </span>
                                            @endif
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-gray-300">
                                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                        d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                                                </svg>
                                            </div>
                                        @endif
                                        @if ($product->is_featured)
                                            <span
                                                class="absolute top-1.5 left-1.5 flex items-center gap-0.5 bg-accent text-white text-xs px-1.5 py-0.5 rounded font-medium">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.958a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.367 2.446a1 1 0 00-.363 1.118l1.287 3.957c.3.922-.755 1.688-1.538 1.118l-3.367-2.446a1 1 0 00-1.176 0l-3.367 2.446c-.783.57-1.838-.196-1.538-1.118l1.287-3.957a1 1 0 00-.363-1.118L2.063 9.385c-.784-.57-.38-1.81.588-1.81h4.163a1 1 0 00.95-.69l1.285-3.958z" />
                                                </svg>
                                                Destacado
                                            </span>
                                        @endif
                                        @if ($product->stock <= 3 && $product->stock > 0)
                                            <span
                                                class="absolute top-1.5 right-1.5 bg-orange-500 text-white text-xs px-1.5 py-0.5 rounded font-medium">
                                                ¡Últimos!
                                            </span>
                                        @endif
                                        @if ($product->stock == 0)
                                            <div
                                                class="absolute inset-0 bg-black/50 flex items-center justify-center">
                                                <span
                                                    class="bg-red-500 text-white text-xs px-2 py-1 rounded font-bold">Agotado</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="p-2.5">
                                        @if ($product->category)
                                            <p class="text-xs text-secondary font-medium mb-0.5">
                                                {{ $product->category->name }}</p>
                                        @endif
                                        <h3 class="font-semibold text-dark text-sm line-clamp-2 mb-1">{{ $product->name }}
                                        </h3>
                                        <p class="text-xs text-gray-400 mb-1.5">SKU: {{ $product->sku }}</p>
                                        <div class="flex items-center justify-between">
                                            <span
                                                class="text-primary font-bold text-lg tabular-nums">${{ number_format($product->price, 2) }}</span>
                                            <span
                                                class="text-xs font-medium {{ $product->stock > 5 ? 'text-green-600' : ($product->stock > 0 ? 'text-orange-500' : 'text-red-500') }}">
                                                {{ $product->stock > 0 ? 'Stock: ' . $product->stock : 'Sin stock' }}
                                            </span>
                                        </div>
                                    </div>
                                </a>
                                <div class="px-2.5 pb-2.5">
                                    <a href="https://wa.me/?text={{ urlencode('¡Mira esta Funkomaceta! 🎉\n\n' . $product->name . '\n💰 Precio: $' . number_format($product->price, 2) . '\n📦 Stock: ' . $product->stock . '\n🔗 ' . route('catalog.product', $product->slug)) }}"
                                        target="_blank"
                                        class="w-full min-h-[44px] bg-green-500 hover:bg-green-600 text-white rounded-lg flex items-center justify-center gap-1.5 text-xs font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-green-600 focus-visible:ring-offset-1">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path
                                                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                                        </svg>
                                        WhatsApp
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if ($products->hasPages())
                        <div class="mt-6">
                            {{ $products->withQueryString()->links('pagination::tailwind') }}
                        </div>
                    @endif
                @else
                    <div class="text-center py-16 px-4">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center text-gray-300">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-dark mb-1">No se encontraron productos</h3>
                        <p class="text-gray-500 text-sm">
                            @if (request()->has('search'))
                                No hay resultados para "{{ request()->get('search') }}". Prueba con otra palabra o revisa las categorías.
                            @else
                                Prueba con otra categoría o vuelve a intentarlo más tarde.
                            @endif
                        </p>
                        <a href="{{ route('catalog') }}"
                            class="inline-block mt-5 px-5 py-2.5 bg-primary hover:bg-primary/90 text-white rounded-lg text-sm font-medium transition-colors">
                            Ver todos los productos
                        </a>
                    </div>
                @endif
            </div>
        </section>
    </div>

    {{-- Fixed WhatsApp Share Button - Mobile --}}
    <div class="fixed bottom-0 left-0 right-0 bg-primary md:hidden z-50">
        <a href="https://wa.me/?text={{ urlencode('¡Mira el catálogo completo de El Jardín de las Macetas! 🎉 ' . route('catalog')) }}"
            target="_blank" class="flex items-center justify-center gap-2 py-3 text-white font-semibold">
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                <path
                    d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
            </svg>
            Compartir por WhatsApp
        </a>
    </div>
@endsection
