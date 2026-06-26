@extends('layouts.catalog')

@section('title', $product->name . ' - Funkomacetas')

@section('content')
<div class="min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="mb-6">
            <ol class="flex items-center gap-2 text-sm">
                <li><a href="{{ route('catalog') }}" class="text-primary hover:underline">Catálogo</a></li>
                <li class="text-gray-400">/</li>
                @if($product->category)
                <li><a href="{{ route('catalog', ['category' => $product->category->slug]) }}" class="text-primary hover:underline">{{ $product->category->name }}</a></li>
                <li class="text-gray-400">/</li>
                @endif
                <li class="text-gray-600">{{ $product->name }}</li>
            </ol>
        </nav>

        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="grid md:grid-cols-2 gap-8 p-6 md:p-8">
                <div class="relative">
                    <div class="aspect-square bg-gray-100 rounded-xl overflow-hidden">
                        @if($product->image)
                        <img src="{{ $product->image }}" alt="{{ $product->name }}"
                             class="w-full h-full object-cover" id="mainImage">
                        @else
                        <div class="w-full h-full flex items-center justify-center text-gray-400">
                            <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        @endif
                    </div>
                    @if($product->is_featured)
                    <span class="absolute top-4 left-4 bg-accent text-white text-sm font-bold px-3 py-1 rounded-full">
                        ★ Destacado
                    </span>
                    @endif
                </div>

                <div class="flex flex-col">
                    @if($product->category)
                    <span class="text-secondary font-medium mb-2">{{ $product->category->name }}</span>
                    @endif
                    <h1 class="text-3xl font-bold text-dark mb-2">{{ $product->name }}</h1>
                    <p class="text-gray-500 text-sm mb-4">SKU: {{ $product->sku }}</p>

                    <div class="mb-6">
                        <span class="text-4xl font-bold text-primary">${{ number_format($product->price, 2) }}</span>
                        @if($product->cost && $product->cost > 0)
                        <span class="text-gray-400 line-through ml-2">${{ number_format($product->cost, 2) }}</span>
                        @endif
                    </div>

                    <div class="mb-6">
                        @if($product->stock > 0)
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full {{ $product->is_low_stock ? 'bg-orange-100 text-orange-700' : 'bg-green-100 text-green-700' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ $product->is_low_stock ? '¡Últimos disponibles!' : 'En stock' }}
                            <span class="font-semibold">({{ $product->stock }} unidades)</span>
                        </span>
                        @else
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-red-100 text-red-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Agotado
                        </span>
                        @endif
                    </div>

                    @if($product->description)
                    <div class="mb-6">
                        <h3 class="font-semibold text-dark mb-2">Descripción</h3>
                        <p class="text-gray-600">{{ $product->description }}</p>
                    </div>
                    @endif

                    @if($product->figure)
                    <div class="mb-6 p-4 bg-purple-50 rounded-lg">
                        <h3 class="font-semibold text-dark mb-1">Figura Funko Pop</h3>
                        <p class="text-gray-600">{{ $product->figure->name }}</p>
                    </div>
                    @endif

                    <div class="mt-auto space-y-3">
                        @if($product->stock > 0)
                        <a href="https://wa.me/?text={{ urlencode('¡Hola! Quiero comprar esta Funkomaceta! 🎉\n\n' . $product->name . '\n💰 Precio: $' . number_format($product->price, 2) . '\n📦 Stock: ' . $product->stock . '\n🔗 ' . route('catalog.product', $product->slug)) }}"
                           target="_blank"
                           class="w-full bg-green-500 hover:bg-green-600 text-white py-4 rounded-xl flex items-center justify-center gap-3 transition-colors text-lg font-semibold">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                            </svg>
                            Comprar por WhatsApp
                        </a>
                        @endif

                        <a href="https://wa.me/?text={{ urlencode('¡Mira esta Funkomaceta! 🎉 ' . $product->name . '\n💰 Precio: $' . number_format($product->price, 2) . '\n🔗 ' . route('catalog.product', $product->slug)) }}"
                           target="_blank"
                           class="w-full border-2 border-primary text-primary hover:bg-primary hover:text-white py-4 rounded-xl flex items-center justify-center gap-3 transition-colors text-lg font-semibold">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                            </svg>
                            Compartir
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @if($relatedProducts->count() > 0)
        <section class="mt-8 md:mt-12">
            <h2 class="text-xl md:text-2xl font-bold text-dark mb-4 md:mb-6">Productos Relacionados</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 md:gap-6">
                @foreach($relatedProducts as $related)
                <a href="{{ route('catalog.product', $related->slug) }}" class="bg-white rounded-xl shadow-sm overflow-hidden group">
                    <div class="aspect-square bg-gray-100">
                        @if($related->image)
                        <img src="{{ $related->image }}" alt="{{ $related->name }}"
                             class="w-full h-full object-cover transition-transform group-hover:scale-110">
                        @else
                        <div class="w-full h-full flex items-center justify-center text-gray-300">
                            <span class="text-4xl">📦</span>
                        </div>
                        @endif
                    </div>
                    <div class="p-3 md:p-4">
                        <h3 class="font-semibold text-dark text-sm truncate">{{ $related->name }}</h3>
                        <p class="text-primary font-bold">${{ number_format($related->price, 2) }}</p>
                    </div>
                </a>
                @endforeach
            </div>
        </section>
        @endif
    </div>
</div>

{{-- Fixed WhatsApp Button - Mobile --}}
@if($product->stock > 0)
<div class="fixed bottom-0 left-0 right-0 bg-primary md:hidden z-50 pb-safe">
    <a href="https://wa.me/?text={{ urlencode('¡Hola! Quiero comprar esta Funkomaceta! 🎉\n\n' . $product->name . '\n💰 Precio: $' . number_format($product->price, 2) . '\n📦 Stock: ' . $product->stock . '\n🔗 ' . route('catalog.product', $product->slug)) }}"
       target="_blank"
       class="flex items-center justify-center gap-2 py-4 text-white font-semibold text-base">
        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
        </svg>
        Comprar por WhatsApp
    </a>
</div>
@endif
@endsection
