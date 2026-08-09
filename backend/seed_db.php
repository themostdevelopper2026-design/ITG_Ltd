<?php
// backend/seed_db.php — Script de remplissage initial pour XAMPP MySQL via PHP PDO

$host = '127.0.0.1';
$port = '3307';
$user = 'TheBest';
$pass = 'The Best2026';
$db   = 'itg_platform';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Remplir si les départements n'existent pas encore
    $count = $pdo->query("SELECT COUNT(*) FROM departements")->fetchColumn();
    if ($count == 0) {
        $pdo->exec("INSERT INTO departements (id, nom, description) VALUES
            (1, 'Technique', 'Ingénierie & Développement Web/Mobile'),
            (2, 'Commercial', 'Gestion Clientèle CRM'),
            (3, 'Formation', 'Pédagogie ITG Academy'),
            (4, 'Support', 'SAV & Maintenance'),
            (5, 'Administration', 'Direction Générale');");

        $pdo->exec("INSERT INTO utilisateurs (id, nom, prenom, email, telephone, mot_de_passe, role, departement_id) VALUES
            (1, 'Niyonzima', 'Jean', 'admin@itg.bi', '+257 79 000 001', 'admin123', 'admin', 5),
            (2, 'Hakizimana', 'Jean', 'jean@itg.bi', '+257 79 000 002', 'chef123', 'chef_projet', 1),
            (3, 'Ndayishimiye', 'Alice', 'commercial@itg.bi', '+257 79 000 003', 'crm123', 'commercial', 2);");

        $pdo->exec("INSERT INTO clients (id, nom_entreprise, contact_nom, email, telephone, ville, statut) VALUES
            (1, 'SOGEA-SATOM', 'Marc Dupont', 'contact@sogea.bi', '+257 22 22 11 00', 'Bujumbura', 'actif'),
            (2, 'BANCOBU', 'Alain Ndaye', 'info@bancobu.bi', '+257 22 26 50 00', 'Bujumbura', 'actif');");

        $pdo->exec("INSERT INTO projets (id, nom, description, client_id, chef_projet_id, departement_id, statut, avancement_pct, budget) VALUES
            (1, 'Refonte plateforme ITG', 'Plateforme de gestion interne', 1, 2, 1, 'en_cours', 60, 45000000.00),
            (2, 'Application mobile CRM', 'App iOS/Android SOGEA', 1, 3, 2, 'en_cours', 45, 32000000.00);");

        $pdo->exec("INSERT INTO taches (id, projet_id, titre, description, assigne_a, statut, priorite) VALUES
            (1, 1, 'Conception maquette UI/UX', 'Maquettes Figma ITG-Pulse', 1, 'en_cours', 'haute'),
            (2, 1, 'Développement API REST PHP', 'Endpoints Laravel/PHP', 2, 'en_cours', 'haute');");

        $pdo->exec("INSERT INTO activites_journal (utilisateur_id, type_activite, reference_table, reference_id, description) VALUES
            (1, 'tache_assignee', 'taches', 1, 'Nouvelle tâche assignée: Conception maquette UI/UX');");

        $pdo->exec("INSERT INTO factures (id, numero, client_id, projet_id, montant, statut) VALUES
            (1, 'FAC-2024-001', 1, 1, 45000000.00, 'payee'),
            (2, 'FAC-2024-002', 2, 2, 33450000.00, 'payee');");

        echo "✅ Données initiales insérées avec succès dans MySQL via PHP PDO !\n";
    } else {
        echo "Données déjà présentes dans la base de données MySQL.\n";
    }
} catch (PDOException $e) {
    echo "❌ Erreur PHP PDO : " . $e->getMessage() . "\n";
}
