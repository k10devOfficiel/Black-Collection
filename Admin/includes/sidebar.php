<?php
// =====================================================================
//  BLACK COLLECTION - Squelette commun des pages admin
//  Utilisation dans chaque page :
//      exiger_admin();
//      admin_debut('Titre de la page', 'dashboard');   // clé du menu actif
//      ... contenu ...
//      admin_fin();
// =====================================================================
require_once __DIR__ . '/icons.php';

/** Date en français : "5 oct. 2026" */
function date_fr(int $ts): string
{
    $mois = ['janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];
    return date('j', $ts) . ' ' . $mois[(int) date('n', $ts) - 1] . ' ' . date('Y', $ts);
}

/** Date complète en français : "lundi 5 octobre 2026" */
function date_longue_fr(int $ts): string
{
    $jours = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
    $mois  = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
    return $jours[(int) date('w', $ts)] . ' ' . date('j', $ts) . ' ' . $mois[(int) date('n', $ts) - 1] . ' ' . date('Y', $ts);
}

function admin_debut(string $titre, string $actif = ''): void
{
    $menu = [
        'dashboard'  => ['index.php',        'Tableau de bord',  'dashboard'],
        'produits'   => ['produits.php',     'Produits',         'box'],
        'ajouter'    => ['produit-form.php', 'Ajouter un produit', 'plus'],
        'categories' => ['categories.php',   'Catégories',       'shapes'],
        'parametres' => ['parametres.php',   'Paramètres',       'settings'],
    ];
    $nom = $_SESSION['admin_nom'] ?? 'Admin';
    ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title><?= e($titre) ?> - Black Collection</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Montserrat:wght@500;600;700&family=Playfair+Display:wght@400;500;600&display=swap">
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="dash">

<div class="overlay" id="overlay"></div>

<aside class="sidebar" id="sidebar">
  <div>
    <div class="side-head">
      <a class="brand" href="index.php">
        <span class="brand-1">BLACK</span>
        <span class="brand-2">COLLECTION</span>
      </a>
      <button type="button" class="icon-btn only-mobile" id="menu-close" aria-label="Fermer le menu"><?= icon('close') ?></button>
    </div>

    <div class="side-nav">
      <span class="label-caps">Navigation</span>
      <nav>
        <?php foreach ($menu as $cle => [$lien, $texte, $ico]): ?>
          <a href="<?= e($lien) ?>" class="nav-link<?= $cle === $actif ? ' is-active' : '' ?>"<?= $cle === $actif ? ' aria-current="page"' : '' ?>>
            <?= icon($ico) ?><span><?= e($texte) ?></span>
          </a>
        <?php endforeach; ?>
      </nav>
    </div>
  </div>

  <div class="side-foot">
    <a href="../index.php" target="_blank" rel="noopener" class="nav-link">
      <?= icon('store') ?><span>Voir la boutique</span><?= icon('external', 16) ?>
    </a>
    <form method="post" action="logout.php">
      <?= csrf_champ() ?>
      <button type="submit" class="nav-link nav-btn"><?= icon('logout') ?><span>Déconnexion</span></button>
    </form>
  </div>
</aside>

<div class="content">
  <header class="topbar">
    <div class="topbar-left">
      <button type="button" class="icon-btn only-mobile" id="menu-open" aria-label="Ouvrir le menu"><?= icon('menu') ?></button>
      <span class="chip"><i class="dot"></i> Administrateur &bull; Abidjan</span>
    </div>
    <div class="topbar-right">
      <span class="topbar-date"><?= icon('calendar', 16) ?><span><?= e(date_longue_fr(time())) ?></span></span>
      <span class="avatar" title="<?= e($nom) ?>"><?= icon('user', 18) ?></span>
    </div>
  </header>

  <main class="page">
<?php
}

function admin_fin(): void
{
    ?>
  </main>
</div>

<script src="../assets/js/admin.js"></script>
</body>
</html>
<?php
}