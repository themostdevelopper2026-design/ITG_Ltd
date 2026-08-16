<?php
// backend/app/Models/User.php — Modèle Eloquent Laravel pour la table utilisateurs ITG

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    /**
     * Nom de la table dans la base de données.
     * Laravel utilise 'users' par défaut, mais notre table s'appelle 'utilisateurs'.
     */
    protected $table = 'utilisateurs';

    /**
     * Colonnes remplissables (protège contre l'injection de masse).
     */
    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'telephone',
        'mot_de_passe',
        'role',
        'departement_id',
        'photo_url',
    ];

    /**
     * Champs à masquer dans les sérialisations JSON.
     */
    protected $hidden = [
        'mot_de_passe',
    ];

    /**
     * Casts automatiques.
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ─── Relations ──────────────────────────────────────────────────────────

    /**
     * Un utilisateur appartient à un département.
     */
    public function departement(): BelongsTo
    {
        return $this->belongsTo(Departement::class, 'departement_id');
    }

    /**
     * Un utilisateur peut être chef de plusieurs projets.
     */
    public function projetsChef(): HasMany
    {
        return $this->hasMany(Projet::class, 'chef_projet_id');
    }

    /**
     * Un utilisateur peut avoir plusieurs tâches assignées.
     */
    public function taches(): HasMany
    {
        return $this->hasMany(Tache::class, 'assigne_a');
    }

    /**
     * Journal d'activités de l'utilisateur.
     */
    public function activites(): HasMany
    {
        return $this->hasMany(ActiviteJournal::class, 'utilisateur_id');
    }

    // ─── Accesseurs ─────────────────────────────────────────────────────────

    /**
     * Retourne le nom complet prénom + nom.
     */
    public function getNomCompletAttribute(): string
    {
        return "{$this->prenom} {$this->nom}";
    }

    /**
     * Vérifie si l'utilisateur est administrateur.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Vérifie si l'utilisateur est chef de projet.
     */
    public function isChefProjet(): bool
    {
        return $this->role === 'chef_projet';
    }
}
