<?php
// =====================================================================
//  BLACK COLLECTION - En-tête public
// =====================================================================
require_once __DIR__ . '/db.php';

$nomMarque   = get_param('nom_marque', 'Black Collection');
$slogan      = get_param('slogan', "L'élégance a son côté sombre.");
$whatsapp    = get_param('whatsapp', '2250554971592');
$minCommande = (int) get_param('livraison_minimum', '5');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($nomMarque) ?> &mdash; <?= e($slogan) ?></title>
  <meta name="description" content="<?= e($nomMarque) ?> : Haute parfumerie et pièces exclusives. Commandez directement à Abidjan via WhatsApp.">

  <!-- Polices Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Montserrat:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap">

  <!-- Feuilles de style -->
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

  <!-- Barre d'annonce supérieure -->
  <aside class="top-announcement" aria-label="Informations de commande">
    <div class="container announcement-content">
      <span><strong class="gold-text">&bull;</strong> Livraison rapide à <?= e(get_param('livraison_zone', 'Abidjan')) ?></span>
      <span>Minimum de commande : <strong><?= $minCommande ?> flacons</strong></span>
      <a href="https://wa.me/<?= e($whatsapp) ?>" target="_blank" rel="noopener" class="top-wa-link">
        Commander via WhatsApp &rarr;
      </a>
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
        <span class="logo-main"><?= e($nomMarque) ?></span>
        <span class="logo-sub">HAUTE PARFUMERIE</span>
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
