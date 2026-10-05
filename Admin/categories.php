<?php
// =====================================================================
//  BLACK COLLECTION - Gestion des Catégories
// =====================================================================
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/sidebar.php';
exiger_admin();

$erreurs = [];
$succes = '';

// Action de suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'supprimer') {
    if (!csrf_valide()) {
        $erreurs[] = 'Jeton de sécurité invalide.';
    } else {
        $catId = (int) ($_POST['id'] ?? 0);
        if ($catId > 0) {
            $st = db()->prepare('DELETE FROM categories WHERE id = ?');
            $st->execute([$catId]);
            $succes = 'Catégorie supprimée avec succès.';
        }
    }
}

// Action d'ajout / modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'enregistrer') {
    if (!csrf_valide()) {
        $erreurs[] = 'Jeton de sécurité invalide.';
    } else {
        $id    = (int) ($_POST['id'] ?? 0);
        $nom   = trim($_POST['nom'] ?? '');
        $type  = in_array($_POST['type'] ?? '', ['parfum', 'vetement']) ? $_POST['type'] : 'parfum';
        $ordre = (int) ($_POST['ordre'] ?? 0);

        if ($nom === '') {
            $erreurs[] = 'Le nom de la catégorie est obligatoire.';
        } else {
            $slugBase = slugify($nom);
            $slug = $slugBase;
            $cpt = 1;
            while (true) {
                $stSlug = db()->prepare('SELECT id FROM categories WHERE slug = ? AND id != ?');
                $stSlug->execute([$slug, $id]);
                if (!$stSlug->fetch()) {
                    break;
                }
                $cpt++;
                $slug = $slugBase . '-' . $cpt;
            }

            if ($id > 0) {
                $st = db()->prepare('UPDATE categories SET nom = :n, slug = :s, type = :t, ordre = :o WHERE id = :id');
                $st->execute([':n' => $nom, ':s' => $slug, ':t' => $type, ':o' => $ordre, ':id' => $id]);
                $succes = 'Catégorie mise à jour.';
            } else {
                $st = db()->prepare('INSERT INTO categories (nom, slug, type, ordre) VALUES (:n, :s, :t, :o)');
                $st->execute([':n' => $nom, ':s' => $slug, ':t' => $type, ':o' => $ordre]);
                $succes = 'Catégorie créée.';
            }
        }
    }
}

// Liste de toutes les catégories avec décompte des produits
$sql = "SELECT c.*, COUNT(p.id) AS nb_produits
          FROM categories c
     LEFT JOIN produits p ON p.categorie_id = c.id
      GROUP BY c.id
      ORDER BY c.type ASC, c.ordre ASC, c.nom ASC";
$categories = db()->query($sql)->fetchAll();

// Si on édite une catégorie
$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
$catAEditer = null;
if ($editId > 0) {
    foreach ($categories as $cat) {
        if ((int) $cat['id'] === $editId) {
            $catAEditer = $cat;
            break;
        }
    }
}

admin_debut('Catégories', 'categories');
?>

<section class="page-head">
  <div>
    <span class="label-caps"><i class="dot"></i> Catalogue &bull; Organisation</span>
    <h1 class="titre">Catégories de la collection</h1>
    <p class="sous-titre">Organisez vos articles par univers olfactif ou vestimentaire.</p>
  </div>
</section>

<?php if ($succes): ?>
  <div class="msg-success"><?= icon('check', 16) ?> <?= e($succes) ?></div>
<?php endif; ?>

<?php if ($erreurs): ?>
  <div class="msg-error">
    <?php foreach ($erreurs as $err): ?>
      <div><?= e($err) ?></div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<div class="grille-2">
  <!-- Liste des catégories -->
  <div class="panel">
    <div class="panel-head">
      <div>
        <h2 class="panel-titre"><?= count($categories) ?> Catégorie<?= count($categories) > 1 ? 's' : '' ?></h2>
        <p class="panel-sous">Classées par univers et ordre d'affichage</p>
      </div>
    </div>

    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>Nom</th>
            <th>Type</th>
            <th>Slug</th>
            <th>Ordre</th>
            <th>Articles</th>
            <th style="text-align: right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($categories as $cat): ?>
            <tr>
              <td>
                <strong style="color: var(--white);"><?= e($cat['nom']) ?></strong>
              </td>
              <td>
                <span class="pill"><?= $cat['type'] === 'vetement' ? 'Vêtement' : 'Parfum' ?></span>
              </td>
              <td><code class="muted"><?= e($cat['slug']) ?></code></td>
              <td><?= (int) $cat['ordre'] ?></td>
              <td><?= (int) $cat['nb_produits'] ?> ref.</td>
              <td>
                <div class="actions-cell">
                  <a href="categories.php?edit=<?= (int) $cat['id'] ?>" class="btn-ghost btn-sm">
                    <?= icon('edit', 14) ?>
                  </a>
                  <form method="post" onsubmit="return confirm('Supprimer cette catégorie ? Les produits associés resteront sans catégorie.');" style="margin: 0;">
                    <?= csrf_champ() ?>
                    <input type="hidden" name="action" value="supprimer">
                    <input type="hidden" name="id" value="<?= (int) $cat['id'] ?>">
                    <button type="submit" class="icon-btn bordered" style="width: 32px; height: 32px;" title="Supprimer">
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
  </div>

  <!-- Formulaire d'ajout / modification -->
  <div class="panel">
    <div class="panel-head">
      <div>
        <h2 class="panel-titre"><?= $catAEditer ? 'Modifier la catégorie' : 'Nouvelle catégorie' ?></h2>
        <p class="panel-sous"><?= $catAEditer ? 'Éditer : ' . e($catAEditer['nom']) : 'Créer un nouveau classement' ?></p>
      </div>
      <?php if ($catAEditer): ?>
        <a href="categories.php" class="btn-ghost btn-sm">Annuler</a>
      <?php endif; ?>
    </div>

    <form method="post">
      <?= csrf_champ() ?>
      <input type="hidden" name="action" value="enregistrer">
      <input type="hidden" name="id" value="<?= $catAEditer ? (int) $catAEditer['id'] : 0 ?>">

      <div class="form-group">
        <label class="form-label" for="nom">Nom de la catégorie <span class="req">*</span></label>
        <input type="text" id="nom" name="nom" class="form-control" required 
               value="<?= e($catAEditer['nom'] ?? '') ?>" placeholder="Ex: Parfums Unisexe, Éditions Rares">
      </div>

      <div class="form-group">
        <label class="form-label" for="type">Univers <span class="req">*</span></label>
        <select id="type" name="type" class="form-control">
          <option value="parfum" <?= ($catAEditer['type'] ?? '') === 'parfum' ? 'selected' : '' ?>>Parfum</option>
          <option value="vetement" <?= ($catAEditer['type'] ?? '') === 'vetement' ? 'selected' : '' ?>>Vêtement</option>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label" for="ordre">Ordre d'affichage</label>
        <input type="number" id="ordre" name="ordre" class="form-control" 
               value="<?= (int) ($catAEditer['ordre'] ?? 1) ?>" min="0" step="1">
        <div class="form-hint">Les catégories avec les plus petits chiffres s'affichent en premier.</div>
      </div>

      <button type="submit" class="btn-primary btn-full" style="margin-top: 24px;">
        <?= $catAEditer ? 'Mettre à jour' : 'Ajouter la catégorie' ?>
      </button>
    </form>
  </div>
</div>

<?php admin_fin(); ?>
