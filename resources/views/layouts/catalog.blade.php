<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="theme-color" content="#6C5CE7">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <title>@yield('title', 'El Jardín de las Macetas - Catálogo')</title>
    <meta name="description"
        content="Catálogo de El Jardín de las Macetas - Figuras Funko Pop convertidas en macetas artesanales">
    <meta name="keywords"
        content="funko pop, macetas artesanales, funkomacetas, decoración, figuras coleccionables, macetas personalizadas">
    <meta name="author" content="El Jardín de las Macetas">
    <meta name="robots" content="index, follow">

    <link rel="canonical" href="{{ url()->current() }}">

    <meta property="og:type" content="website">
    <meta property="og:title" content="@yield('title', 'El Jardín de las Macetas - Catálogo')">
    <meta property="og:description"
        content="Catálogo de El Jardín de las Macetas - Figuras Funko Pop convertidas en macetas artesanales">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="El Jardín de las Macetas">
    <meta property="og:locale" content="es_MX">
    <meta property="og:image" content="{{ asset('images/og-image.jpg') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'El Jardín de las Macetas - Catálogo')">
    <meta name="twitter:description"
        content="Catálogo de El Jardín de las Macetas - Figuras Funko Pop convertidas en macetas artesanales">
    <meta name="twitter:image" content="{{ asset('images/og-image.jpg') }}">

    <link rel="icon" type="image/png" href="{{ asset('storage/favicon-96x96.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('storage/favicon-96x96.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#6C5CE7',
                        secondary: '#00CEC9',
                        accent: '#FD79A8',
                        dark: '#2D3436',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        * {
            -webkit-tap-highlight-color: transparent;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            overscroll-behavior-y: contain;
        }

        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .safe-area-inset-top {
            padding-top: env(safe-area-inset-top);
        }

        .safe-area-inset-bottom {
            padding-bottom: env(safe-area-inset-bottom);
        }

        .pb-safe {
            padding-bottom: max(env(safe-area-inset-bottom), 0.5rem);
        }
    </style>
    @stack('styles')
</head>

<body class="bg-gray-50 font-sans antialiased">
    {{-- Compact Header --}}
    <nav class="bg-white shadow-sm sticky top-0 z-50 safe-area-inset-top border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-20">
                <a href="{{ route('catalog') }}" class="flex items-center gap-3">
                    @if (file_exists(public_path('storage/logo.png')))
                        <img src="{{ asset('images/logo.png') . '?v=' . filemtime(public_path('images/logo.png')) }}"
                            alt="El Jardín de las Macetas"
                            class="w-14 h-14 object-contain rounded-xl shadow-sm">
                    @else
                        <div
                            class="w-14 h-14 bg-gradient-to-br from-primary to-secondary rounded-xl flex items-center justify-center shadow-sm">
                            <span class="text-white font-bold text-base">F</span>
                        </div>
                    @endif
                    <div class="flex flex-col">
                        <span class="font-bold text-base sm:text-lg text-dark leading-tight">El Jardín</span>
                        <span class="text-xs text-gray-500 leading-tight hidden sm:inline">de las Macetas</span>
                    </div>
                </a>

                <div class="flex items-center gap-2">
                    <a href="https://wa.me/?text={{ urlencode('¡Mira mi catálogo de El Jardín de las Macetas! 🎉 ' . route('catalog')) }}"
                        target="_blank"
                        class="bg-green-500 hover:bg-green-600 text-white px-3 py-1.5 rounded-full flex items-center gap-1.5 text-sm font-medium transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                        </svg>
                        <span class="hidden sm:inline">Compartir</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    @yield('content')

    <footer class="bg-dark text-white py-6 mt-8 hidden md:block">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <p class="text-gray-400">© {{ date('Y') }} El Jardín de las Macetas. Todos los derechos reservados.
                </p>
                <p class="text-gray-500 text-sm mt-2">Figuras Funko Pop convertidas en macetas artesanales</p>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>

</html>
