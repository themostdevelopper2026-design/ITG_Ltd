<?php
// backend/app/Models/Utilisateur.php — Modèle Eloquent Laravel

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Utilisateur extends Model
{
    protected $table = 'utilisateurs';
    public $timestamps = false;

    protected $fillable = [
        'nom', 'prenom', 'email', 'telephone', 'mot_de_passe',
        'role', 'departement_id', 'photo_url', 'actif'
    ];

    protected $hidden = ['mot_de_passe'];

    public function departement()
    {
        return $this->belongsTo(Departement::class, 'departement_id');
    }

    public function projets()
    {
        return $this->hasMany(Projet::class, 'chef_projet_id');
    }

    public function taches()
    {
        return $this->hasMany(Tache::class, 'assigne_a');
    }
}
