<?php
// backend/app/Models/Departement.php — Modèle Eloquent Laravel

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departement extends Model
{
    protected $table = 'departements';
    public $timestamps = false;
    protected $fillable = ['nom', 'description'];

    public function utilisateurs()
    {
        return $this->hasMany(Utilisateur::class, 'departement_id');
    }

    public function projets()
    {
        return $this->hasMany(Projet::class, 'departement_id');
    }
}
