<?php
// backend/public/index.php — Serveur & APIs REST PHP / PDO MySQL pour ITG-PULSE

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=utf-8");

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

// POST /api/auth/login — Connexion administrateur / staff ITG
if ($uri === '/api/auth/login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input    = json_decode(file_get_contents('php://input'), true);
    $email    = trim($input['email'] ?? '');
    $password = trim($input['password'] ?? '');

    if (!$email || !$password) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Email et mot de passe requis.']);
        exit;
    }

    // Récupérer l'utilisateur par email uniquement (pour comparer le hash ensuite)
    $stmt = $pdo->prepare("
        SELECT u.*, d.nom as departement_nom
        FROM utilisateurs u
        LEFT JOIN departements d ON u.departement_id = d.id
        WHERE u.email = ? AND u.actif = 1
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Vérifier le mot de passe : supporte hash PHP ET texte clair (rétrocompatibilité)
    $passwordOk = false;
    if ($user) {
        $hash = $user['mot_de_passe'];
        if (password_get_info($hash)['algo'] !== 0) {
            // Mot de passe haché avec password_hash()
            $passwordOk = password_verify($password, $hash);
        } else {
            // Mot de passe en clair (ancien ou compte démo direct en DB)
            $passwordOk = ($password === $hash);
        }
    }

    if ($user && $passwordOk) {
        unset($user['mot_de_passe']);
        $token = base64_encode($user['id'] . ':' . time() . ':' . bin2hex(random_bytes(8)));
        echo json_encode([
            'success' => true,
            'token'   => $token,
            'user'    => [
                'id'          => (int)$user['id'],
                'email'       => $user['email'],
                'prenom'      => $user['prenom'],
                'nom'         => $user['nom'],
                'role'        => $user['role'],
                'telephone'   => $user['telephone'] ?? '',
                'photo_url'   => $user['photo_url'] ?? '',
                'departement' => $user['departement_nom'] ?? '',
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Email ou mot de passe incorrect.']);
    }
    exit;
}

// GET /api/auth/check — Vérifier si une session est valide (utilisé au chargement du dashboard)
if ($uri === '/api/auth/check' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    // On vérifie juste que le token existe (système simplifié sans JWT)
    if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
        echo json_encode(['valid' => true]);
    } else {
        http_response_code(401);
        echo json_encode(['valid' => false, 'message' => 'Token manquant ou invalide.']);
    }
    exit;
}

// GET /api/auth/setup-demo — Initialise les comptes de démo en DB (à appeler UNE SEULE FOIS)
if ($uri === '/api/auth/setup-demo' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $secret = $_GET['key'] ?? '';
    if ($secret !== 'ITG_SETUP_2026') {
        http_response_code(403);
        echo json_encode(['error' => 'Clé de sécurité invalide.']);
        exit;
    }

    // S'assurer que les départements existent
    $depts = [
        ['Administration', 'Direction générale et administration'],
        ['Technique',      'Développement, infrastructure et réseau'],
        ['Commercial',     'Ventes, CRM et relation client'],
        ['Formation',      'ITG Academy et formateurs'],
        ['Support',        'SAV, tickets et interventions'],
    ];
    foreach ($depts as [$nom, $desc]) {
        $chk = $pdo->prepare('SELECT id FROM departements WHERE nom = ?');
        $chk->execute([$nom]);
        if (!$chk->fetch()) {
            $pdo->prepare('INSERT INTO departements (nom, description) VALUES (?, ?)')->execute([$nom, $desc]);
        }
    }

    // Récupérer les IDs de département
    $deptIds = [];
    foreach ($pdo->query('SELECT id, nom FROM departements') as $row) {
        $deptIds[$row['nom']] = $row['id'];
    }

    // Utilisateurs de démo à insérer
    $demoUsers = [
        ['email' => 'admin@itg.bi',      'password' => 'admin123',  'prenom' => 'Jean',   'nom' => 'Niyonzima',   'role' => 'admin',       'dept' => 'Administration'],
        ['email' => 'jean@itg.bi',       'password' => 'chef123',   'prenom' => 'Jean',   'nom' => 'Hakizimana',  'role' => 'chef_projet', 'dept' => 'Technique'],
        ['email' => 'commercial@itg.bi', 'password' => 'crm123',    'prenom' => 'Alice',  'nom' => 'Ndayishimiye','role' => 'commercial',  'dept' => 'Commercial'],
        ['email' => 'formateur@itg.bi',  'password' => 'form123',   'prenom' => 'Pierre', 'nom' => 'Nkurunziza',  'role' => 'formateur',   'dept' => 'Formation'],
        ['email' => 'support@itg.bi',    'password' => 'sav123',    'prenom' => 'Marie',  'nom' => 'Uwimana',     'role' => 'technicien',  'dept' => 'Support'],
    ];

    $inserted = [];
    $skipped  = [];
    foreach ($demoUsers as $u) {
        $chk = $pdo->prepare('SELECT id FROM utilisateurs WHERE email = ?');
        $chk->execute([$u['email']]);
        if ($chk->fetch()) {
            $skipped[] = $u['email'];
            continue;
        }
        $hash   = password_hash($u['password'], PASSWORD_DEFAULT);
        $deptId = $deptIds[$u['dept']] ?? null;
        $pdo->prepare("
            INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role, departement_id, actif)
            VALUES (?, ?, ?, ?, ?, ?, 1)
        ")->execute([$u['nom'], $u['prenom'], $u['email'], $hash, $u['role'], $deptId]);
        $inserted[] = $u['email'];
    }

    echo json_encode([
        'success'  => true,
        'message'  => 'Comptes de démo initialisés.',
        'inserted' => $inserted,
        'skipped'  => $skipped,
    ]);
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

// ------------------------------------------------------------
// MODULE ACADEMY — APPRENANTS (Inscription & Connexion)
// ------------------------------------------------------------

// POST /api/apprenants/register — Inscription d'un nouvel apprenant
if ($uri === '/api/apprenants/register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $nom_complet = trim($input['nom_complet'] ?? '');
    $email       = trim($input['email'] ?? '');
    $telephone   = trim($input['telephone'] ?? '');
    $password    = trim($input['password'] ?? '');
    $formation   = trim($input['formation'] ?? '');

    // Validation de base
    if (!$nom_complet || !$email || !$password) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Champs obligatoires manquants (nom, email, mot de passe).']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Adresse email invalide.']);
        exit;
    }

    // Vérifier si l'email existe déjà
    $check = $pdo->prepare("SELECT id FROM apprenants WHERE email = ?");
    $check->execute([$email]);
    if ($check->fetch()) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Un compte avec cet email existe déjà. Veuillez vous connecter.']);
        exit;
    }

    // Séparer nom / prénom (premier mot = prénom, reste = nom)
    $parts  = explode(' ', $nom_complet, 2);
    $prenom = $parts[0];
    $nom    = $parts[1] ?? $parts[0];

    // Hachage du mot de passe
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO apprenants (nom, prenom, email, telephone, mot_de_passe) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$nom, $prenom, $email, $telephone, $hash]);
    $newId = $pdo->lastInsertId();

    // Lier à une formation si précisée (chercher la session ouverte la plus récente)
    if ($formation) {
        $sess = $pdo->prepare("SELECT sf.id FROM sessions_formation sf 
            JOIN formations f ON sf.formation_id = f.id 
            WHERE f.categorie = ? AND sf.statut IN ('planifiee','en_cours') 
            ORDER BY sf.date_debut DESC LIMIT 1");
        $sess->execute([$formation]);
        $session = $sess->fetch();
        if ($session) {
            $ins = $pdo->prepare("INSERT IGNORE INTO inscriptions (apprenant_id, session_id) VALUES (?, ?)");
            $ins->execute([$newId, $session['id']]);
        }
    }

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Inscription réussie ! Bienvenue chez ITG Academy.',
        'apprenant' => [
            'id'     => $newId,
            'nom'    => $nom,
            'prenom' => $prenom,
            'email'  => $email,
        ]
    ]);
    exit;
}

// POST /api/apprenants/login — Connexion d'un apprenant existant
if ($uri === '/api/apprenants/login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input    = json_decode(file_get_contents('php://input'), true);
    $email    = trim($input['email'] ?? '');
    $password = trim($input['password'] ?? '');

    if (!$email || !$password) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Email et mot de passe requis.']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM apprenants WHERE email = ?");
    $stmt->execute([$email]);
    $apprenant = $stmt->fetch();

    if ($apprenant && password_verify($password, $apprenant['mot_de_passe'])) {
        // Récupérer les inscriptions actives
        $inscStmt = $pdo->prepare("
            SELECT i.*, f.titre as formation_titre, sf.date_debut, sf.date_fin
            FROM inscriptions i
            JOIN sessions_formation sf ON i.session_id = sf.id
            JOIN formations f ON sf.formation_id = f.id
            WHERE i.apprenant_id = ?
            ORDER BY i.date_inscription DESC
        ");
        $inscStmt->execute([$apprenant['id']]);
        $inscriptions = $inscStmt->fetchAll();

        echo json_encode([
            'success' => true,
            'token'   => 'student_token_' . $apprenant['id'] . '_' . time(),
            'apprenant' => [
                'id'               => $apprenant['id'],
                'nom'              => $apprenant['nom'],
                'prenom'           => $apprenant['prenom'],
                'email'            => $apprenant['email'],
                'telephone'        => $apprenant['telephone'],
                'date_inscription' => $apprenant['date_inscription'],
                'inscriptions'     => $inscriptions,
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Email ou mot de passe incorrect.']);
    }
    exit;
}

http_response_code(404);
echo json_encode(["error" => "Route API non trouvée"]);
