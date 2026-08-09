<?php
// backend/app/Models/Projet.php — Modèle Eloquent Laravel

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Projet extends Model
{
    protected $table = 'projets';
    public $timestamps = false;

    protected $fillable = [
        'nom', 'description', 'client_id', 'chef_projet_id',
        'departement_id', 'service_id', 'statut', 'avancement_pct',
        'budget', 'date_debut', 'date_fin_prevue', 'date_fin_reelle'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function chefProjet()
    {
        return $this->belongsTo(Utilisateur::class, 'chef_projet_id');
    }

    public function departement()
    {
        return $this->belongsTo(Departement::class, 'departement_id');
    }

    public function taches()
    {
        return $this->hasMany(Tache::class, 'projet_id');
    }
}
