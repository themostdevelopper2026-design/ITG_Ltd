<?php
// backend/database/seeders/DatabaseSeeder.php
// Seeder Laravel — Données initiales ITG Platform
// Conversion du script PDO brut (seed_db.php) en Seeder Eloquent Laravel
// Exécuter avec : php artisan db:seed

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Remplir la base de données avec les données initiales ITG Platform.
     * Lance uniquement l'insertion si les tables sont vides.
     */
    public function run(): void
    {
        // ────────────────────────────────────────────
        // 1. Départements
        // ────────────────────────────────────────────
        if (DB::table('departements')->count() === 0) {
            DB::table('departements')->insert([
                ['id' => 1, 'nom' => 'Technique',      'description' => 'Ingénierie & Développement Web/Mobile'],
                ['id' => 2, 'nom' => 'Commercial',     'description' => 'Gestion Clientèle CRM'],
                ['id' => 3, 'nom' => 'Formation',      'description' => 'Pédagogie ITG Academy'],
                ['id' => 4, 'nom' => 'Support',        'description' => 'SAV & Maintenance'],
                ['id' => 5, 'nom' => 'Administration', 'description' => 'Direction Générale'],
            ]);

            $this->command->info('✅ Départements insérés.');
        }

        // ────────────────────────────────────────────
        // 2. Utilisateurs
        // ────────────────────────────────────────────
        if (DB::table('utilisateurs')->count() === 0) {
            DB::table('utilisateurs')->insert([
                [
                    'id'            => 1,
                    'nom'           => 'Niyonzima',
                    'prenom'        => 'Jean',
                    'email'         => 'admin@itg.bi',
                    'telephone'     => '+257 79 000 001',
                    'mot_de_passe'  => 'admin123',  // NOTE: Hacher avec bcrypt en production
                    'role'          => 'admin',
                    'departement_id'=> 5,
                ],
                [
                    'id'            => 2,
                    'nom'           => 'Hakizimana',
                    'prenom'        => 'Jean',
                    'email'         => 'jean@itg.bi',
                    'telephone'     => '+257 79 000 002',
                    'mot_de_passe'  => 'chef123',
                    'role'          => 'chef_projet',
                    'departement_id'=> 1,
                ],
                [
                    'id'            => 3,
                    'nom'           => 'Ndayishimiye',
                    'prenom'        => 'Alice',
                    'email'         => 'commercial@itg.bi',
                    'telephone'     => '+257 79 000 003',
                    'mot_de_passe'  => 'crm123',
                    'role'          => 'commercial',
                    'departement_id'=> 2,
                ],
            ]);

            $this->command->info('✅ Utilisateurs insérés.');
        }

        // ────────────────────────────────────────────
        // 3. Clients
        // ────────────────────────────────────────────
        if (DB::table('clients')->count() === 0) {
            DB::table('clients')->insert([
                [
                    'id'             => 1,
                    'nom_entreprise' => 'SOGEA-SATOM',
                    'contact_nom'    => 'Marc Dupont',
                    'email'          => 'contact@sogea.bi',
                    'telephone'      => '+257 22 22 11 00',
                    'ville'          => 'Bujumbura',
                    'statut'         => 'actif',
                ],
                [
                    'id'             => 2,
                    'nom_entreprise' => 'BANCOBU',
                    'contact_nom'    => 'Alain Ndaye',
                    'email'          => 'info@bancobu.bi',
                    'telephone'      => '+257 22 26 50 00',
                    'ville'          => 'Bujumbura',
                    'statut'         => 'actif',
                ],
            ]);

            $this->command->info('✅ Clients insérés.');
        }

        // ────────────────────────────────────────────
        // 4. Projets
        // ────────────────────────────────────────────
        if (DB::table('projets')->count() === 0) {
            DB::table('projets')->insert([
                [
                    'id'              => 1,
                    'nom'             => 'Refonte plateforme ITG',
                    'description'     => 'Plateforme de gestion interne',
                    'client_id'       => 1,
                    'chef_projet_id'  => 2,
                    'departement_id'  => 1,
                    'statut'          => 'en_cours',
                    'avancement_pct'  => 60,
                    'budget'          => 45000000.00,
                ],
                [
                    'id'              => 2,
                    'nom'             => 'Application mobile CRM',
                    'description'     => 'App iOS/Android SOGEA',
                    'client_id'       => 1,
                    'chef_projet_id'  => 3,
                    'departement_id'  => 2,
                    'statut'          => 'en_cours',
                    'avancement_pct'  => 45,
                    'budget'          => 32000000.00,
                ],
            ]);

            $this->command->info('✅ Projets insérés.');
        }

        // ────────────────────────────────────────────
        // 5. Tâches
        // ────────────────────────────────────────────
        if (DB::table('taches')->count() === 0) {
            DB::table('taches')->insert([
                [
                    'id'          => 1,
                    'projet_id'   => 1,
                    'titre'       => 'Conception maquette UI/UX',
                    'description' => 'Maquettes Figma ITG-Pulse',
                    'assigne_a'   => 1,
                    'statut'      => 'en_cours',
                    'priorite'    => 'haute',
                ],
                [
                    'id'          => 2,
                    'projet_id'   => 1,
                    'titre'       => 'Développement API REST PHP',
                    'description' => 'Endpoints Laravel/PHP',
                    'assigne_a'   => 2,
                    'statut'      => 'en_cours',
                    'priorite'    => 'haute',
                ],
            ]);

            $this->command->info('✅ Tâches insérées.');
        }

        // ────────────────────────────────────────────
        // 6. Journal d'activités
        // ────────────────────────────────────────────
        if (DB::table('activites_journal')->count() === 0) {
            DB::table('activites_journal')->insert([
                [
                    'utilisateur_id'  => 1,
                    'type_activite'   => 'tache_assignee',
                    'reference_table' => 'taches',
                    'reference_id'    => 1,
                    'description'     => 'Nouvelle tâche assignée: Conception maquette UI/UX',
                ],
            ]);

            $this->command->info('✅ Activités insérées.');
        }

        // ────────────────────────────────────────────
        // 7. Factures
        // ────────────────────────────────────────────
        if (DB::table('factures')->count() === 0) {
            DB::table('factures')->insert([
                [
                    'id'         => 1,
                    'numero'     => 'FAC-2024-001',
                    'client_id'  => 1,
                    'projet_id'  => 1,
                    'montant'    => 45000000.00,
                    'statut'     => 'payee',
                ],
                [
                    'id'         => 2,
                    'numero'     => 'FAC-2024-002',
                    'client_id'  => 2,
                    'projet_id'  => 2,
                    'montant'    => 33450000.00,
                    'statut'     => 'payee',
                ],
            ]);

            $this->command->info('✅ Factures insérées.');
        }

        $this->command->info('🎉 Seeding terminé — Base de données ITG Platform initialisée !');
    }
}
