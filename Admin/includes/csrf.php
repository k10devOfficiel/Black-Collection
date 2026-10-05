<?php
// =====================================================================
//  BLACK COLLECTION - Protection CSRF (jeton caché dans chaque formulaire)
//  Chargé automatiquement par auth.php
// =====================================================================

/* ---------- Protection CSRF ---------- */
function csrf_token(): string
{
    demarrer_session();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_champ(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_valide(): bool
{
    demarrer_session();
    return isset($_POST['csrf'], $_SESSION['csrf'])
        && hash_equals($_SESSION['csrf'], (string) $_POST['csrf']);
}