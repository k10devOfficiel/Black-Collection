<?php
// =====================================================================
//  BLACK COLLECTION - Connexion Base de Données (PDO Singleton)
// =====================================================================
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

/**
 * Retourne l'instance unique de connexion PDO à MySQL.
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_PORT,
            DB_NAME,
            DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // En cas d'erreur de connexion
            http_response_code(500);
            die('Erreur de connexion à la base de données : ' . e($e->getMessage()));
        }
    }

    return $pdo;
}
