<?php
// =====================================================================
//  BLACK COLLECTION - En-tête public
// =====================================================================
require_once __DIR__ . '/db.php';

$nomMarque   = get_param('nom_marque', 'Black Collection');
$slogan      = get_param('slogan', "L'élégance a son côté sombre.");
$whatsapp    = get_param('whatsapp', '2250554971592');
$minCommande = (int) get_param('livraison_minimum', '5');
$logo        = get_param('logo');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($nomMarque) ?> &mdash; <?= e($slogan) ?></title>
  <meta name="description" content="<?= e($nomMarque) ?> : Haute parfumerie et pièces exclusives. Commandez directement à Abidjan via WhatsApp.">
    <?php
  $schema  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
  $baseUrl = $schema . '://' . ($_SERVER['HTTP_HOST'] ?? '');
  ?>
  <link rel="icon" type="image/svg+xml" href="assets/img/favicon.svg">
  <link rel="icon" type="image/png" sizes="32x32" href="assets/img/favicon-32.png">
  <link rel="apple-touch-icon" href="assets/img/apple-touch-icon.png">
  <meta name="theme-color" content="#000000">
  <meta property="og:type" content="website">
  <meta property="og:title" content="<?= e($nomMarque) ?> &mdash; <?= e($slogan) ?>">
  <meta property="og:description" content="Haute parfumerie à Abidjan. Commandez via WhatsApp.">
  <meta property="og:image" content="<?= e($baseUrl) ?>/assets/img/og-image.jpg">
  <!-- Polices Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Jost:wght@300;400;500&family=Marcellus&display=swap">

  <!-- Feuilles de style -->
  <link rel="stylesheet" href="assets/css/style.css?v=<?= (int) @filemtime(__DIR__ . '/../assets/css/style.css') ?>">
  <link rel="stylesheet" href="assets/css/checkout.css?v=<?= (int) @filemtime(__DIR__ . '/../assets/css/checkout.css') ?>">
</head>
<body>

  <!-- Barre d'annonce supérieure -->
  <aside class="top-announcement" aria-label="Informations de commande">
    <div class="container announcement-content">
      <span>Livraison à <?= e(get_param('livraison_zone', 'Abidjan')) ?></span>
      <span>Minimum de commande : <strong><?= $minCommande ?></strong></span>
    
    </div>
  </aside>

  <!-- En-tête / Barre de navigation -->
  <header class="site-header" id="siteHeader">
    <div class="container header-container">
      <!-- Menu Mobile Trigger -->
      <button type="button" class="nav-toggle" id="navToggle" aria-label="Ouvrir le menu">
        <span class="bar"></span>
        <span class="bar"></span>
      </button>

      <!-- Logo de la marque -->
      <a href="index.php" class="site-logo">
        <?php if ($logo !== ''): ?>
          <img class="logo-img" src="<?= e($logo) ?>" alt="<?= e($nomMarque) ?>">
        <?php else: ?>
          <span class="logo-main"><?= e($nomMarque) ?></span>
          <span class="logo-sub">HAUTE PARFUMERIE</span>
        <?php endif; ?>
      </a>

      <!-- Navigation principale -->
      <nav class="site-nav" id="siteNav">
        <ul class="nav-list">
          <li><a href="index.php" class="nav-item active">Accueil</a></li>
          <li><a href="index.php#parfums" class="nav-item">Parfums</a></li>
          <li><a href="index.php#vetements" class="nav-item">Vêtements</a></li>
          <li><a href="index.php#univers" class="nav-item">L'Univers</a></li>
          <li><a href="index.php#contact" class="nav-item">Contact</a></li>
        </ul>
      </nav>

      <!-- Bouton Panier / Commande -->
      <div class="header-actions">
        <button type="button" class="cart-trigger" id="cartTrigger" aria-label="Ouvrir mon panier">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><line x1="3" x2="21" y1="6" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/>
          </svg>
          <span class="cart-badge" id="cartCount">0</span>
        </button>
      </div>
    </div>
  </header>