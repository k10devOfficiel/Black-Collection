<?php
// =====================================================================
//  BLACK COLLECTION - Gestion des Produits
// =====================================================================
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/sidebar.php';
exiger_admin();

// Filtrage
$typeFiltre = $_GET['type'] ?? '';
$where = [];
$params = [];

if ($typeFiltre === 'parfum' || $typeFiltre === 'vetement') {
    $where[] = 'p.type = :type';
    $params[':type'] = $typeFiltre;
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "SELECT p.*, c.nom AS categorie_nom,
               (SELECT i.chemin FROM produit_images i 
                 WHERE i.produit_id = p.id 
                 ORDER BY i.principale DESC, i.ordre ASC, i.id ASC LIMIT 1) AS photo
          FROM produits p
     LEFT JOIN categories c ON p.categorie_id = c.id
       $whereSql
      ORDER BY p.cree_le DESC, p.id DESC";

$stmt = db()->prepare($sql);
$stmt->execute($params);
$produits = $stmt->fetchAll();

$message = $_GET['msg'] ?? '';

admin_debut('Produits', 'produits');
?>

<section class="page-head">
  <div>
    <span class="label-caps"><i class="dot"></i> Catalogue &bull; Gestion</span>
    <h1 class="titre">Tous les produits</h1>
    <p class="sous-titre">Consultez, modifiez ou ajoutez de nouvelles références.</p>
  </div>
  <div class="head-actions">
    <a href="produit-form.php" class="btn-primary"><?= icon('plus', 16) ?> Ajouter un produit</a>
  </div>
</section>

<?php if ($message === 'cree'): ?>
  <div class="msg-success"><?= icon('check', 16) ?> Le produit a été créé avec succès.</div>
<?php elseif ($message === 'modifie'): ?>
  <div class="msg-success"><?= icon('check', 16) ?> Le produit a été mis à jour.</div>
<?php elseif ($message === 'supprime'): ?>
  <div class="msg-success"><?= icon('check', 16) ?> Le produit a été supprimé du catalogue.</div>
<?php endif; ?>

<div class="panel">
  <div class="panel-head">
    <div>
      <h2 class="panel-titre"><?= count($produits) ?> Référence<?= count($produits) > 1 ? 's' : '' ?></h2>
      <p class="panel-sous">Affichage des parfums et vêtements enregistrés</p>
    </div>
    <div class="outils">
      <a href="produits.php" class="btn-ghost btn-sm<?= empty($typeFiltre) ? ' is-active' : '' ?>">Tous</a>
      <a href="produits.php?type=parfum" class="btn-ghost btn-sm<?= $typeFiltre === 'parfum' ? ' is-active' : '' ?>">Parfums</a>
      <a href="produits.php?type=vetement" class="btn-ghost btn-sm<?= $typeFiltre === 'vetement' ? ' is-active' : '' ?>">Vêtements</a>
    </div>
  </div>

  <?php if (!$produits): ?>
    <div class="vide">
      <p>Aucun produit trouvé dans cette sélection.</p>
      <a href="produit-form.php" class="btn-primary"><?= icon('plus', 16) ?> Ajouter un produit</a>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th style="width: 60px;">Photo</th>
            <th>Nom du produit</th>
            <th>Type</th>
            <th>Catégorie</th>
            <th>Prix</th>
            <th>Disponibilité</th>
            <th>Statut</th>
            <th style="text-align: right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($produits as $p): 
            $rupture = $p['disponibilite'] === 'rupture';
          ?>
            <tr>
              <td>
                <div class="thumb" style="width: 44px; height: 44px;">
                  <?php if (!empty($p['photo'])): ?>
                    <img src="../<?= e($p['photo']) ?>" alt="<?= e($p['nom']) ?>" class="preview-img">
                  <?php else: ?>
                    <?= icon($p['type'] === 'vetement' ? 'shirt' : 'flask', 18) ?>
                  <?php endif; ?>
                </div>
              </td>
              <td>
                <strong style="color: var(--white); font-size: 15px;"><?= e($p['nom']) ?></strong>
                <?php if ($p['contenance']): ?>
                  <span class="muted" style="font-size: 12px; margin-left: 6px;">(<?= e($p['contenance']) ?>)</span>
                <?php endif; ?>
              </td>
              <td>
                <span class="pill"><?= $p['type'] === 'vetement' ? 'Vêtement' : 'Parfum' ?></span>
              </td>
              <td>
                <span class="muted"><?= e($p['categorie_nom'] ?? 'Sans catégorie') ?></span>
              </td>
              <td>
                <strong style="color: var(--white);"><?= prix((int) $p['prix']) ?></strong>
              </td>
              <td>
                <span class="badge <?= $rupture ? 'badge-off' : 'badge-on' ?>">
                  <i class="dot<?= $rupture ? ' dot-off' : '' ?>"></i>
                  <?= $rupture ? 'Rupture' : 'En stock' ?>
                </span>
              </td>
              <td>
                <?php if ($p['visible']): ?>
                  <span class="muted" style="font-size: 12px;">En ligne</span>
                <?php else: ?>
                  <span class="badge badge-off">Masqué</span>
                <?php endif; ?>
              </td>
              <td>
                <div class="actions-cell">
                  <a href="produit-form.php?id=<?= (int) $p['id'] ?>" class="btn-ghost btn-sm" title="Modifier">
                    <?= icon('edit', 14) ?> Modifier
                  </a>
                  <form method="post" action="produit-delete.php" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?');" style="margin: 0;">
                    <?= csrf_champ() ?>
                    <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                    <button type="submit" class="icon-btn bordered" title="Supprimer" style="width: 32px; height: 32px;">
                      <?= icon('trash', 14) ?>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php admin_fin(); ?>
