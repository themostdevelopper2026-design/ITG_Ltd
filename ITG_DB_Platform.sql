-- ============================================================
-- BASE DE DONNÉES CENTRALE — ITG (Innovation Technology Group)
-- Couvre : ITG-Pulse (interne), CRM, ITG Academy (formations),
--          Boutique (produits & matériels), Support / SAV
-- SGBD cible :  MySQL 
-- ============================================================


-- ============================================================
-- MODULE 1 — ORGANISATION INTERNE (ITG-Pulse)
-- ============================================================

-- ============================================================
-- MODULE 1 — DEPARTEMENTS & UTILISATEURS
-- ============================================================

CREATE TABLE departements (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nom             VARCHAR(100) NOT NULL,          -- Technique, Commercial, Formation, Support, Administration
    description     TEXT
);

CREATE TABLE utilisateurs (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nom             VARCHAR(100) NOT NULL,
    prenom          VARCHAR(100) NOT NULL,
    email           VARCHAR(150) NOT NULL UNIQUE,
    telephone       VARCHAR(30),
    mot_de_passe    VARCHAR(255) NOT NULL,           -- hash
    role            VARCHAR(50) NOT NULL,            -- admin, directeur, chef_projet, commercial, formateur, technicien, support
    departement_id  INT,
    photo_url       VARCHAR(255),
    actif           BOOLEAN DEFAULT TRUE,
    date_creation   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (departement_id) REFERENCES departements(id)
);

-- ============================================================
-- MODULE 2 — SERVICES (catalogue des 5 pôles ITG)
-- ============================================================

CREATE TABLE services (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nom             VARCHAR(150) NOT NULL,   -- ex: Développement d'applications web
    pole            VARCHAR(50) NOT NULL,    -- developpement, automatisation_ia, securite_reseau, formation, materiel
    description     TEXT
);

-- ============================================================
-- MODULE 3 — CRM (gestion des clients / commercial)
-- ============================================================

CREATE TABLE clients (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nom_entreprise  VARCHAR(150) NOT NULL,
    contact_nom     VARCHAR(150),
    email           VARCHAR(150),
    telephone       VARCHAR(30),
    adresse         VARCHAR(255),
    ville           VARCHAR(100),
    statut          VARCHAR(20) DEFAULT 'prospect',  -- prospect, actif, inactif
    date_creation   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE leads (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nom             VARCHAR(150) NOT NULL,
    entreprise      VARCHAR(150),
    email           VARCHAR(150),
    telephone       VARCHAR(30),
    besoin          VARCHAR(255),                    -- ex: "Infrastructure réseau"
    source          VARCHAR(50),                      -- site_web, recommandation, salon, publicite...
    statut          VARCHAR(30) DEFAULT 'nouveau',    -- nouveau, contacte, qualifie, converti, perdu
    commercial_id   INT,
    date_creation   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (commercial_id) REFERENCES utilisateurs(id)
);

CREATE TABLE devis (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    numero          VARCHAR(30) NOT NULL UNIQUE,
    lead_id         INT,
    client_id       INT,
    service_id      INT,
    montant         DECIMAL(14,2) NOT NULL,
    statut          VARCHAR(30) DEFAULT 'envoye',     -- envoye, converti, refuse, expire
    date_emission   DATE DEFAULT (CURRENT_DATE),
    date_validite   DATE,
    FOREIGN KEY (lead_id) REFERENCES leads(id),
    FOREIGN KEY (client_id) REFERENCES clients(id),
    FOREIGN KEY (service_id) REFERENCES services(id)
);

CREATE TABLE contrats (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    client_id       INT NOT NULL,
    type_contrat    VARCHAR(100),                     -- maintenance, support annuel, développement...
    montant         DECIMAL(14,2),
    date_debut      DATE,
    date_fin        DATE,
    statut          VARCHAR(30) DEFAULT 'actif',       -- actif, expire, resilie
    FOREIGN KEY (client_id) REFERENCES clients(id)
);

CREATE TABLE factures (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    numero          VARCHAR(30) NOT NULL UNIQUE,
    client_id       INT NOT NULL,
    projet_id       INT,
    devis_id        INT,
    montant         DECIMAL(14,2) NOT NULL,
    statut          VARCHAR(30) DEFAULT 'en_attente',  -- payee, en_attente, en_retard
    date_emission   DATE DEFAULT (CURRENT_DATE),
    date_echeance   DATE,
    date_paiement   DATE,
    FOREIGN KEY (client_id) REFERENCES clients(id),
    FOREIGN KEY (projet_id) REFERENCES projets(id),
    FOREIGN KEY (devis_id) REFERENCES devis(id)
);

CREATE TABLE partenaires (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nom             VARCHAR(150) NOT NULL,
    type_partenariat VARCHAR(100),
    email           VARCHAR(150),
    telephone       VARCHAR(30)
);

-- ============================================================
-- SUITE MODULE 1 — PROJETS & TÂCHES (référencent clients / services)
-- ============================================================

CREATE TABLE projets (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    nom                 VARCHAR(200) NOT NULL,
    description         TEXT,
    client_id           INT,
    chef_projet_id      INT,
    departement_id      INT,
    service_id          INT,
    statut              VARCHAR(30) DEFAULT 'en_cours',  -- en_cours, en_pause, termine, annule
    avancement_pct      SMALLINT DEFAULT 0,
    budget              DECIMAL(14,2),
    date_debut          DATE,
    date_fin_prevue     DATE,
    date_fin_reelle     DATE,
    date_creation       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id),
    FOREIGN KEY (chef_projet_id) REFERENCES utilisateurs(id),
    FOREIGN KEY (departement_id) REFERENCES departements(id),
    FOREIGN KEY (service_id) REFERENCES services(id)
);

CREATE TABLE taches (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    projet_id       INT NOT NULL,
    titre           VARCHAR(200) NOT NULL,
    description     TEXT,
    assigne_a       INT,
    statut          VARCHAR(30) DEFAULT 'a_faire',    -- a_faire, en_cours, termine
    priorite        VARCHAR(20) DEFAULT 'moyenne',    -- basse, moyenne, haute
    date_echeance   DATE,
    date_creation   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (projet_id) REFERENCES projets(id) ON DELETE CASCADE,
    FOREIGN KEY (assigne_a) REFERENCES utilisateurs(id)
);

CREATE TABLE activites_journal (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id  INT,
    type_activite   VARCHAR(50),          -- tache_assignee, document_maj, reunion_planifiee, commentaire
    reference_table VARCHAR(50),          -- table concernée (projets, taches, etc.)
    reference_id    INT,
    description     TEXT,
    date_activite   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id)
);

-- ============================================================
-- MODULE 4 — ITG ACADEMY (formations)
-- ============================================================

CREATE TABLE formations (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    titre           VARCHAR(200) NOT NULL,
    categorie       VARCHAR(50) NOT NULL,   -- bureautique, prog_web, prog_mobile, ecommerce, automatisation, cybersecurite, reseau
    description     TEXT,
    niveau          VARCHAR(20),            -- debutant, intermediaire, avance
    duree_heures    INT,
    format          VARCHAR(20),            -- presentiel, en_ligne, hybride
    prix            DECIMAL(12,2)
);

CREATE TABLE sessions_formation (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    formation_id    INT NOT NULL,
    formateur_id    INT,
    date_debut      TIMESTAMP NOT NULL,
    date_fin        TIMESTAMP,
    lieu_ou_lien    VARCHAR(255),
    capacite_max    INT,
    statut          VARCHAR(30) DEFAULT 'planifiee',  -- planifiee, en_cours, terminee, annulee
    FOREIGN KEY (formation_id) REFERENCES formations(id),
    FOREIGN KEY (formateur_id) REFERENCES utilisateurs(id)
);

CREATE TABLE apprenants (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nom             VARCHAR(100) NOT NULL,
    prenom          VARCHAR(100) NOT NULL,
    email           VARCHAR(150) NOT NULL UNIQUE,
    telephone       VARCHAR(30),
    mot_de_passe    VARCHAR(255) NOT NULL,
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE inscriptions (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    apprenant_id        INT NOT NULL,
    session_id          INT NOT NULL,
    statut              VARCHAR(30) DEFAULT 'inscrit',  -- inscrit, en_cours, termine, abandonne
    progression_pct     SMALLINT DEFAULT 0,
    date_inscription    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (apprenant_id) REFERENCES apprenants(id),
    FOREIGN KEY (session_id) REFERENCES sessions_formation(id),
    UNIQUE KEY unique_inscription (apprenant_id, session_id)
);

CREATE TABLE evaluations (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    inscription_id  INT NOT NULL,
    note            DECIMAL(3,1),          -- ex: 4.5 / 5
    commentaire     TEXT,
    date_evaluation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (inscription_id) REFERENCES inscriptions(id)
);

CREATE TABLE certificats (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    apprenant_id        INT NOT NULL,
    formation_id        INT NOT NULL,
    inscription_id      INT,
    numero_certificat   VARCHAR(50) NOT NULL UNIQUE,
    date_obtention      DATE DEFAULT (CURRENT_DATE),
    FOREIGN KEY (apprenant_id) REFERENCES apprenants(id),
    FOREIGN KEY (formation_id) REFERENCES formations(id),
    FOREIGN KEY (inscription_id) REFERENCES inscriptions(id)
);

-- ============================================================
-- MODULE 5 — BOUTIQUE (produits & matériels)
-- ============================================================

CREATE TABLE categories_produits (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nom             VARCHAR(100) NOT NULL   -- Réseau, Serveurs, Accessoires...
);

CREATE TABLE produits (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nom             VARCHAR(200) NOT NULL,
    categorie_id    INT,
    marque          VARCHAR(100),           -- Cisco, Dell, Fortinet, HP, Synology...
    reference       VARCHAR(100),
    prix            DECIMAL(12,2) NOT NULL,
    stock_quantite  INT DEFAULT 0,
    description     TEXT,
    image_url       VARCHAR(255),
    FOREIGN KEY (categorie_id) REFERENCES categories_produits(id)
);

CREATE TABLE promotions (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    produit_id          INT NOT NULL,
    pourcentage_reduction SMALLINT NOT NULL,
    date_debut          DATE,
    date_fin            DATE,
    FOREIGN KEY (produit_id) REFERENCES produits(id)
);

CREATE TABLE commandes (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    numero          VARCHAR(30) NOT NULL UNIQUE,
    client_id       INT NOT NULL,
    statut          VARCHAR(30) DEFAULT 'en_attente',  -- en_attente, expediee, livree, annulee
    montant_total   DECIMAL(14,2),
    date_commande   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id)
);

CREATE TABLE commande_lignes (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    commande_id     INT NOT NULL,
    produit_id      INT NOT NULL,
    quantite        INT NOT NULL DEFAULT 1,
    prix_unitaire   DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (commande_id) REFERENCES commandes(id) ON DELETE CASCADE,
    FOREIGN KEY (produit_id) REFERENCES produits(id)
);

-- ============================================================
-- MODULE 6 — SUPPORT / SAV
-- ============================================================

CREATE TABLE tickets_support (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    numero          VARCHAR(30) NOT NULL UNIQUE,
    client_id       INT NOT NULL,
    sujet           VARCHAR(200) NOT NULL,
    description     TEXT,
    statut          VARCHAR(30) DEFAULT 'ouvert',   -- ouvert, en_attente, resolu
    priorite        VARCHAR(20) DEFAULT 'moyenne',  -- basse, moyenne, haute
    assigne_a       INT,
    date_creation   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_resolution TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id),
    FOREIGN KEY (assigne_a) REFERENCES utilisateurs(id)
);

CREATE TABLE interventions (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id           INT NOT NULL,
    technicien_id       INT,
    date_intervention   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    description         TEXT,
    statut              VARCHAR(30) DEFAULT 'planifiee',  -- planifiee, en_cours, terminee
    FOREIGN KEY (ticket_id) REFERENCES tickets_support(id) ON DELETE CASCADE,
    FOREIGN KEY (technicien_id) REFERENCES utilisateurs(id)
);

CREATE TABLE base_connaissances (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    titre           VARCHAR(200) NOT NULL,
    contenu         TEXT,
    categorie       VARCHAR(100),
    date_creation   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- MODULE 7 — SATISFACTION (utilisée par le SAV et le CRM)
-- ============================================================

CREATE TABLE avis_satisfaction (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    client_id       INT,
    ticket_id       INT,
    note            SMALLINT,          -- ex: sur 5 ou en %
    commentaire     TEXT,
    date_avis       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id),
    FOREIGN KEY (ticket_id) REFERENCES tickets_support(id)
);

-- ============================================================
-- INDEX RECOMMANDÉS (performance des tableaux de bord)
-- ============================================================

CREATE INDEX idx_projets_statut ON projets(statut);
CREATE INDEX idx_taches_projet ON taches(projet_id);
CREATE INDEX idx_taches_assigne ON taches(assigne_a);
CREATE INDEX idx_leads_statut ON leads(statut);
CREATE INDEX idx_devis_statut ON devis(statut);
CREATE INDEX idx_factures_statut ON factures(statut);
CREATE INDEX idx_inscriptions_apprenant ON inscriptions(apprenant_id);
CREATE INDEX idx_inscriptions_session ON inscriptions(session_id);
CREATE INDEX idx_commandes_client ON commandes(client_id);
CREATE INDEX idx_tickets_statut ON tickets_support(statut);
CREATE INDEX idx_tickets_priorite ON tickets_support(priorite);