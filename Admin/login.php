<?php
// =====================================================================
//  CONNEXION ADMINISTRATEUR
// =====================================================================
require_once __DIR__ . '/includes/auth.php';
demarrer_session();

// Déjà connecté -> direction le tableau de bord
if (admin_connecte()) {
    header('Location: index.php');
    exit;
}

$erreur = '';
$email  = '';
$info   = isset($_GET['cree']) ? 'Compte créé. Tu peux te connecter.' : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $mdp   = $_POST['mot_de_passe'] ?? '';

    if (!csrf_valide()) {
        $erreur = 'Session expirée. Recharge la page et réessaie.';
    } elseif (trop_de_tentatives($email)) {
        $erreur = 'Trop d\'essais. Réessaie dans ' . FENETRE_MINUTES . ' minutes.';
    } else {
        $st = db()->prepare('SELECT id, nom, mot_de_passe FROM admins WHERE email = :e LIMIT 1');
        $st->execute([':e' => $email]);
        $admin = $st->fetch();

        // Si l'email n'existe pas, on fait quand même un calcul de hash :
        // le temps de réponse reste similaire, on ne révèle rien.
        if ($admin) {
            $ok = password_verify($mdp, $admin['mot_de_passe']);
        } else {
            password_hash($mdp, PASSWORD_DEFAULT);
            $ok = false;
        }

        if ($admin && $ok) {
            // Remet le hash à jour si l'algorithme par défaut a évolué
            if (password_needs_rehash($admin['mot_de_passe'], PASSWORD_DEFAULT)) {
                $up = db()->prepare('UPDATE admins SET mot_de_passe = :m WHERE id = :id');
                $up->execute([':m' => password_hash($mdp, PASSWORD_DEFAULT), ':id' => $admin['id']]);
            }
            purger_tentatives($email);
            connecter($admin);
            db()->prepare('UPDATE admins SET derniere_connexion = NOW() WHERE id = ?')->execute([$admin['id']]);
            header('Location: index.php');
            exit;
        }

        noter_tentative($email, false);
        $erreur = 'Identifiants incorrects.';   // message volontairement générique
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>Connexion - Black Collection</title>
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="auth-page">
  <main class="auth-card">
    <div class="logo">BLACK COLLECTION</div>
    <h1>Espace administrateur</h1>

    <?php if ($info): ?><div class="info"><p><?= e($info) ?></p></div><?php endif; ?>
    <?php if ($erreur): ?><div class="alert" role="alert"><p><?= e($erreur) ?></p></div><?php endif; ?>

    <form method="post" novalidate>
      <?= csrf_champ() ?>

      <label for="email">Email</label>
      <input type="email" id="email" name="email" value="<?= e($email) ?>" autocomplete="username" required autofocus>

      <label for="mot_de_passe">Mot de passe</label>
      <div class="champ-mdp">
        <input type="password" id="mot_de_passe" name="mot_de_passe" autocomplete="current-password" required>
        <button type="button" class="voir" id="voir" aria-label="Afficher ou masquer le mot de passe">Voir</button>
      </div>

      <button type="submit" class="btn">SE CONNECTER</button>
    </form>
  </main>

  <script>
    // Afficher / masquer le mot de passe
    document.getElementById('voir').addEventListener('click', function () {
      var champ = document.getElementById('mot_de_passe');
      var visible = champ.type === 'text';
      champ.type = visible ? 'password' : 'text';
      this.textContent = visible ? 'Voir' : 'Cacher';
    });
  </script>
</body>
</html>