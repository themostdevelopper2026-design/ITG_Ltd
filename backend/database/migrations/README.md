# 📁 database/migrations/ — Migrations Laravel

Ce dossier contient les fichiers de migration Laravel pour créer la structure de la base de données `itg_platform`.

## Base de données de référence
Le schéma SQL complet est disponible dans : [`ITG_DB_Platform.sql`](../../../ITG_DB_Platform.sql)

## Tables principales

| Table | Description |
|-------|-------------|
| `departements` | Départements de l'entreprise ITG |
| `utilisateurs` | Comptes staff/admin (table principale auth) |
| `clients` | Clients et partenaires ITG |
| `projets` | Projets en cours ou terminés |
| `taches` | Tâches liées aux projets |
| `activites_journal` | Journal d'activités et audit trail |
| `factures` | Factures liées aux projets clients |

## Commandes Laravel

```bash
# Générer une migration
php artisan make:migration create_departements_table

# Exécuter les migrations
php artisan migrate

# Remettre à zéro et re-migrer
php artisan migrate:fresh

# Remplir avec les données initiales
php artisan db:seed
```

> **Note** : En développement avec XAMPP, la base de données peut être importée
> directement via phpMyAdmin depuis le fichier `ITG_DB_Platform.sql`.
