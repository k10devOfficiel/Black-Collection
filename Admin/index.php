<?php
// =====================================================================
//  TABLEAU DE BORD ADMINISTRATEUR
// =====================================================================
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/sidebar.php';
exiger_admin();

/* ---------- Chiffres clés ---------- */
$stats = db()->query(
    "SELECT COUNT(*)                        AS total,
            SUM(visible = 1)                AS actifs,
            SUM(type = 'parfum')            AS parfums,
            SUM(type = 'vetement')          AS vetements,
            SUM(disponibilite = 'rupture')  AS ruptures
       FROM produits"
)->fetch();
$total     = (int) $stats['total'];
$actifs    = (int) $stats['actifs'];
$parfums   = (int) $stats['parfums'];
$vetements = (int) $stats['vetements'];
$ruptures  = (int) $stats['ruptures'];

/* ---------- Commandes & clients ---------- */
$cmd = ['total' => 0, 'nouvelles' => 0, 'ca' => 0];
$nbClients = 0;
$dernieresCommandes = [];
$libStatut = ['nouvelle' => 'Nouvelle', 'confirmee' => 'Confirmée', 'livree' => 'Livrée', 'annulee' => 'Annulée'];
try {
    $c = db()->query(
        "SELECT COUNT(*) AS total,
                COALESCE(SUM(statut = 'nouvelle'), 0) AS nouvelles,
                COALESCE(SUM(CASE WHEN statut <> 'annulee' THEN total END), 0) AS ca
           FROM commandes"
    )->fetch();
    $cmd = ['total' => (int) $c['total'], 'nouvelles' => (int) $c['nouvelles'], 'ca' => (int) $c['ca']];
    $nbClients = (int) db()->query('SELECT COUNT(*) FROM clients')->fetchColumn();
    $dernieresCommandes = db()->query(
        'SELECT id, reference, nom_client, total, statut, cree_le FROM commandes ORDER BY cree_le DESC, id DESC LIMIT 5'
    )->fetchAll();
} catch (Throwable $e) {
    // tables clients/commandes pas encore créées : on affiche des zéros
}

$nomsRupture = db()->query(
    "SELECT nom FROM produits WHERE disponibilite = 'rupture' ORDER BY modifie_le DESC LIMIT 2"
)->fetchAll(PDO::FETCH_COLUMN);

/* ---------- 5 derniers produits ajoutés (avec leur photo principale) ---------- */
$recents = db()->query(
    "SELECT p.id, p.nom, p.type, p.contenance, p.prix, p.disponibilite, p.visible, p.cree_le,
            (SELECT i.chemin FROM produit_images i
              WHERE i.produit_id = p.id
              ORDER BY i.principale DESC, i.ordre ASC, i.id ASC LIMIT 1) AS photo
       FROM produits p
      ORDER BY p.cree_le DESC, p.id DESC
      LIMIT 5"
)->fetchAll();

$whatsapp = get_param('whatsapp');
$minimum  = get_param('livraison_minimum', '5');
$pluriel  = fn (int $n): string => $n > 1 ? 's' : '';

admin_debut('Tableau de bord', 'dashboard');
?>

<?php /* ===== En-tête de page ===== */ ?>
<section class="page-head">
  <div>
    <span class="label-caps"><i class="dot"></i> Atelier Abidjan &bull; Contrôle global</span>
    <h1 class="titre">Bonjour, <?= e($_SESSION['admin_nom']) ?></h1>
    <p class="sous-titre">Gestion de la boutique Black Collection</p>
  </div>
  <div class="head-actions">
    <a href="index.php" class="btn-ghost"><?= icon('refresh', 16) ?> Actualiser</a>
    <a href="produit-form.php" class="btn-primary"><?= icon('plus-line', 16) ?> Ajouter un parfum</a>
  </div>
</section>

<?php /* ===== Cartes chiffres ===== */ ?>
<section class="stats">
  <article class="stat">
    <div class="stat-top"><span class="label-caps">Total des produits</span><?= icon('grid', 20) ?></div>
    <p class="stat-val"><?= $total ?> <small>référence<?= $pluriel($total) ?></small></p>
    <p class="stat-note"><i class="dot"></i> <?= $actifs ?> actif<?= $pluriel($actifs) ?> en ligne</p>
  </article>

  <article class="stat">
    <div class="stat-top"><span class="label-caps">Parfums</span><?= icon('flask', 20) ?></div>
    <p class="stat-val"><?= $parfums ?> <small>parfum<?= $pluriel($parfums) ?></small></p>
    <p class="stat-note">Catalogue des parfums et essences</p>
  </article>

  <article class="stat">
    <div class="stat-top"><span class="label-caps">Vêtements</span><?= icon('shirt', 20) ?></div>
    <p class="stat-val"><?= $vetements ?> <small>pièce<?= $pluriel($vetements) ?></small></p>
    <p class="stat-note"><?= $vetements === 0 ? 'Bientôt disponible &bull; En préparation' : 'Collection en ligne' ?></p>
  </article>

  <article class="stat">
    <div class="stat-top"><span class="label-caps">En rupture de stock</span><?= $ruptures > 0 ? '<i class="dot dot-off"></i>' : '' ?></div>
    <p class="stat-val"><?= $ruptures ?> <small>épuisé<?= $pluriel($ruptures) ?></small></p>
    <p class="stat-note">
      <?php if ($nomsRupture): ?>
        <?php foreach ($nomsRupture as $n): ?><span class="pill"><?= e($n) ?></span> <?php endforeach; ?>
      <?php else: ?>
        Tout est disponible
      <?php endif; ?>
    </p>
  </article>
</section>

<section class="stats">
  <article class="stat">
    <div class="stat-top"><span class="label-caps">Commandes à traiter</span><?= icon('bag', 20) ?></div>
    <p class="stat-val"><?= $cmd['nouvelles'] ?> <small>nouvelle<?= $pluriel($cmd['nouvelles']) ?></small></p>
    <p class="stat-note"><a href="commandes.php?statut=nouvelle" class="lien-fleche">Voir les commandes <?= icon('chevron', 14) ?></a></p>
  </article>

  <article class="stat">
    <div class="stat-top"><span class="label-caps">Clients</span><?= icon('user', 20) ?></div>
    <p class="stat-val"><?= $nbClients ?> <small>client<?= $pluriel($nbClients) ?></small></p>
    <p class="stat-note"><a href="clients.php" class="lien-fleche">Voir les clients <?= icon('chevron', 14) ?></a></p>
  </article>

  <article class="stat">
    <div class="stat-top"><span class="label-caps">Chiffre d'affaires</span><?= icon('box', 20) ?></div>
    <p class="stat-val"><?= e(prix($cmd['ca'])) ?></p>
    <p class="stat-note"><?= $cmd['total'] ?> commande<?= $pluriel($cmd['total']) ?> (hors annulées)</p>
  </article>
</section>

<section class="grille-2">

  <?php /* ===== Derniers produits ===== */ ?>
  <div class="panel">
    <div class="panel-head">
      <div>
        <h2 class="panel-titre">Derniers produits ajoutés</h2>
        <p class="panel-sous">Catalogue des parfums et vêtements</p>
      </div>
      <div class="outils">
        <label class="recherche">
          <?= icon('search', 16) ?>
          <input type="search" id="filtre" placeholder="Filtrer..." autocomplete="off">
        </label>
        <button type="button" class="icon-btn bordered" id="filtre-rupture" title="Afficher seulement les ruptures" aria-pressed="false"><?= icon('filter', 18) ?></button>
      </div>
    </div>

    <?php if (!$recents): ?>
      <div class="vide">
        <p>Aucun produit pour l'instant.</p>
        <a href="produit-form.php" class="btn-primary"><?= icon('plus-line', 16) ?> Ajouter mon premier parfum</a>
      </div>
    <?php else: ?>
      <ul class="liste-prod" id="liste-prod">
        <?php foreach ($recents as $p):
            $rupture = $p['disponibilite'] === 'rupture'; ?>
          <li class="row-prod<?= $rupture ? ' is-rupture' : '' ?>"
              data-nom="<?= e(mb_strtolower($p['nom'])) ?>" data-rupture="<?= $rupture ? '1' : '0' ?>">
            <div class="thumb">
              <?php if ($p['photo']): ?>
                <img src="../<?= e($p['photo']) ?>" alt="<?= e($p['nom']) ?>" loading="lazy">
              <?php else: ?>
                <?= icon($p['type'] === 'vetement' ? 'shirt' : 'flask', 22) ?>
              <?php endif; ?>
            </div>

            <div class="row-info">
              <div class="row-titre">
                <span class="row-nom"><?= e($p['nom']) ?></span>
                <span class="badge <?= $rupture ? 'badge-off' : 'badge-on' ?>"><i class="dot<?= $rupture ? ' dot-off' : '' ?>"></i><?= $rupture ? 'Rupture' : 'En stock' ?></span>
                <?php if (!$p['visible']): ?><span class="badge badge-off">Masqué</span><?php endif; ?>
              </div>
              <p class="row-meta">
                <?= e($p['type'] === 'vetement' ? 'Vêtement' : 'Flacon' . ($p['contenance'] ? ' ' . $p['contenance'] : '')) ?>
                <span class="sep">&bull;</span> <?= e(prix((int) $p['prix'])) ?>
                <span class="sep">&bull;</span> Ajouté le <?= e(date_fr(strtotime($p['cree_le']))) ?>
              </p>
            </div>

            <a href="produit-form.php?id=<?= (int) $p['id'] ?>" class="btn-ghost btn-sm">Modifier</a>
          </li>
        <?php endforeach; ?>
      </ul>
      <p class="aucun" id="aucun-resultat" hidden>Aucun produit ne correspond.</p>

      <div class="panel-foot">
        <span class="label-caps">Affichage de <?= count($recents) ?> sur <?= $total ?> article<?= $pluriel($total) ?></span>
        <a href="produits.php" class="lien-fleche">Voir tout le catalogue <?= icon('chevron', 14) ?></a>
      </div>
    <?php endif; ?>
  </div>

  <?php /* ===== Colonne de droite ===== */ ?>
  <div class="colonne">

    <div class="panel">
      <div class="panel-head">
        <h2 class="panel-titre">Dernières commandes</h2>
        <a href="commandes.php" class="lien-fleche">Tout voir <?= icon('chevron', 14) ?></a>
      </div>
      <?php if (!$dernieresCommandes): ?>
        <p class="texte-doux">Aucune commande pour l'instant.</p>
      <?php else: ?>
        <ul class="liste-prod">
          <?php foreach ($dernieresCommandes as $o): ?>
            <li class="row-prod">
              <div class="row-info">
                <div class="row-titre">
                  <span class="row-nom"><?= e($o['nom_client']) ?></span>
                  <span class="pill"><?= e($libStatut[$o['statut']] ?? $o['statut']) ?></span>
                </div>
                <p class="row-meta"><?= e($o['reference']) ?> <span class="sep">&bull;</span> <?= e(prix((int) $o['total'])) ?> <span class="sep">&bull;</span> <?= e(date_fr(strtotime($o['cree_le']))) ?></p>
              </div>
              <a href="commandes.php?id=<?= (int) $o['id'] ?>" class="btn-ghost btn-sm">Voir</a>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>

    <div class="panel">
      <div class="panel-head">
        <h2 class="panel-titre">Raccourcis rapides</h2>
        <?= icon('bolt', 18) ?>
      </div>
      <nav class="raccourcis">
        <a href="produit-form.php" class="raccourci">
          <?= icon('plus', 20) ?>
          <span><strong>Nouveau parfum</strong></span>
          <?= icon('chevron', 14) ?>
        </a>
        <a href="parametres.php" class="raccourci">
          <?= icon('message', 20) ?>
          <span><strong>Modifier le contact WhatsApp</strong><em><?= e($whatsapp !== '' ? '+' . $whatsapp : 'Non renseigné') ?></em></span>
          <?= icon('chevron', 14) ?>
        </a>
        <a href="parametres.php" class="raccourci">
          <?= icon('bag', 20) ?>
          <span><strong>Ajuster le minimum de commande</strong><em>Actuel : <?= e($minimum) ?> parfums requis</em></span>
          <?= icon('chevron', 14) ?>
        </a>
        <a href="categories.php" class="raccourci">
          <?= icon('shapes', 20) ?>
          <span><strong>Gérer les catégories</strong><em>Homme / Femme</em></span>
          <?= icon('chevron', 14) ?>
        </a>
      </nav>
    </div>

    <div class="panel">
      <div class="panel-head">
        <span class="label-caps"><i class="dot"></i> Boutique publique</span>
        <span class="label-caps muted">En direct</span>
      </div>
      <p class="texte-doux">Toutes vos modifications sont publiées immédiatement sur le site public.</p>
      <a href="../index.php" target="_blank" rel="noopener" class="btn-ghost btn-full">Accéder au site public <?= icon('external', 16) ?></a>
    </div>

  </div>
</section>

<?php admin_fin(); ?>