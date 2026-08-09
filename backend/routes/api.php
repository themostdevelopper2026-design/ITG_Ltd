<?php
// backend/routes/api.php — Routes API REST Laravel officielles pour ITG Group

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PulseController;

/*
|--------------------------------------------------------------------------
| Routes API Authentification
|--------------------------------------------------------------------------
*/
Route::post('/auth/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Routes API Module 1: ITG-Pulse (Dashboard Admin, Projets, Tâches)
|--------------------------------------------------------------------------
*/
Route::prefix('pulse')->group(function () {
    Route::get('/stats', [PulseController::class, 'getStats']);
    Route::get('/activites', [PulseController::class, 'getActivites']);
    Route::get('/projets-en-cours', [PulseController::class, 'getProjetsEnCours']);
    Route::get('/repartition-departements', [PulseController::class, 'getRepartitionDepartements']);
});

Route::get('/projets', [PulseController::class, 'getProjets']);
Route::post('/projets', [PulseController::class, 'storeProjet']);
Route::get('/taches', [PulseController::class, 'getTaches']);
