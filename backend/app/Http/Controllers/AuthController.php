<?php
// backend/app/Http/Controllers/AuthController.php — Contrôleur Authentification Laravel

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');

        $user = DB::table('utilisateurs as u')
            ->leftJoin('departements as d', 'u.departement_id', '=', 'd.id')
            ->where('u.email', $email)
            ->where('u.mot_de_passe', $password)
            ->select('u.*', 'd.nom as departement_nom')
            ->first();

        if ($user) {
            return response()->json([
                'success' => true,
                'token' => 'token_' . $user->id . '_' . time(),
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'prenom' => $user->prenom,
                    'nom' => $user->nom,
                    'role' => $user->role,
                    'departement' => $user->departement_nom
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Email ou mot de passe incorrect'
        ], 401);
    }
}
