<?php
require_once __DIR__ . '/includes/auth.php';
demarrer_session();

// Déconnexion uniquement par formulaire (POST) avec jeton CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_valide()) {
    deconnecter();
}
header('Location: login.php');
exit;