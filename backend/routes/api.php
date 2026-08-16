<?php
// backend/routes/api.php — Routes API REST Laravel officielles pour ITG Group

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;


/*
|--------------------------------------------------------------------------
| Routes API Authentification
|--------------------------------------------------------------------------
*/
Route::post('/auth/login', [AuthController::class, 'login']);


