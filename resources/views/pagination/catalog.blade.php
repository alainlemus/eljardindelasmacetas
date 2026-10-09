@if ($paginator->hasPages())
    @php
        $current = $paginator->currentPage();
        $last = $paginator->lastPage();
        // Ventana de páginas: primera, última y 1 alrededor de la actual.
        $pages = collect([1, $last, $current - 1, $current, $current + 1])
            ->filter(fn ($p) => $p >= 1 && $p <= $last)->unique()->sort()->values();
        $base = 'inline-flex h-11 min-w-11 items-center justify-center rounded-full px-4 text-sm font-bold transition';
    @endphp

    <nav role="navigation" aria-label="Paginación" class="flex flex-col items-center gap-3">
        <div class="flex flex-wrap items-center justify-center gap-2">
            @if ($paginator->onFirstPage())
                <span class="{{ $base }} cursor-not-allowed bg-cream-100 text-clay-800/30" aria-disabled="true">← Anterior</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $base }} bg-white text-leaf-700 ring-1 ring-cream-200 hover:bg-leaf-500 hover:text-white">← Anterior</a>
            @endif

            @php($prev = null)
            @foreach ($pages as $page)
                @if ($prev && $page - $prev > 1)
                    <span class="px-1 text-clay-800/40" aria-hidden="true">…</span>
                @endif
                @if ($page === $current)
                    <span aria-current="page" class="{{ $base }} bg-leaf-500 text-white shadow">{{ $page }}</span>
                @else
                    <a href="{{ $paginator->url($page) }}" aria-label="Ir a la página {{ $page }}" class="{{ $base }} hidden bg-white text-clay-800 ring-1 ring-cream-200 hover:bg-leaf-100 sm:inline-flex">{{ $page }}</a>
                @endif
                @php($prev = $page)
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $base }} bg-white text-leaf-700 ring-1 ring-cream-200 hover:bg-leaf-500 hover:text-white">Siguiente →</a>
            @else
                <span class="{{ $base }} cursor-not-allowed bg-cream-100 text-clay-800/30" aria-disabled="true">Siguiente →</span>
            @endif
        </div>

        <p class="text-sm text-clay-800/60">
            Página {{ $current }} de {{ $last }} · Mostrando {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} de {{ $paginator->total() }} figuras
        </p>
    </nav>
@endif
