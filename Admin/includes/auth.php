<?php
// =====================================================================
//  BLACK COLLECTION - Authentification administrateur
//  Session sécurisée, jeton CSRF, limitation des essais de connexion
// =====================================================================
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/csrf.php';

const SESSION_INACTIVITE = 1800;   // déconnexion après 30 min sans action
const MAX_TENTATIVES     = 5;      // essais échoués autorisés...
const FENETRE_MINUTES    = 15;     // ...pendant cette durée

/* ---------- Session ---------- */
function demarrer_session(): void
{
    if (session_status() !== PHP_SESSION_NONE) {
        return;
    }
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_name('bc_admin');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $https,     // true automatiquement une fois le HTTPS activé
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function admin_connecte(): bool
{
    demarrer_session();
    if (empty($_SESSION['admin_id'])) {
        return false;
    }
    if (time() - ($_SESSION['derniere_activite'] ?? 0) > SESSION_INACTIVITE) {
        deconnecter();
        return false;
    }
    $_SESSION['derniere_activite'] = time();
    return true;
}

/** À mettre au début de chaque page protégée de l'admin. */
function exiger_admin(): void
{
    if (!admin_connecte()) {
        header('Location: login.php');
        exit;
    }
}

function connecter(array $admin): void
{
    demarrer_session();
    session_regenerate_id(true);               // empêche le vol de session
    $_SESSION['admin_id']          = (int) $admin['id'];
    $_SESSION['admin_nom']         = $admin['nom'];
    $_SESSION['derniere_activite'] = time();
}

function deconnecter(): void
{
    demarrer_session();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/* ---------- Limitation des tentatives ---------- */
function ip_client(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function trop_de_tentatives(string $email): bool
{
    $sql = 'SELECT COUNT(*) FROM tentatives_connexion
            WHERE reussie = 0
              AND cree_le > (NOW() - INTERVAL ' . FENETRE_MINUTES . ' MINUTE)
              AND (ip = :ip OR email = :email)';
    $st = db()->prepare($sql);
    $st->execute([':ip' => ip_client(), ':email' => $email]);
    return (int) $st->fetchColumn() >= MAX_TENTATIVES;
}

function noter_tentative(string $email, bool $reussie): void
{
    $st = db()->prepare('INSERT INTO tentatives_connexion (email, ip, reussie) VALUES (:e, :ip, :r)');
    $st->execute([':e' => $email, ':ip' => ip_client(), ':r' => $reussie ? 1 : 0]);
}

function purger_tentatives(string $email): void
{
    $st = db()->prepare('DELETE FROM tentatives_connexion WHERE email = :e OR ip = :ip');
    $st->execute([':e' => $email, ':ip' => ip_client()]);
}