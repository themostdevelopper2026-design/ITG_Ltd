<?php
// backend/app/Models/Tache.php — Modèle Eloquent Laravel

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tache extends Model
{
    protected $table = 'taches';
    public $timestamps = false;

    protected $fillable = [
        'projet_id', 'titre', 'description', 'assigne_a',
        'statut', 'priorite', 'date_echeance'
    ];

    public function projet()
    {
        return $this->belongsTo(Projet::class, 'projet_id');
    }

    public function assigne()
    {
        return $this->belongsTo(Utilisateur::class, 'assigne_a');
    }
}
