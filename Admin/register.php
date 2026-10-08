<?php
// =====================================================================
//  INSCRIPTION SECRETE - à utiliser UNE SEULE FOIS
//  Protégée par : code d'invitation + fermeture automatique dès qu'un
//  administrateur existe + désactivation possible dans config.php
// =====================================================================
require_once __DIR__ . '/includes/auth.php';
demarrer_session();

// 1. Inscription désactivée dans la configuration -> la page "n'existe pas"
if (!INSCRIPTION_ACTIVE) {
    http_response_code(404);
    exit('Page introuvable.');
}

// 2. Un administrateur existe déjà -> inscription fermée automatiquement
$nbAdmins = (int) db()->query('SELECT COUNT(*) FROM admins')->fetchColumn();
if ($nbAdmins > 0) {
    http_response_code(404);
    exit('Page introuvable.');
}

$erreurs = [];
$nom = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom      = trim($_POST['nom'] ?? '');
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $mdp      = $_POST['mot_de_passe'] ?? '';
    $mdp2     = $_POST['mot_de_passe2'] ?? '';
    $code     = $_POST['code'] ?? '';
    $cleLimite = 'inscription';       // sert à limiter les essais du code secret

    if (!csrf_valide()) {
        $erreurs[] = 'Session expirée. Recharge la page et réessaie.';
    } elseif (trop_de_tentatives($cleLimite)) {
        $erreurs[] = 'Trop d\'essais. Réessaie dans quelques minutes.';
    } else {
        if (!hash_equals(CODE_INVITATION, $code)) {
            noter_tentative($cleLimite, false);
            $erreurs[] = 'Code d\'invitation incorrect.';
        }
        if ($nom === '' || mb_strlen($nom) > 100) {
            $erreurs[] = 'Indique ton nom (100 caractères maximum).';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
            $erreurs[] = 'Adresse email invalide.';
        }
        if (mb_strlen($mdp) < 10) {
            $erreurs[] = 'Le mot de passe doit contenir au moins 10 caractères.';
        }
        if ($mdp !== $mdp2) {
            $erreurs[] = 'Les deux mots de passe ne sont pas identiques.';
        }
    }

    if (!$erreurs) {
        $hash = password_hash($mdp, PASSWORD_DEFAULT);
        $st = db()->prepare('INSERT INTO admins (nom, email, mot_de_passe) VALUES (:n, :e, :m)');
        $st->execute([':n' => $nom, ':e' => $email, ':m' => $hash]);
        purger_tentatives($cleLimite);
        header('Location: login.php?cree=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>Création du compte administrateur - Black Collection</title>
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="auth-page">
  <main class="auth-card">
    <?php $logo = get_param('logo'); ?>
    <?php if ($logo !== ''): ?>
      <img class="auth-logo-img" src="../<?= e($logo) ?>" alt="Black Collection">
    <?php else: ?>
      <div class="logo">BLACK COLLECTION</div>
    <?php endif; ?>
    <h1>Créer le compte administrateur</h1>

    <?php if ($erreurs): ?>
      <div class="alert" role="alert">
        <?php foreach ($erreurs as $err): ?><p><?= e($err) ?></p><?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="post" autocomplete="off" novalidate>
      <?= csrf_champ() ?>

      <label for="nom">Nom</label>
      <input type="text" id="nom" name="nom" value="<?= e($nom) ?>" required>

      <label for="email">Email</label>
      <input type="email" id="email" name="email" value="<?= e($email) ?>" required>

      <label for="mot_de_passe">Mot de passe (10 caractères minimum)</label>
      <input type="password" id="mot_de_passe" name="mot_de_passe" required>

      <label for="mot_de_passe2">Confirmer le mot de passe</label>
      <input type="password" id="mot_de_passe2" name="mot_de_passe2" required>

      <label for="code">Code d'invitation</label>
      <input type="password" id="code" name="code" required>

      <button type="submit" class="btn">CRÉER LE COMPTE ADMINISTRATEUR</button>
    </form>
  </main>
</body>
</html>