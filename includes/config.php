<?php
// =====================================================================
//  BLACK COLLECTION - Configuration générale
// =====================================================================

// Fuseau horaire (Abidjan / GMT)
date_default_timezone_set('Africa/Abidjan');

// Local (XAMPP/WAMP) ou production (InfinityFree) : détection automatique.
// En local : base 'black_collection', utilisateur 'root' sans mot de passe (à adapter si besoin).
$enLocal = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1', '::1'], true);

// Paramètres de connexion MySQL (production : infinityfree)

define('DB_HOST', $enLocal ? '127.0.0.1' : 'sql105.infinityfree.com');
define('DB_PORT', '3306');
define('DB_NAME', $enLocal ? 'black_collection' : 'if0_43110371_BACKLCOLLECTION');
define('DB_USER', $enLocal ? 'root' : 'if0_43110371');
define('DB_PASS', $enLocal ? '' : '4hIh7qlVUF0QJ');
define('DB_CHARSET', 'utf8mb4');

// Sécurité pour la création du compte administrateur
// INSCRIPTION_ACTIVE : true pour autoriser la première inscription, false pour la couper
define('INSCRIPTION_ACTIVE', false);

// Code secret requis lors de l'inscription du premier compte administrateur
define('CODE_INVITATION', 'BLACK2026');

// Chemins et URL
define('SITE_URL', '/Black-Colletion');
define('UPLOAD_DIR', dirname(__DIR__) . '/uploads/produits/');
define('UPLOAD_URL', SITE_URL . '/uploads/produits/');
