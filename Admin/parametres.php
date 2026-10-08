<?php
// =====================================================================
//  BLACK COLLECTION - Paramètres généraux du site
// =====================================================================
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/sidebar.php';
exiger_admin();

$cles = [
    'nom_marque',
    'slogan',
    'whatsapp',
    'wave_business',
    'livraison_minimum',
    'livraison_zone',
    'instagram',
    'tiktok',
    'facebook',
    'adresse',
];

$succes = '';
$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valide()) {
        $erreur = 'Jeton de sécurité invalide. Rechargez la page.';
    } else {
        foreach ($cles as $cle) {
            if (isset($_POST[$cle])) {
                $val = trim((string) $_POST[$cle]);
                // Nettoyer numéro whatsapp si saisi avec des espaces ou des +
                if ($cle === 'whatsapp') {
                    $val = preg_replace('/[^0-9]/', '', $val);
                }
                set_param($cle, $val);
            }
        }

        // --- Logo de la boutique (lu directement en base : get_param() garde un cache) ---
        $stLogo = db()->prepare('SELECT valeur FROM parametres WHERE cle = ?');
        $stLogo->execute(['logo']);
        $logoActuel = (string) $stLogo->fetchColumn();
        $effacerAncien = function () use (&$logoActuel): void {
            if ($logoActuel !== '') {
                $ancien = dirname(__DIR__) . '/' . $logoActuel;
                if (is_file($ancien)) {
                    @unlink($ancien);
                }
            }
        };

        if (!empty($_POST['supprimer_logo'])) {
            $effacerAncien();
            set_param('logo', '');
        } elseif (!empty($_FILES['logo']['name'])) {
            $f = $_FILES['logo'];
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            if ($f['error'] !== UPLOAD_ERR_OK) {
                $erreur = 'Le téléversement du logo a échoué (fichier trop lourd ?).';
            } elseif (!in_array($ext, ['png', 'jpg', 'jpeg', 'webp'], true) || @getimagesize($f['tmp_name']) === false) {
                $erreur = 'Logo refusé : utilisez une vraie image PNG, JPG ou WEBP.';
            } elseif ($f['size'] > 2 * 1024 * 1024) {
                $erreur = 'Logo trop lourd : 2 Mo maximum.';
            } else {
                $dossier = dirname(__DIR__) . '/uploads/logo/';
                if (!is_dir($dossier)) {
                    mkdir($dossier, 0755, true);
                }
                $nomFichier = 'logo_' . time() . '.' . $ext;
                if (move_uploaded_file($f['tmp_name'], $dossier . $nomFichier)) {
                    $effacerAncien();
                    set_param('logo', 'uploads/logo/' . $nomFichier);
                } else {
                    $erreur = 'Impossible d\'enregistrer le logo sur le serveur.';
                }
            }
        }

        if ($erreur === '') {
            $succes = 'Paramètres enregistrés avec succès.';
        }
    }
}

// Rechargement des paramètres
$params = [];
foreach ($cles as $cle) {
    $params[$cle] = get_param($cle);
}
$logo = get_param('logo');

admin_debut('Paramètres', 'parametres');
?>

<section class="page-head">
  <div>
    <span class="label-caps"><i class="dot"></i> Configuration &bull; Marque & Vente</span>
    <h1 class="titre">Paramètres de la boutique</h1>
    <p class="sous-titre">Coordonnées WhatsApp, règles de livraison et réseaux sociaux.</p>
  </div>
</section>

<?php if ($succes): ?>
  <div class="msg-success"><?= icon('check', 16) ?> <?= e($succes) ?></div>
<?php endif; ?>

<?php if ($erreur): ?>
  <div class="msg-error"><?= e($erreur) ?></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="panel">
  <?= csrf_champ() ?>

  <div class="form-grid">
    <!-- Identité de marque -->
    <div class="form-group full" style="border-bottom: 1px solid var(--line-soft); padding-bottom: 12px; margin-bottom: 24px;">
      <h3 style="font-family: var(--serif); font-size: 20px; color: var(--white); margin: 0 0 6px;">Identité & Marque</h3>
      <span class="muted" style="font-size: 13px;">Appellation officielle et devise affichée sur la vitrine</span>
    </div>

    <div class="form-group full">
      <label class="form-label" for="logo">Logo de la boutique</label>
      <?php if ($logo !== ''): ?>
        <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 12px;">
          <img src="../<?= e($logo) ?>" alt="Logo actuel" style="max-height: 64px; max-width: 220px; object-fit: contain; background: #0c1419; border: 1px solid var(--line); padding: 8px;">
          <label class="form-check">
            <input type="checkbox" name="supprimer_logo" value="1">
            <span>Supprimer le logo (revenir au nom de la marque)</span>
          </label>
        </div>
      <?php endif; ?>
      <input type="file" id="logo" name="logo" class="form-control" accept="image/png,image/jpeg,image/webp">
      <div class="form-hint">PNG, JPG ou WEBP, 2 Mo maximum. Idéal : PNG à fond transparent, en couleurs claires (le site est sombre). Affiché sur la boutique, la page de connexion et le tableau de bord.</div>
    </div>

    <div class="form-group">
      <label class="form-label" for="nom_marque">Nom de la marque</label>
      <input type="text" id="nom_marque" name="nom_marque" class="form-control" value="<?= e($params['nom_marque']) ?>" required>
    </div>

    <div class="form-group">
      <label class="form-label" for="slogan">Slogan / Devise</label>
      <input type="text" id="slogan" name="slogan" class="form-control" value="<?= e($params['slogan']) ?>">
    </div>

    <!-- Vente & Commandes WhatsApp -->
    <div class="form-group full" style="border-bottom: 1px solid var(--line-soft); padding-bottom: 12px; margin-top: 16px; margin-bottom: 24px;">
      <h3 style="font-family: var(--serif); font-size: 20px; color: var(--white); margin: 0 0 6px;">Commandes & Paiements</h3>
      <span class="muted" style="font-size: 13px;">Numéro de réception des commandes clients et paiements</span>
    </div>

    <div class="form-group">
      <label class="form-label" for="whatsapp">Numéro WhatsApp (avec indicatif)</label>
      <input type="text" id="whatsapp" name="whatsapp" class="form-control" value="<?= e($params['whatsapp']) ?>" placeholder="Ex: 2250554971592">
      <div class="form-hint">Format international sans le signe +, ex: 2250554971592</div>
    </div>

    <div class="form-group">
      <label class="form-label" for="wave_business">Numéro Wave Business / QR</label>
      <input type="text" id="wave_business" name="wave_business" class="form-control" value="<?= e($params['wave_business']) ?>" placeholder="Numéro ou lien Wave">
    </div>

    <div class="form-group">
      <label class="form-label" for="livraison_minimum">Minimum de commande (parfums uniquement)</label>
      <input type="number" id="livraison_minimum" name="livraison_minimum" class="form-control" value="<?= e($params['livraison_minimum']) ?>" min="1">
      <div class="form-hint">Nombre minimum de flacons de parfum requis (les vêtements ne sont pas soumis à ce minimum).</div>
    </div>

    <div class="form-group">
      <label class="form-label" for="livraison_zone">Zone de livraison principale</label>
      <input type="text" id="livraison_zone" name="livraison_zone" class="form-control" value="<?= e($params['livraison_zone']) ?>" placeholder="Ex: Abidjan et intérieur du pays">
    </div>

    <div class="form-group full">
      <label class="form-label" for="adresse">Adresse physique / Atelier</label>
      <input type="text" id="adresse" name="adresse" class="form-control" value="<?= e($params['adresse']) ?>" placeholder="Ex: Cocody Angré, Abidjan">
    </div>

    <!-- Réseaux Sociaux -->
    <div class="form-group full" style="border-bottom: 1px solid var(--line-soft); padding-bottom: 12px; margin-top: 16px; margin-bottom: 24px;">
      <h3 style="font-family: var(--serif); font-size: 20px; color: var(--white); margin: 0 0 6px;">Réseaux Sociaux</h3>
      <span class="muted" style="font-size: 13px;">Liens publics pour la communauté</span>
    </div>

    <div class="form-group">
      <label class="form-label" for="instagram">Lien ou nom Instagram</label>
      <input type="text" id="instagram" name="instagram" class="form-control" value="<?= e($params['instagram']) ?>" placeholder="@blackcollection.ci">
    </div>

    <div class="form-group">
      <label class="form-label" for="tiktok">Lien ou nom TikTok</label>
      <input type="text" id="tiktok" name="tiktok" class="form-control" value="<?= e($params['tiktok']) ?>" placeholder="@blackcollection">
    </div>

    <div class="form-group">
      <label class="form-label" for="facebook">Page Facebook</label>
      <input type="text" id="facebook" name="facebook" class="form-control" value="<?= e($params['facebook']) ?>" placeholder="blackcollectionofficiel">
    </div>
  </div>

  <div style="margin-top: 32px;">
    <button type="submit" class="btn-primary">Enregistrer les paramètres</button>
  </div>
</form>

<?php admin_fin(); ?>