<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Páginas públicas del catálogo: se pueden cachear unos minutos (navegador y CDN).
 * Se registran sin sesión ni cookies (ver routes/web.php), para no crear una fila de
 * sesión por cada visitante o bot y permitir el caché compartido.
 */
class PublicPage
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->isMethodCacheable() && $response->isSuccessful()) {
            $response->headers->set('Cache-Control', 'public, max-age=60, s-maxage=300, stale-while-revalidate=600');
        }

        return $response;
    }
}
