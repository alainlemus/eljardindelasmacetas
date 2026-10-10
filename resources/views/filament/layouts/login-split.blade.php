@php
    $livewire ??= null;
    $renderHookScopes = $livewire?->getRenderHookScopes();
@endphp

<x-filament-panels::layout.base :livewire="$livewire">
    <style>
        @font-face { font-family: 'Fredoka'; font-style: normal; font-weight: 500 700; font-display: swap; src: url('{{ asset('fonts/site/Fredoka-600.woff2') }}') format('woff2'); }
        .jm-split { min-height: 100vh; display: grid; grid-template-columns: 1fr; background: #fffaf0; }
        .dark .jm-split { background: #1c1712; }
        @media (min-width: 900px) { .jm-split { grid-template-columns: minmax(0, 1.05fr) minmax(0, 1fr); } }

        /* ── Sección izquierda: marca ── */
        .jm-brand { position: relative; overflow: hidden; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 1rem; padding: 2rem 1.5rem; text-align: center; color: #fff; background: linear-gradient(145deg, #4c9a3f 0%, #3a7f30 55%, #2f6628 100%); min-height: 220px; }
        @media (min-width: 900px) { .jm-brand { padding: 3rem; min-height: 100vh; gap: 1.5rem; } }
        .jm-brand > * { position: relative; z-index: 2; }
        .jm-logo { width: 120px; height: 120px; border-radius: 9999px; background: #fffaf0; padding: 8px; object-fit: contain; box-shadow: 0 18px 40px -12px rgba(0,0,0,.45); animation: jm-bob 5s ease-in-out infinite; }
        @media (min-width: 900px) { .jm-logo { width: 200px; height: 200px; padding: 12px; } }
        .jm-title { font-family: Fredoka, Nunito, system-ui, sans-serif; font-weight: 600; font-size: 1.5rem; line-height: 1.15; margin: 0; }
        @media (min-width: 900px) { .jm-title { font-size: 2.6rem; } }
        .jm-tag { margin: 0; max-width: 26rem; color: rgba(255,255,255,.88); font-size: 1rem; display: none; }
        @media (min-width: 900px) { .jm-tag { display: block; font-size: 1.1rem; } }
        .jm-chips { display: none; gap: .5rem; flex-wrap: wrap; justify-content: center; }
        @media (min-width: 900px) { .jm-chips { display: flex; } }
        .jm-chip { background: rgba(255,255,255,.16); border: 1px solid rgba(255,255,255,.28); border-radius: 9999px; padding: .35rem .85rem; font-size: .85rem; font-weight: 700; backdrop-filter: blur(4px); }

        .jm-leaf-c { display: none; }
        @media (min-width: 900px) { .jm-leaf-c { display: block; } }

        .jm-blob, .jm-bubble, .jm-leaf { position: absolute; z-index: 1; pointer-events: none; }
        .jm-blob { border-radius: 9999px; animation: jm-drift 16s ease-in-out infinite; }
        .jm-bubble { bottom: -30px; border-radius: 9999px; border: 1px solid rgba(255,255,255,.5); background: rgba(255,255,255,.18); animation: jm-rise var(--d, 10s) ease-in infinite; animation-delay: var(--delay, 0s); opacity: 0; }
        .jm-leaf { font-size: 1.8rem; animation: jm-sway var(--d, 6s) ease-in-out infinite; transform-origin: 50% 100%; }

        @keyframes jm-drift { 0%,100% { transform: translate(0,0) scale(1); } 33% { transform: translate(-30px,24px) scale(1.08); } 66% { transform: translate(20px,-18px) scale(.94); } }
        @keyframes jm-rise { 0% { transform: translateY(0) scale(.6); opacity: 0; } 12% { opacity: .55; } 100% { transform: translateY(-110vh) translateX(18px) scale(1.1); opacity: 0; } }
        @keyframes jm-bob { 0%,100% { transform: translateY(0) rotate(-1.5deg); } 50% { transform: translateY(-10px) rotate(1.5deg); } }
        @keyframes jm-sway { 0%,100% { transform: rotate(-12deg); } 50% { transform: rotate(12deg) translateY(-8px); } }

        /* ── Sección derecha: formulario ── */
        .jm-form { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 2rem 1.25rem; }
        @media (min-width: 900px) { .jm-form { padding: 3rem; } }
        .jm-form-inner { width: 100%; max-width: 26rem; }
        .jm-form .fi-simple-main { background: transparent; box-shadow: none; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; padding: 0; max-width: none; width: 100%; margin: 0; }
        .jm-form .fi-simple-page-content { row-gap: 1.5rem; }
        .jm-form .fi-header-heading, .jm-form .fi-simple-header-heading { font-family: Fredoka, Nunito, system-ui, sans-serif; color: #3a7f30; }
        .dark .jm-form .fi-header-heading, .dark .jm-form .fi-simple-header-heading { color: #8fd07f; }
        .jm-foot { margin-top: 2rem; font-size: .75rem; color: #8a7466; font-family: ui-monospace, monospace; }
        .jm-back { margin-top: .75rem; font-size: .85rem; }
        .jm-back a { color: #3a7f30; font-weight: 700; text-decoration: none; }
        .jm-back a:hover { text-decoration: underline; }

        @media (prefers-reduced-motion: reduce) { .jm-blob, .jm-bubble, .jm-leaf, .jm-logo { animation: none !important; } .jm-bubble { display: none; } }
    </style>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIMPLE_LAYOUT_START, scopes: $renderHookScopes) }}

    <div class="jm-split">
        <aside class="jm-brand" aria-label="El Jardín de las Macetas">
            <span class="jm-blob" style="right:-70px;top:-70px;width:260px;height:260px;background:rgba(255,255,255,.10)"></span>
            <span class="jm-blob" style="left:-60px;bottom:40px;width:200px;height:200px;background:rgba(246,185,59,.22);animation-delay:-5s"></span>
            <span class="jm-blob" style="right:12%;bottom:-60px;width:150px;height:150px;background:rgba(217,58,53,.2);animation-delay:-9s"></span>

            @foreach ([[8, 14, '9s', '0s'], [22, 10, '12s', '-5s'], [40, 18, '10s', '-2s'], [58, 9, '13s', '-8s'], [74, 15, '9s', '-4s'], [90, 12, '11s', '-6s']] as [$left, $size, $dur, $delay])
                <span class="jm-bubble" style="left:{{ $left }}%;width:{{ $size }}px;height:{{ $size }}px;--d:{{ $dur }};--delay:{{ $delay }}"></span>
            @endforeach
            <span class="jm-leaf" style="left:10%;top:14%;--d:6s" aria-hidden="true">🌿</span>
            <span class="jm-leaf" style="right:12%;top:22%;--d:7s;animation-delay:-2s" aria-hidden="true">🌱</span>
            <span class="jm-leaf jm-leaf-c" style="left:16%;bottom:12%;--d:5s;animation-delay:-3s" aria-hidden="true">🌸</span>

            <img class="jm-logo" src="{{ asset('images/logo.png') }}" alt="El Jardín de las Macetas" width="200" height="200">
            <h2 class="jm-title">El Jardín de las Macetas</h2>
            <p class="jm-tag">Administra tu catálogo de figuras Funko Pop convertidas en macetas artesanales.</p>
            <div class="jm-chips" aria-hidden="true">
                <span class="jm-chip">🎭 Figuras</span>
                <span class="jm-chip">🏷️ Categorías</span>
                <span class="jm-chip">📦 Inventario</span>
            </div>
        </aside>

        <section class="jm-form">
            <div class="jm-form-inner">
                <main class="fi-simple-main">
                    {{ $slot }}
                </main>

                <p class="jm-back"><a href="{{ url('/') }}">← Ir al catálogo</a></p>
            </div>
            <p class="jm-foot">{{ \App\Support\AppVersion::label() }}</p>
        </section>
    </div>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::FOOTER, scopes: $renderHookScopes) }}
    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIMPLE_LAYOUT_END, scopes: $renderHookScopes) }}
</x-filament-panels::layout.base>
