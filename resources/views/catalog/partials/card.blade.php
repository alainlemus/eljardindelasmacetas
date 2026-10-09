@php($img = $figure->image_url)
<article class="group relative flex flex-col overflow-hidden rounded-3xl border border-cream-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
    <a href="{{ route('catalog.product', $figure->slug) }}" class="flex flex-1 flex-col focus-visible:outline-2 focus-visible:outline-leaf-500">
        <div class="relative aspect-square overflow-hidden bg-cream-100">
            @if ($img)
                <img src="{{ $img }}" alt="{{ $figure->name }}" loading="lazy" decoding="async"
                    class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
            @else
                <div class="flex h-full w-full items-center justify-center">
                    <img src="{{ asset('images/logo.png') }}" alt="" class="h-1/2 w-1/2 object-contain opacity-30 grayscale">
                </div>
            @endif

            <div class="absolute left-2 top-2 flex flex-col gap-1">
                @if ($figure->is_featured)
                    <span class="rounded-full bg-sun-400 px-2.5 py-1 text-[11px] font-extrabold text-clay-800 shadow">★ Destacada</span>
                @endif
                @if ($figure->stock > 0 && $figure->stock <= 3)
                    <span class="rounded-full bg-berry-500 px-2.5 py-1 text-[11px] font-extrabold text-white shadow">¡Últimas {{ $figure->stock }}!</span>
                @endif
            </div>

            @if ($figure->stock <= 0)
                <div class="absolute inset-0 flex items-center justify-center bg-clay-800/30">
                    <span class="rounded-full bg-white px-3 py-1 text-xs font-extrabold text-leaf-700">Sobre pedido</span>
                </div>
            @endif
        </div>

        <div class="flex flex-1 flex-col p-3.5">
            @if ($figure->category)
                <p class="text-[11px] font-bold uppercase tracking-wide text-leaf-600">{{ $figure->category->name }}</p>
            @endif
            <h3 class="mt-0.5 line-clamp-2 text-[15px] font-semibold leading-snug">{{ $figure->name }}</h3>
            <p class="mt-auto pt-2 font-display text-xl font-semibold text-berry-600 tabular-nums">{{ $figure->formatted_price }}</p>
        </div>
    </a>

    <div class="px-3.5 pb-3.5">
        <a href="{{ \App\Models\Figure::whatsappUrl($figure->shareText('¡Mira esta figura de El Jardín de las Macetas!')) }}" target="_blank" rel="noopener"
            class="flex min-h-11 w-full items-center justify-center gap-2 rounded-2xl bg-leaf-50 text-sm font-bold text-leaf-700 transition hover:bg-leaf-500 hover:text-white">
            @include('catalog.partials.whatsapp-icon', ['class' => 'h-4 w-4'])
            Compartir
        </a>
    </div>
</article>
