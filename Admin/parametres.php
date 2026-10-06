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
        $succes = 'Paramètres enregistrés avec succès.';
    }
}

// Rechargement des paramètres
$params = [];
foreach ($cles as $cle) {
    $params[$cle] = get_param($cle);
}

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

<form method="post" class="panel">
  <?= csrf_champ() ?>

  <div class="form-grid">
    <!-- Identité de marque -->
    <div class="form-group full" style="border-bottom: 1px solid var(--line-soft); padding-bottom: 12px; margin-bottom: 24px;">
      <h3 style="font-family: var(--serif); font-size: 20px; color: var(--white); margin: 0 0 6px;">Identité & Marque</h3>
      <span class="muted" style="font-size: 13px;">Appellation officielle et devise affichée sur la vitrine</span>
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
