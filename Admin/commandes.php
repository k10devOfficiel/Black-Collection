<?php
// =====================================================================
//  BLACK COLLECTION - Commandes (liste, détail, changement de statut)
// =====================================================================
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/sidebar.php';
exiger_admin();

$statuts = ['nouvelle' => 'Nouvelle', 'confirmee' => 'Confirmée', 'livree' => 'Livrée', 'annulee' => 'Annulée'];
$succes = '';
$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valide()) {
        $erreur = 'Jeton de sécurité invalide.';
    } else {
        $cid = (int) ($_POST['id'] ?? 0);
        $nouveau = $_POST['statut'] ?? '';
        if ($cid > 0 && isset($statuts[$nouveau])) {
            db()->prepare('UPDATE commandes SET statut = ? WHERE id = ?')->execute([$nouveau, $cid]);
            $succes = 'Statut mis à jour.';
        }
    }
}

$detailId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$detail = null;
$items = [];
if ($detailId > 0) {
    $st = db()->prepare('SELECT * FROM commandes WHERE id = ?');
    $st->execute([$detailId]);
    $detail = $st->fetch() ?: null;
    if ($detail) {
        $si = db()->prepare('SELECT * FROM commande_items WHERE commande_id = ? ORDER BY id');
        $si->execute([$detailId]);
        $items = $si->fetchAll();
    }
}

$filtre   = isset($statuts[$_GET['statut'] ?? '']) ? $_GET['statut'] : '';
$clientId = (int) ($_GET['client'] ?? 0);
$where = [];
$args = [];
if ($filtre !== '')  { $where[] = 'statut = ?';    $args[] = $filtre; }
if ($clientId > 0)   { $where[] = 'client_id = ?'; $args[] = $clientId; }
$sql = 'SELECT * FROM commandes' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY cree_le DESC, id DESC LIMIT 200';
$st = db()->prepare($sql);
$st->execute($args);
$commandes = $st->fetchAll();

admin_debut('Commandes', 'commandes');
?>
<section class="page-head">
  <div>
    <span class="label-caps"><i class="dot"></i> Ventes &bull; Suivi</span>
    <h1 class="titre">Commandes</h1>
    <p class="sous-titre">Commandes reçues depuis la boutique (envoyées aussi sur WhatsApp).</p>
  </div>
</section>

<?php if ($succes): ?><div class="msg-success"><?= icon('check', 16) ?> <?= e($succes) ?></div><?php endif; ?>
<?php if ($erreur): ?><div class="msg-error"><?= e($erreur) ?></div><?php endif; ?>

<?php if ($detail): ?>
<div class="panel" style="margin-bottom:24px;">
  <div class="panel-head">
    <div>
      <h2 class="panel-titre">Commande <?= e($detail['reference']) ?></h2>
      <p class="panel-sous"><?= e(date_fr(strtotime($detail['cree_le']))) ?> à <?= e(date('H:i', strtotime($detail['cree_le']))) ?></p>
    </div>
    <a href="commandes.php" class="btn-ghost btn-sm">Fermer</a>
  </div>

  <p><strong style="color:var(--white);"><?= e($detail['nom_client']) ?></strong><br>
     Tél : <a href="https://wa.me/<?= e($detail['telephone']) ?>" target="_blank" rel="noopener">+<?= e($detail['telephone']) ?></a><br>
     Livraison : <?= e($detail['adresse']) ?><br>
     Paiement : <?= $detail['paiement'] === 'wave' ? 'Wave' : 'Espèces' ?>
     <?php if ($detail['note']): ?><br>Note : <?= e($detail['note']) ?><?php endif; ?></p>

  <div class="table-wrap">
    <table class="data-table">
      <thead><tr><th>Article</th><th>Qté</th><th>Prix unit.</th><th>Sous-total</th></tr></thead>
      <tbody>
      <?php foreach ($items as $it): ?>
        <tr>
          <td><?= e($it['nom']) ?> <span class="muted"><?= e($it['type'] === 'vetement' ? '[Vêtement]' : ($it['contenance'] ? '(' . $it['contenance'] . ')' : '')) ?></span></td>
          <td><?= (int) $it['qte'] ?></td>
          <td><?= e(prix((int) $it['prix'])) ?></td>
          <td><?= e(prix((int) $it['prix'] * (int) $it['qte'])) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <p style="text-align:right;margin-top:12px;"><strong>Total : <?= e(prix((int) $detail['total'])) ?></strong></p>

  <form method="post" style="display:flex;gap:10px;align-items:center;">
    <?= csrf_champ() ?>
    <input type="hidden" name="id" value="<?= (int) $detail['id'] ?>">
    <select name="statut" class="form-control" style="max-width:220px;">
      <?php foreach ($statuts as $k => $lib): ?>
        <option value="<?= $k ?>" <?= $detail['statut'] === $k ? 'selected' : '' ?>><?= $lib ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn-primary">Mettre à jour le statut</button>
  </form>
</div>
<?php endif; ?>

<div class="panel">
  <div class="panel-head">
    <div><h2 class="panel-titre"><?= count($commandes) ?> commande<?= count($commandes) > 1 ? 's' : '' ?></h2></div>
    <div class="outils">
      <a href="commandes.php" class="btn-ghost btn-sm">Toutes</a>
      <?php foreach ($statuts as $k => $lib): ?>
        <a href="commandes.php?statut=<?= $k ?>" class="btn-ghost btn-sm"><?= $lib ?></a>
      <?php endforeach; ?>
    </div>
  </div>

  <?php if (!$commandes): ?>
    <div class="vide"><p>Aucune commande pour l'instant.</p></div>
  <?php else: ?>
  <div class="table-wrap">
    <table class="data-table">
      <thead><tr><th>Réf.</th><th>Client</th><th>Articles</th><th>Total</th><th>Paiement</th><th>Statut</th><th>Date</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($commandes as $c): ?>
        <tr>
          <td><code><?= e($c['reference']) ?></code></td>
          <td><?= e($c['nom_client']) ?><br><span class="muted">+<?= e($c['telephone']) ?></span></td>
          <td><?= (int) $c['nb_articles'] ?></td>
          <td><?= e(prix((int) $c['total'])) ?></td>
          <td><?= $c['paiement'] === 'wave' ? 'Wave' : 'Espèces' ?></td>
          <td><span class="pill"><?= e($statuts[$c['statut']]) ?></span></td>
          <td><?= e(date_fr(strtotime($c['cree_le']))) ?></td>
          <td><a href="commandes.php?id=<?= (int) $c['id'] ?>" class="btn-ghost btn-sm">Voir</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
<?php admin_fin(); ?>