<?php
// backend/public/index.php — Serveur & APIs REST PHP / PDO MySQL pour ITG-PULSE (8 Menus)

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Configuration PDO MySQL XAMPP (Port: 3307 | User: TheBest | Database: itg_platform)
$dbHost = '127.0.0.1';
$dbPort = '3307';
$dbName = 'itg_platform';
$dbUser = 'TheBest';
$dbPass = 'The Best2026';

try {
    $pdo = new PDO("mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Connexion PDO MySQL échouée : " . $e->getMessage()]);
    exit;
}

// ------------------------------------------------------------
// AUTHENTIFICATION
// ------------------------------------------------------------
if ($uri === '/api/auth/login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("SELECT u.*, d.nom as departement_nom FROM utilisateurs u LEFT JOIN departements d ON u.departement_id = d.id WHERE u.email = ? AND u.mot_de_passe = ?");
    $stmt->execute([$input['email'] ?? '', $input['password'] ?? '']);
    $user = $stmt->fetch();

    if ($user) {
        unset($user['mot_de_passe']);
        echo json_encode(["success" => true, "token" => "token_" . $user['id'] . "_" . time(), "user" => $user]);
    } else {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Email ou mot de passe incorrect"]);
    }
    exit;
}

// ------------------------------------------------------------
// MENU 1: TABLEAU DE BORD (DASHBOARD ITG-PULSE)
// ------------------------------------------------------------
if ($uri === '/api/pulse/stats' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $ca = $pdo->query("SELECT COALESCE(SUM(montant), 125450000) as total FROM factures WHERE statut = 'payee'")->fetch()['total'];
    $projCount = $pdo->query("SELECT COUNT(*) as total FROM projets WHERE statut = 'en_cours'")->fetch()['total'];
    $tachesCount = $pdo->query("SELECT COUNT(*) as total FROM taches WHERE statut != 'termine'")->fetch()['total'];

    echo json_encode([
        "chiffre_affaires" => (float)($ca ?: 125450000),
        "projets_en_cours" => (int)($projCount ?: 28),
        "taches_en_cours" => (int)($tachesCount ?: 156),
        "taux_occupation" => 78
    ]);
    exit;
}

if ($uri === '/api/pulse/activites' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query("SELECT a.*, u.prenom, u.nom FROM activites_journal a LEFT JOIN utilisateurs u ON a.utilisateur_id = u.id ORDER BY a.date_activite DESC LIMIT 10");
    echo json_encode($stmt->fetchAll());
    exit;
}

if ($uri === '/api/pulse/projets-en-cours' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query("SELECT p.*, c.nom_entreprise as client_nom FROM projets p LEFT JOIN clients c ON p.client_id = c.id WHERE p.statut = 'en_cours' LIMIT 5");
    echo json_encode($stmt->fetchAll());
    exit;
}

if ($uri === '/api/pulse/repartition-departements' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query("SELECT d.nom, COUNT(p.id) as count FROM departements d LEFT JOIN projets p ON d.id = p.departement_id GROUP BY d.id, d.nom");
    echo json_encode($stmt->fetchAll());
    exit;
}

// ------------------------------------------------------------
// MENU 2: PROJETS
// ------------------------------------------------------------
if ($uri === '/api/projets' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query("SELECT p.*, c.nom_entreprise as client_nom, CONCAT(u.prenom, ' ', u.nom) as chef_nom FROM projets p LEFT JOIN clients c ON p.client_id = c.id LEFT JOIN utilisateurs u ON p.chef_projet_id = u.id ORDER BY p.id DESC");
    echo json_encode($stmt->fetchAll());
    exit;
}

if ($uri === '/api/projets' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("INSERT INTO projets (nom, description, client_id, chef_projet_id, departement_id, budget, date_debut, date_fin_prevue) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $input['nom'] ?? 'Nouveau Projet',
        $input['description'] ?? '',
        $input['client_id'] ?? 1,
        $input['chef_projet_id'] ?? 1,
        $input['departement_id'] ?? 1,
        $input['budget'] ?? 0,
        $input['date_debut'] ?? null,
        $input['date_fin_prevue'] ?? null
    ]);
    $newId = $pdo->lastInsertId();

    $stmt2 = $pdo->prepare("INSERT INTO activites_journal (utilisateur_id, type_activite, reference_table, reference_id, description) VALUES (1, 'projet_cree', 'projets', ?, ?)");
    $stmt2->execute([$newId, "Nouveau projet créé: " . ($input['nom'] ?? '')]);

    http_response_code(201);
    echo json_encode(["success" => true, "id" => $newId]);
    exit;
}

// ------------------------------------------------------------
// MENU 3: TÂCHES
// ------------------------------------------------------------
if ($uri === '/api/taches' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query("SELECT t.*, p.nom as projet_nom, CONCAT(u.prenom, ' ', u.nom) as assigne_nom FROM taches t LEFT JOIN projets p ON t.projet_id = p.id LEFT JOIN utilisateurs u ON t.assigne_a = u.id ORDER BY t.id DESC");
    echo json_encode($stmt->fetchAll());
    exit;
}

if ($uri === '/api/taches' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("INSERT INTO taches (projet_id, titre, description, assigne_a, statut, priorite, date_echeance) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $input['projet_id'] ?? 1,
        $input['titre'] ?? 'Nouvelle tâche',
        $input['description'] ?? '',
        $input['assigne_a'] ?? 1,
        $input['statut'] ?? 'a_faire',
        $input['priorite'] ?? 'moyenne',
        $input['date_echeance'] ?? null
    ]);
    $newId = $pdo->lastInsertId();

    $stmt2 = $pdo->prepare("INSERT INTO activites_journal (utilisateur_id, type_activite, reference_table, reference_id, description) VALUES (1, 'tache_assignee', 'taches', ?, ?)");
    $stmt2->execute([$newId, "Nouvelle tâche assignée: " . ($input['titre'] ?? '')]);

    http_response_code(201);
    echo json_encode(["success" => true, "id" => $newId]);
    exit;
}

// ------------------------------------------------------------
// MENU 4: CRM
// ------------------------------------------------------------
if ($uri === '/api/crm' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $leads = $pdo->query("SELECT * FROM leads ORDER BY id DESC")->fetchAll();
    $clients = $pdo->query("SELECT * FROM clients ORDER BY id DESC")->fetchAll();
    echo json_encode(["leads" => $leads, "clients" => $clients]);
    exit;
}

// ------------------------------------------------------------
// MENU 5: FORMATIONS (ITG ACADEMY)
// ------------------------------------------------------------
if ($uri === '/api/formations' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $formations = $pdo->query("SELECT * FROM formations")->fetchAll();
    $sessions = $pdo->query("SELECT s.*, f.titre as formation_titre FROM sessions_formation s LEFT JOIN formations f ON s.formation_id = f.id")->fetchAll();
    echo json_encode(["formations" => $formations, "sessions" => $sessions]);
    exit;
}

// ------------------------------------------------------------
// MENU 6: STOCKS
// ------------------------------------------------------------
if ($uri === '/api/stocks' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $produits = $pdo->query("SELECT p.*, c.nom as categorie_nom FROM produits p LEFT JOIN categories_produits c ON p.categorie_id = c.id")->fetchAll();
    echo json_encode($produits);
    exit;
}

// ------------------------------------------------------------
// MENU 7: RAPPORTS
// ------------------------------------------------------------
if ($uri === '/api/rapports' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $factures = $pdo->query("SELECT * FROM factures ORDER BY date_emission DESC")->fetchAll();
    echo json_encode($factures);
    exit;
}

// ------------------------------------------------------------
// MENU 8: PARAMÈTRES
// ------------------------------------------------------------
if ($uri === '/api/parametres' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $utilisateurs = $pdo->query("SELECT u.id, u.nom, u.prenom, u.email, u.role, u.telephone, d.nom as departement_nom FROM utilisateurs u LEFT JOIN departements d ON u.departement_id = d.id")->fetchAll();
    $departements = $pdo->query("SELECT * FROM departements")->fetchAll();
    echo json_encode(["utilisateurs" => $utilisateurs, "departements" => $departements]);
    exit;
}

http_response_code(404);
echo json_encode(["error" => "Route API non trouvée"]);
