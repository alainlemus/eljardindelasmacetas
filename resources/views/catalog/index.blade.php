@extends('layouts.catalog')

@section('title', 'Catálogo de El Jardín de las Macetas')

@section('content')
    <div class="min-h-screen pb-20 md:pb-0">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-primary to-purple-700 text-white py-12 px-4">
            <div class="max-w-7xl mx-auto">
                <h1 class="text-2xl font-bold mb-2">🎉 Catálogo El Jardín de las Macetas</h1>
                <p class="text-white text-opacity-80 text-sm">Figuras Funko Pop convertidas en macetas artesanales</p>
            </div>
        </div>

        {{-- Featured Products --}}
        @if ($featured->count() > 0)
            <section class="py-4">
                <div class="max-w-7xl mx-auto px-4">
                    <h2 class="text-lg font-bold text-dark mb-3">🔥 Destacados</h2>
                    <div class="flex gap-3 overflow-x-auto pb-2 -mx-4 px-4">
                        @foreach ($featured as $product)
                            <a href="{{ route('catalog.product', $product->slug) }}" class="flex-shrink-0 w-36">
                                <div class="bg-white rounded-xl overflow-hidden shadow-md">
                                    <div class="aspect-square bg-gray-100">
                                        @if ($product->image)
                                            <img src="{{ $product->image }}" alt="{{ $product->name }}"
                                                class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-gray-400">
                                                <span class="text-3xl">📦</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="p-2">
                                        <p class="text-dark font-semibold text-xs truncate">{{ $product->name }}</p>
                                        <p class="text-primary font-bold text-sm">${{ number_format($product->price, 2) }}
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
        <section class="py-4 px-4 sticky top-0 bg-gray-50 z-40">
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
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <button type="submit"
                        class="px-4 py-3 bg-primary text-white rounded-xl flex items-center gap-1 text-sm font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <span class="hidden sm:inline">Buscar</span>
                    </button>
                </form>

                {{-- Category Pills - Mobile Horizontal Scroll --}}
                <div class="flex gap-2 mt-3 overflow-x-auto pb-2 -mx-4 px-4">
                    <a href="{{ route('catalog') }}"
                        class="flex-shrink-0 px-3 py-1.5 rounded-full text-xs font-medium transition-colors {{ !request()->has('category') ? 'bg-primary text-white' : 'bg-white text-gray-600 border border-gray-300' }}">
                        Todas
                    </a>
                    @foreach ($categories as $category)
                        <a href="{{ route('catalog', ['category' => $category->slug]) }}"
                            class="flex-shrink-0 px-3 py-1.5 rounded-full text-xs font-medium transition-colors {{ request()->get('category') == $category->slug ? 'bg-primary text-white' : 'bg-white text-gray-600 border border-gray-300' }}">
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
                            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                                <a href="{{ route('catalog.product', $product->slug) }}">
                                    <div class="aspect-square bg-gray-100 relative overflow-hidden">
                                        @if ($product->image)
                                            <img src="{{ $product->image }}" alt="{{ $product->name }}"
                                                class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-gray-300">
                                                <span class="text-4xl">📦</span>
                                            </div>
                                        @endif
                                        @if ($product->is_featured)
                                            <span
                                                class="absolute top-1.5 left-1.5 bg-accent text-white text-xs px-1.5 py-0.5 rounded font-medium">
                                                ★
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
                                                class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center">
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
                                                class="text-primary font-bold text-lg">${{ number_format($product->price, 2) }}</span>
                                            <span
                                                class="text-xs {{ $product->stock > 5 ? 'text-green-600' : ($product->stock > 0 ? 'text-orange-500' : 'text-red-500') }}">
                                                {{ $product->stock > 0 ? 'Stock: ' . $product->stock : 'Sin stock' }}
                                            </span>
                                        </div>
                                    </div>
                                </a>
                                <div class="px-2.5 pb-2.5">
                                    <a href="https://wa.me/?text={{ urlencode('¡Mira esta Funkomaceta! 🎉\n\n' . $product->name . '\n💰 Precio: $' . number_format($product->price, 2) . '\n📦 Stock: ' . $product->stock . '\n🔗 ' . route('catalog.product', $product->slug)) }}"
                                        target="_blank"
                                        class="w-full bg-green-500 hover:bg-green-600 text-white py-2 rounded-lg flex items-center justify-center gap-1.5 text-xs font-medium transition-colors">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
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
                    <div class="text-center py-12">
                        <div class="text-6xl mb-4">📦</div>
                        <h3 class="text-lg font-semibold text-gray-600 mb-2">No se encontraron productos</h3>
                        <p class="text-gray-400 text-sm">Intenta con otra búsqueda o categoría</p>
                        <a href="{{ route('catalog') }}"
                            class="inline-block mt-4 px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium">
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
