<?php
// =====================================================================
//  BLACK COLLECTION - Vitrine Principale / Boutique Publique
// =====================================================================
require_once __DIR__ . '/includes/header.php';

// Récupération des catégories
$categories = db()->query('SELECT * FROM categories ORDER BY type ASC, ordre ASC, nom ASC')->fetchAll();

// Récupération des produits visibles
$sql = "SELECT p.*, c.nom AS categorie_nom, c.slug AS categorie_slug,
               (SELECT i.chemin FROM produit_images i 
                 WHERE i.produit_id = p.id 
                 ORDER BY i.principale DESC, i.ordre ASC, i.id ASC LIMIT 1) AS photo
          FROM produits p
     LEFT JOIN categories c ON p.categorie_id = c.id
         WHERE p.visible = 1
      ORDER BY p.mis_en_avant DESC, p.cree_le DESC";
$produits = db()->query($sql)->fetchAll();

// Séparation des produits mis en avant et collections
$misEnAvant = array_filter($produits, fn($p) => (int)$p['mis_en_avant'] === 1);
$parfums    = array_filter($produits, fn($p) => $p['type'] === 'parfum');
$vetements  = array_filter($produits, fn($p) => $p['type'] === 'vetement');
?>

<!-- Section Hero / Bannière Haute Couture -->
<section class="hero-section">
  <div class="hero-overlay"></div>
  <div class="container hero-content">
    <span class="hero-badge"><i class="gold-dot">&bull;</i> ATELIER ABIDJAN</span>
    <h1 class="hero-title">L'Élégance a son côté sombre.</h1>
    <p class="hero-subtitle">Des sillages intenses, des accords rares et une allure d'exception conçus pour marquer les esprits.</p>
    <div class="hero-actions">
      <a href="#parfums" class="btn-gold">Explorer la collection</a>
      <a href="https://wa.me/<?= e($whatsapp) ?>?text=Bonjour%2C%20je%20souhaite%20d%C3%A9couvrir%20la%20collection%20Black%20Collection." target="_blank" rel="noopener" class="btn-outline-gold">Conseiller WhatsApp</a>
    </div>
  </div>
</section>

<!-- Barre de réassurance -->
<section class="trust-bar">
  <div class="container trust-grid">
    <div class="trust-item">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
      </svg>
      <div>
        <strong>Essences de Haute Tenue</strong>
        <span>Concentration premium longue durée</span>
      </div>
    </div>

    <div class="trust-item">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
        <rect x="1" y="3" width="15" height="13"/>
        <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
        <circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>
      </svg>
      <div>
        <strong>Livraison <?= e(get_param('livraison_zone', 'Abidjan')) ?></strong>
        <span>Expédition rapide & soignée</span>
      </div>
    </div>

    <div class="trust-item">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
        <rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/>
      </svg>
      <div>
        <strong>Commande WhatsApp & Wave</strong>
        <span>Simplicité, flexibilité et écoute</span>
      </div>
    </div>
  </div>
</section>

<!-- Section Coups de cœur (Mis en avant) -->
<?php if (!empty($misEnAvant)): ?>
<section class="featured-section container">
  <div class="section-head text-center">
    <span class="section-tag">SÉLECTION SIGNATURE</span>
    <h2 class="section-title">Les Essences Privilèges</h2>
    <div class="gold-line"></div>
  </div>

  <div class="product-grid">
    <?php foreach (array_slice($misEnAvant, 0, 3) as $p): ?>
      <?php include __DIR__ . '/includes/product-card.php'; ?>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- Section Catalogue Global / Filtres -->
<section class="catalog-section container" id="parfums">
  <div class="section-head">
    <div>
      <span class="section-tag">CATALOGUE OFFICIEL</span>
      <h2 class="section-title">Nos Collections</h2>
    </div>

    <!-- Filtres par catégorie -->
    <div class="filter-tabs" id="catalogFilters">
      <button type="button" class="tab-btn active" data-filter="all">Toutes les pièces</button>
      <button type="button" class="tab-btn" data-filter="parfum">Parfums</button>
      <button type="button" class="tab-btn" data-filter="vetement">Vêtements</button>
      <?php foreach ($categories as $cat): ?>
        <button type="button" class="tab-btn" data-filter="cat-<?= (int)$cat['id'] ?>"><?= e($cat['nom']) ?></button>
      <?php endforeach; ?>
    </div>
  </div>

  <?php if (empty($produits)): ?>
    <div class="catalog-empty">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
        <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
      </svg>
      <h3>La collection est en cours de préparation</h3>
      <p>Nos artisans préparent de nouvelles essences rares. Revenez très bientôt ou contactez-nous directement sur WhatsApp.</p>
      <a href="https://wa.me/<?= e($whatsapp) ?>" target="_blank" rel="noopener" class="btn-gold">Nous contacter sur WhatsApp</a>
    </div>
  <?php else: ?>
    <div class="product-grid" id="mainProductGrid">
      <?php foreach ($produits as $p): ?>
        <?php include __DIR__ . '/includes/product-card.php'; ?>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<!-- Section Univers / Philosophie -->
<section class="universe-section" id="univers">
  <div class="container universe-grid">
    <div class="universe-text">
      <span class="section-tag">L'ATELIER</span>
      <h2 class="universe-title">L'Art de l'Ombre et de la Matière</h2>
      <p>Née du désir d’incarner une élégance ténébreuse et affirmée, <strong><?= e($nomMarque) ?></strong> sélectionne méticuleusement des matières nobles. Des bois précieux fumés aux épices suaves, chaque création est une promesse d’intensité.</p>
      <p>Que ce soit à travers un flacon d'essence rare ou une coupe vestimentaire épurée, notre démarche reste la même : révéler votre singularité avec mystère et distinction.</p>
      <div class="universe-quote">
        &laquo; Porter Black Collection, c'est choisir de ne pas passer inaperçu sans jamais avoir à hausser la voix. &raquo;
      </div>
    </div>
    <div class="universe-visual">
      <div class="visual-card">
        <div class="visual-brand">BLACK COLLECTION</div>
        <span class="visual-sub">ABIDJAN &bull; SÉLECTION PRIVÉE</span>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
