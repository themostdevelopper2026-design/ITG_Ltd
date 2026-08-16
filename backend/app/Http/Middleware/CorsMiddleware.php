<?php
// backend/app/Http/Middleware/CorsMiddleware.php
// Middleware CORS pour autoriser les requêtes du frontend vers l'API Laravel
// Enregistrer dans bootstrap/app.php → withMiddleware()

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    /**
     * Autorise les requêtes cross-origin du frontend ITG (HTML statique via XAMPP).
     * Ajouter dans bootstrap/app.php : $middleware->append(CorsMiddleware::class)
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept');

        // Pré-flight OPTIONS
        if ($request->getMethod() === 'OPTIONS') {
            return response('', 200)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept');
        }

        return $response;
    }
}
