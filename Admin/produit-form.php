<?php
// =====================================================================
//  BLACK COLLECTION - Formulaire d'ajout / modification de produit
// =====================================================================
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/sidebar.php';
exiger_admin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$edition = $id > 0;
$erreurs = [];

// Catégories disponibles
$categories = db()->query('SELECT id, nom, type FROM categories ORDER BY type ASC, ordre ASC, nom ASC')->fetchAll();

// Valeurs par défaut
$p = [
    'nom'              => '',
    'type'             => 'parfum',
    'categorie_id'     => '',
    'style'            => '',
    'notes_olfactives' => '',
    'description'      => '',
    'contenance'       => '',
    'prix'             => '',
    'disponibilite'    => 'en_stock',
    'tailles'          => '',
    'couleurs'         => '',
    'mis_en_avant'     => 0,
    'visible'          => 1,
];
$imageActuelle = null;

// Si édition, on charge le produit existant
if ($edition) {
    $st = db()->prepare('SELECT * FROM produits WHERE id = ?');
    $st->execute([$id]);
    $existant = $st->fetch();

    if (!$existant) {
        rediriger('produits.php');
    }
    $p = $existant;

    // Charger l'image principale actuelle
    $stImg = db()->prepare('SELECT chemin FROM produit_images WHERE produit_id = ? ORDER BY principale DESC, ordre ASC LIMIT 1');
    $stImg->execute([$id]);
    $imageActuelle = $stImg->fetchColumn() ?: null;
}

// Traitement du formulaire POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valide()) {
        $erreurs[] = 'Session expirée. Veuillez recharger la page.';
    } else {
        $p['nom']              = trim($_POST['nom'] ?? '');
        $p['type']             = in_array($_POST['type'] ?? '', ['parfum', 'vetement']) ? $_POST['type'] : 'parfum';
        $p['categorie_id']     = !empty($_POST['categorie_id']) ? (int) $_POST['categorie_id'] : null;
        $p['style']            = trim($_POST['style'] ?? '');
        $p['notes_olfactives'] = trim($_POST['notes_olfactives'] ?? '');
        $p['description']      = trim($_POST['description'] ?? '');
        $p['contenance']       = trim($_POST['contenance'] ?? '');
        $p['prix']             = (int) ($_POST['prix'] ?? 0);
        $p['disponibilite']    = ($_POST['disponibilite'] ?? '') === 'rupture' ? 'rupture' : 'en_stock';
        $p['tailles']          = trim($_POST['tailles'] ?? '');
        $p['couleurs']         = trim($_POST['couleurs'] ?? '');
        $p['mis_en_avant']     = isset($_POST['mis_en_avant']) ? 1 : 0;
        $p['visible']          = isset($_POST['visible']) ? 1 : 0;

        if ($p['nom'] === '') {
            $erreurs[] = 'Le nom du produit est obligatoire.';
        }
        if ($p['prix'] <= 0) {
            $erreurs[] = 'Le prix doit être supérieur à 0 FCFA.';
        }

        if (empty($erreurs)) {
            // Génération du slug unique
            $slugBase = slugify($p['nom']);
            $slug = $slugBase;
            $cpt = 1;
            while (true) {
                $stSlug = db()->prepare('SELECT id FROM produits WHERE slug = ? AND id != ?');
                $stSlug->execute([$slug, $id]);
                if (!$stSlug->fetch()) {
                    break;
                }
                $cpt++;
                $slug = $slugBase . '-' . $cpt;
            }

            if ($edition) {
                $sql = 'UPDATE produits SET 
                            type = :type,
                            nom = :nom,
                            slug = :slug,
                            categorie_id = :categorie_id,
                            style = :style,
                            notes_olfactives = :notes_olfactives,
                            description = :description,
                            contenance = :contenance,
                            prix = :prix,
                            disponibilite = :disponibilite,
                            tailles = :tailles,
                            couleurs = :couleurs,
                            mis_en_avant = :mis_en_avant,
                            visible = :visible
                        WHERE id = :id';
                $st = db()->prepare($sql);
                $st->execute([
                    ':type'             => $p['type'],
                    ':nom'              => $p['nom'],
                    ':slug'             => $slug,
                    ':categorie_id'     => $p['categorie_id'],
                    ':style'            => $p['style'],
                    ':notes_olfactives' => $p['notes_olfactives'],
                    ':description'      => $p['description'],
                    ':contenance'       => $p['contenance'],
                    ':prix'             => $p['prix'],
                    ':disponibilite'    => $p['disponibilite'],
                    ':tailles'          => $p['tailles'],
                    ':couleurs'         => $p['couleurs'],
                    ':mis_en_avant'     => $p['mis_en_avant'],
                    ':visible'          => $p['visible'],
                    ':id'               => $id,
                ]);
                $produitId = $id;
                $msg = 'modifie';
            } else {
                $sql = 'INSERT INTO produits (
                            type, nom, slug, categorie_id, style, notes_olfactives,
                            description, contenance, prix, disponibilite, tailles,
                            couleurs, mis_en_avant, visible
                        ) VALUES (
                            :type, :nom, :slug, :categorie_id, :style, :notes_olfactives,
                            :description, :contenance, :prix, :disponibilite, :tailles,
                            :couleurs, :mis_en_avant, :visible
                        )';
                $st = db()->prepare($sql);
                $st->execute([
                    ':type'             => $p['type'],
                    ':nom'              => $p['nom'],
                    ':slug'             => $slug,
                    ':categorie_id'     => $p['categorie_id'],
                    ':style'            => $p['style'],
                    ':notes_olfactives' => $p['notes_olfactives'],
                    ':description'      => $p['description'],
                    ':contenance'       => $p['contenance'],
                    ':prix'             => $p['prix'],
                    ':disponibilite'    => $p['disponibilite'],
                    ':tailles'          => $p['tailles'],
                    ':couleurs'         => $p['couleurs'],
                    ':mis_en_avant'     => $p['mis_en_avant'],
                    ':visible'          => $p['visible'],
                ]);
                $produitId = (int) db()->lastInsertId();
                $msg = 'cree';
            }

            // Gestion de l'image téléversée
            if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $tmpName = $_FILES['photo']['tmp_name'];
                $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];

                if (in_array($ext, $allowed, true) && @getimagesize($tmpName) !== false && $_FILES['photo']['size'] <= 5 * 1024 * 1024) {
                    $dossierUpload = dirname(__DIR__) . '/uploads/produits/';
                    if (!is_dir($dossierUpload)) {
                        mkdir($dossierUpload, 0755, true);
                    }
                    $nomFichier = 'produit_' . $produitId . '_' . time() . '.' . $ext;
                    $destination = $dossierUpload . $nomFichier;

                    if (move_uploaded_file($tmpName, $destination)) {
                        // Enregistrer dans produit_images
                        $cheminBdd = 'uploads/produits/' . $nomFichier;
                        // Anciennes photos du produit (supprimées après l'ajout de la nouvelle)
                        $stOld = db()->prepare('SELECT id, chemin FROM produit_images WHERE produit_id = ?');
                        $stOld->execute([$produitId]);
                        $anciennes = $stOld->fetchAll();

                        $stImg = db()->prepare('INSERT INTO produit_images (produit_id, chemin, principale) VALUES (?, ?, 1)');
                        $stImg->execute([$produitId, $cheminBdd]);
                        $nouvelId = (int) db()->lastInsertId();

                        foreach ($anciennes as $old) {
                            $fichierOld = dirname(__DIR__) . '/' . $old['chemin'];
                            if (is_file($fichierOld)) {
                                @unlink($fichierOld);
                            }
                        }
                        db()->prepare('DELETE FROM produit_images WHERE produit_id = ? AND id <> ?')->execute([$produitId, $nouvelId]);
                    }
                }
            }

            rediriger('produits.php?msg=' . $msg);
        }
    }
}

admin_debut($edition ? 'Modifier un produit' : 'Ajouter un produit', 'ajouter');
?>

<section class="page-head">
  <div>
    <span class="label-caps"><i class="dot"></i> Catalogue &bull; <?= $edition ? 'Édition' : 'Création' ?></span>
    <h1 class="titre"><?= $edition ? 'Modifier : ' . e($p['nom']) : 'Ajouter un nouveau produit' ?></h1>
    <p class="sous-titre">Complétez les informations pour publier ou actualiser cet article.</p>
  </div>
  <div class="head-actions">
    <a href="produits.php" class="btn-ghost"><?= icon('chevron', 14) ?> Retour à la liste</a>
  </div>
</section>

<?php if (!empty($erreurs)): ?>
  <div class="msg-error">
    <?php foreach ($erreurs as $err): ?>
      <div><?= e($err) ?></div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="panel">
  <?= csrf_champ() ?>

  <div class="form-grid">
    <div class="form-group full">
      <label class="form-label" for="nom">Nom de l'article <span class="req">*</span></label>
      <input type="text" id="nom" name="nom" class="form-control" value="<?= e($p['nom']) ?>" required placeholder="Ex: Eau de Parfum - Midnight Wood">
    </div>

    <div class="form-group">
      <label class="form-label" for="type">Type de produit <span class="req">*</span></label>
      <select id="type" name="type" class="form-control">
        <option value="parfum" <?= $p['type'] === 'parfum' ? 'selected' : '' ?>>Parfum / Essence</option>
        <option value="vetement" <?= $p['type'] === 'vetement' ? 'selected' : '' ?>>Vêtement</option>
      </select>
    </div>

    <div class="form-group">
      <label class="form-label" for="categorie_id">Catégorie</label>
      <select id="categorie_id" name="categorie_id" class="form-control">
        <option value="">-- Sans catégorie --</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= (int) $cat['id'] ?>" <?= (int) $p['categorie_id'] === (int) $cat['id'] ? 'selected' : '' ?>>
            <?= e($cat['nom']) ?> (<?= $cat['type'] === 'vetement' ? 'Vêtement' : 'Parfum' ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group">
      <label class="form-label" for="prix">Prix (FCFA) <span class="req">*</span></label>
      <input type="number" id="prix" name="prix" class="form-control" value="<?= e((string) $p['prix']) ?>" required min="0" step="500" placeholder="Ex: 25000">
    </div>

    <div class="form-group">
      <label class="form-label" for="contenance">Contenance / Format</label>
      <input type="text" id="contenance" name="contenance" class="form-control" value="<?= e($p['contenance'] ?? '') ?>" placeholder="Ex: 50 ml, 100 ml, Coffret">
    </div>

    <div class="form-group">
      <label class="form-label" for="disponibilite">Disponibilité du stock</label>
      <select id="disponibilite" name="disponibilite" class="form-control">
        <option value="en_stock" <?= $p['disponibilite'] === 'en_stock' ? 'selected' : '' ?>>En stock</option>
        <option value="rupture" <?= $p['disponibilite'] === 'rupture' ? 'selected' : '' ?>>En rupture de stock</option>
      </select>
    </div>

    <div class="form-group">
      <label class="form-label" for="style">Style / Univers</label>
      <input type="text" id="style" name="style" class="form-control" value="<?= e($p['style'] ?? '') ?>" placeholder="Ex: Boisé & Épicé, Cuir Sombre">
    </div>

    <div class="form-group full">
      <label class="form-label" for="notes_olfactives">Notes olfactives (pour parfums)</label>
      <input type="text" id="notes_olfactives" name="notes_olfactives" class="form-control" value="<?= e($p['notes_olfactives'] ?? '') ?>" placeholder="Ex: Oud, Ambre Noir, Bergamote, Bois de cèdre">
      <div class="form-hint">Séparez les notes par des virgules.</div>
    </div>

    <div class="form-group full">
      <label class="form-label" for="description">Description détaillée</label>
      <textarea id="description" name="description" class="form-control" placeholder="Racontez l'histoire et l'expérience de cette pièce..."><?= e($p['description'] ?? '') ?></textarea>
    </div>

    <div class="form-group full">
      <label class="form-label" for="photo">Photo principale</label>
      <?php if (!empty($imageActuelle)): ?>
        <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 12px;">
          <img src="../<?= e($imageActuelle) ?>" alt="Photo actuelle" style="width: 80px; height: 80px; object-fit: cover; border: 1px solid var(--line);">
          <span class="muted" style="font-size: 13px;">Photo actuelle enregistrée. Choisissez un nouveau fichier ci-dessous pour la remplacer.</span>
        </div>
      <?php endif; ?>
      <input type="file" id="photo" name="photo" class="form-control" accept="image/jpeg,image/png,image/webp">
      <div class="form-hint">Formats acceptés : JPG, PNG, WEBP. Résolution recommandée : carrée 800x800 ou portrait.</div>
    </div>

    <div class="form-group">
      <label class="form-check">
        <input type="checkbox" name="mis_en_avant" value="1" <?= !empty($p['mis_en_avant']) ? 'checked' : '' ?>>
        <span>Mettre en avant sur la page d'accueil (Coup de cœur)</span>
      </label>
    </div>

    <div class="form-group">
      <label class="form-check">
        <input type="checkbox" name="visible" value="1" <?= !empty($p['visible']) ? 'checked' : '' ?>>
        <span>Visible sur la boutique publique</span>
      </label>
    </div>
  </div>

  <div style="margin-top: 32px; display: flex; gap: 16px;">
    <button type="submit" class="btn-primary"><?= $edition ? 'Enregistrer les modifications' : 'Créer et publier le produit' ?></button>
    <a href="produits.php" class="btn-ghost">Annuler</a>
  </div>
</form>

<?php admin_fin(); ?>
