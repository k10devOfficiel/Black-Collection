<?php
// =====================================================================
//  BLACK COLLECTION - Clients (suivi : commandes, total dépensé)
// =====================================================================
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/sidebar.php';
exiger_admin();

$q = trim($_GET['q'] ?? '');
$args = [];
$where = '';
if ($q !== '') {
    $where = 'WHERE c.nom LIKE :q1 OR c.telephone LIKE :q2 OR c.adresse LIKE :q3';
    $args = [':q1' => '%' . $q . '%', ':q2' => '%' . $q . '%', ':q3' => '%' . $q . '%'];
}

$st = db()->prepare(
    "SELECT c.id, c.nom, c.telephone, c.adresse, c.cree_le,
            COUNT(o.id) AS nb_commandes,
            COALESCE(SUM(CASE WHEN o.statut <> 'annulee' THEN o.total END), 0) AS depense,
            MAX(o.cree_le) AS derniere
       FROM clients c
  LEFT JOIN commandes o ON o.client_id = c.id
       $where
   GROUP BY c.id, c.nom, c.telephone, c.adresse, c.cree_le
   ORDER BY derniere DESC, c.id DESC
      LIMIT 300"
);
$st->execute($args);
$clients = $st->fetchAll();

admin_debut('Clients', 'clients');
?>
<section class="page-head">
  <div>
    <span class="label-caps"><i class="dot"></i> Relation client</span>
    <h1 class="titre">Clients</h1>
    <p class="sous-titre">Enregistrés automatiquement à chaque commande (identifiés par leur numéro).</p>
  </div>
</section>

<div class="panel">
  <div class="panel-head">
    <div><h2 class="panel-titre"><?= count($clients) ?> client<?= count($clients) > 1 ? 's' : '' ?></h2></div>
    <form method="get" class="outils">
      <label class="recherche"><?= icon('search', 16) ?>
        <input type="search" name="q" value="<?= e($q) ?>" placeholder="Nom, numéro, quartier...">
      </label>
    </form>
  </div>

  <?php if (!$clients): ?>
    <div class="vide"><p>Aucun client pour l'instant.</p></div>
  <?php else: ?>
  <div class="table-wrap">
    <table class="data-table">
      <thead><tr><th>Client</th><th>Contact</th><th>Adresse</th><th>Commandes</th><th>Total dépensé</th><th>Dernière commande</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($clients as $c): ?>
        <tr>
          <td><strong style="color:var(--white);"><?= e($c['nom']) ?></strong></td>
          <td><a href="https://wa.me/<?= e($c['telephone']) ?>" target="_blank" rel="noopener">+<?= e($c['telephone']) ?></a></td>
          <td><?= e($c['adresse']) ?></td>
          <td><?= (int) $c['nb_commandes'] ?></td>
          <td><?= e(prix((int) $c['depense'])) ?></td>
          <td><?= $c['derniere'] ? e(date_fr(strtotime($c['derniere']))) : '-' ?></td>
          <td><a href="commandes.php?client=<?= (int) $c['id'] ?>" class="btn-ghost btn-sm">Commandes</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
<?php admin_fin(); ?>