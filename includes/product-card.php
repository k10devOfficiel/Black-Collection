<?php
// =====================================================================
//  BLACK COLLECTION - Composant Carte Produit
// =====================================================================
$rupture = ($p['disponibilite'] === 'rupture');
$catClass = !empty($p['categorie_id']) ? 'cat-' . (int)$p['categorie_id'] : '';
?>
<article class="product-card" 
         data-type="<?= e($p['type']) ?>" 
         data-category="<?= e($catClass) ?>"
         data-id="<?= (int)$p['id'] ?>"
         data-nom="<?= e($p['nom']) ?>"
         data-prix="<?= (int)$p['prix'] ?>"
         data-contenance="<?= e($p['contenance'] ?? '') ?>"
         data-photo="<?= !empty($p['photo']) ? e($p['photo']) : '' ?>"
         data-notes="<?= e($p['notes_olfactives'] ?? '') ?>"
         data-desc="<?= e($p['description'] ?? '') ?>"
         data-rupture="<?= $rupture ? '1' : '0' ?>">
  
  <div class="card-media">
    <?php if (!empty($p['mis_en_avant'])): ?>
      <span class="badge-featured">Signature</span>
    <?php endif; ?>

    <span class="badge-status <?= $rupture ? 'status-out' : 'status-in' ?>">
      <?= $rupture ? 'Épuisé' : 'Disponible' ?>
    </span>

    <div class="media-box">
      <?php if (!empty($p['photo'])): ?>
        <img src="<?= e($p['photo']) ?>" alt="<?= e($p['nom']) ?>" loading="lazy" class="card-img">
      <?php else: ?>
        <div class="card-placeholder">
          <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
            <path d="M10 2v7.31L4.14 19.1A2 2 0 0 0 5.86 22h12.28a2 2 0 0 0 1.72-2.9L14 9.31V2"/>
            <line x1="8.5" x2="15.5" y1="2" y2="2"/><line x1="7" x2="17" y1="16" y2="16"/>
          </svg>
          <span><?= e($p['nom']) ?></span>
        </div>
      <?php endif; ?>
    </div>

    <!-- Actions au survol -->
    <div class="card-quick-actions">
      <button type="button" class="quick-view-btn js-quick-view" aria-label="Aperçu des détails">
        Aperçu olfactif
      </button>
    </div>
  </div>

  <div class="card-body">
    <div class="card-meta">
      <span class="card-category"><?= e($p['categorie_nom'] ?? ($p['type'] === 'vetement' ? 'Vêtement' : 'Parfum')) ?></span>
      <?php if (!empty($p['contenance'])): ?>
        <span class="card-contenance"><?= e($p['contenance']) ?></span>
      <?php endif; ?>
    </div>

    <h3 class="card-title"><?= e($p['nom']) ?></h3>

    <?php if (!empty($p['notes_olfactives'])): ?>
      <p class="card-notes" title="<?= e($p['notes_olfactives']) ?>">
        <?= e(mb_strimwidth($p['notes_olfactives'], 0, 48, '...')) ?>
      </p>
    <?php elseif (!empty($p['style'])): ?>
      <p class="card-notes"><?= e($p['style']) ?></p>
    <?php endif; ?>

    <div class="card-footer">
      <div class="card-price"><?= prix((int)$p['prix']) ?></div>

      <?php if ($rupture): ?>
        <button type="button" class="btn-card-disabled" disabled>Épuisé</button>
      <?php else: ?>
        <button type="button" class="btn-card-add js-add-to-cart" 
                data-id="<?= (int)$p['id'] ?>"
                data-nom="<?= e($p['nom']) ?>"
                data-prix="<?= (int)$p['prix'] ?>"
                data-photo="<?= !empty($p['photo']) ? e($p['photo']) : '' ?>"
                data-contenance="<?= e($p['contenance'] ?? '') ?>">
          + Ajouter
        </button>
      <?php endif; ?>
    </div>
  </div>
</article>
