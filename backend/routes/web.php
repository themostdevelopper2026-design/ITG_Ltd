<?php
// backend/routes/web.php — Routes Web Laravel (ITG Group)
// Ces routes servent les pages HTML via Blade views ou redirigent vers le frontend.

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Route principale — Redirection vers le frontend statique
|--------------------------------------------------------------------------
| Le frontend (index.html, itg-pulse/) est servi directement via XAMPP.
| Ce fichier est requis par Laravel mais n'est pas utilisé activement.
*/
Route::get('/', function () {
    return redirect('/index.html');
});

/*
|--------------------------------------------------------------------------
| Route de health check (utile pour les tests de connectivité)
|--------------------------------------------------------------------------
*/
Route::get('/health', function () {
    return response()->json([
        'status'  => 'ok',
        'app'     => 'ITG Platform Backend',
        'version' => '1.0.0',
        'time'    => now()->toISOString(),
    ]);
});
