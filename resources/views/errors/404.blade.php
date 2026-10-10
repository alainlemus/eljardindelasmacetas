@extends('layouts.catalog')

@section('title', 'Página no encontrada | '.config('seo.site_name'))
@section('description', 'La página que buscas no existe o la figura ya no está disponible.')
@section('robots', 'noindex')

@section('content')
    <div class="mx-auto max-w-xl px-4 py-20 text-center">
        <img src="{{ asset('images/logo.png') }}" alt="" width="140" height="140" class="mx-auto mb-6 h-36 w-36 object-contain opacity-80">
        <h1 class="text-3xl font-semibold text-leaf-700">No encontramos esa página</h1>
        <p class="mt-3 text-clay-800/80">Puede que la figura ya no esté disponible o que el enlace tenga un error. Mira el catálogo completo y encuentra tu maceta favorita.</p>
        <a href="{{ route('home') }}" class="mt-6 inline-flex min-h-11 items-center rounded-full bg-leaf-500 px-6 font-bold text-white shadow transition hover:bg-leaf-600">Ver el catálogo</a>
    </div>
@endsection
