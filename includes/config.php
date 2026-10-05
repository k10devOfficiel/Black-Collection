<?php
// =====================================================================
//  BLACK COLLECTION - Configuration générale
// =====================================================================

// Fuseau horaire (Abidjan / GMT)
date_default_timezone_set('Africa/Abidjan');

// Paramètres de connexion MySQL (WAMP)
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'black_collection');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Sécurité pour la création du compte administrateur
// INSCRIPTION_ACTIVE : true pour autoriser la première inscription, false pour la couper
define('INSCRIPTION_ACTIVE', true);

// Code secret requis lors de l'inscription du premier compte administrateur
define('CODE_INVITATION', 'BLACK2026');

// Chemins et URL
define('SITE_URL', '/Black-Colletion');
define('UPLOAD_DIR', dirname(__DIR__) . '/uploads/produits/');
define('UPLOAD_URL', SITE_URL . '/uploads/produits/');
