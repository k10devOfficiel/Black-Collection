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
            http_response_code(500);
            error_log('Erreur BDD : ' . $e->getMessage());
            die('Le site est momentanément indisponible. Merci de réessayer dans quelques minutes.');
        }
    }

    return $pdo;
}
