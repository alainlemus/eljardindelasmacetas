<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Un solo dominio para todo el sitio: si la petición llega por la variante con o sin
 * "www" del dominio de APP_URL, redirige (301) al dominio de APP_URL conservando ruta
 * y parámetros. Evita que Google indexe el catálogo dos veces y que las URLs canónicas
 * y el sitemap salgan con el dominio equivocado.
 *
 * Cualquier otro dominio (pruebas, IP interna del health check) pasa sin cambios.
 */
class RedirectToCanonicalHost
{
    public function handle(Request $request, Closure $next): Response
    {
        $canonical = parse_url((string) config('app.url'), PHP_URL_HOST);
        $host = $request->getHost();

        if (! $canonical || $host === $canonical || ! $this->isWwwVariant($host, $canonical)) {
            return $next($request);
        }

        $root = rtrim((string) config('app.url'), '/');

        return redirect()->to($root.$request->getRequestUri(), 301);
    }

    private function isWwwVariant(string $host, string $canonical): bool
    {
        return $host === 'www.'.$canonical || 'www.'.$host === $canonical;
    }
}
