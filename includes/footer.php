<?php
// =====================================================================
//  BLACK COLLECTION - Pied de page public
// =====================================================================
$nomMarque   = get_param('nom_marque', 'Black Collection');
$slogan      = get_param('slogan', "L'élégance a son côté sombre.");
$whatsapp    = get_param('whatsapp', '2250554971592');
$minCommande = (int) get_param('livraison_minimum', '5');
$zone        = get_param('livraison_zone', 'Abidjan');
$adresse     = get_param('adresse', 'Abidjan, Côte d\'Ivoire');
$instagram   = get_param('instagram', '');
$tiktok      = get_param('tiktok', '');
$facebook    = get_param('facebook', '');
?>
  <!-- Tiroir Latéral du Panier (Cart Drawer) -->
  <div class="cart-drawer-overlay" id="cartOverlay"></div>
  <aside class="cart-drawer" id="cartDrawer" aria-label="Votre sélection">
    <div class="cart-header">
      <div class="cart-header-title">
        <h3>Votre Sélection</h3>
        <span class="cart-subtitle" id="cartSummaryText">0 article</span>
      </div>
      <button type="button" class="close-drawer-btn" id="closeCartBtn" aria-label="Fermer le panier">&times;</button>
    </div>

    <!-- Alert minimum de commande -->
    <div class="cart-notice">
      <span class="gold-dot">&bull;</span>
      <span>Minimum requis : <strong><?= $minCommande ?> flacons</strong> pour validation de commande.</span>
    </div>

    <div class="cart-items" id="cartItemsContainer">
      <!-- Rempli dynamiquement en JS -->
      <div class="empty-cart-state">
        <p>Votre sélection est vide.</p>
        <button type="button" class="btn-outline-gold" onclick="document.getElementById('closeCartBtn').click();">Découvrir le catalogue</button>
      </div>
    </div>

    <div class="cart-footer">
      <div class="cart-total-row">
        <span>Sous-total</span>
        <strong id="cartSubtotal">0 FCFA</strong>
      </div>
      <p class="cart-min-warning" id="cartMinWarning">Il vous manque encore des articles pour atteindre le minimum requis.</p>
      <button type="button" class="btn-wa-order" id="btnWhatsappOrder" data-wa="<?= e($whatsapp) ?>" data-min="<?= $minCommande ?>">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2m.01 1.67c2.2 0 4.26.86 5.82 2.42a8.23 8.23 0 0 1 2.41 5.83c0 4.54-3.7 8.24-8.24 8.24-1.48 0-2.93-.4-4.2-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.19 8.19 0 0 1-1.26-4.38c0-4.54 3.7-8.24 8.24-8.24M8.53 7.33c-.16 0-.42.06-.64.3-.22.25-.85.83-.85 2.02s.87 2.34 1 2.5c.12.16 1.71 2.61 4.14 3.66.58.25 1.03.4 1.38.51.58.18 1.11.16 1.53.1.47-.07 1.44-.59 1.64-1.16.2-.57.2-1.06.14-1.16-.06-.1-.22-.16-.47-.28-.25-.13-1.47-.72-1.7-.81-.23-.08-.39-.13-.56.13-.16.25-.64.81-.79.97-.14.16-.29.18-.54.06s-1.05-.39-2-1.23c-.74-.66-1.24-1.47-1.39-1.72-.14-.25-.02-.38.11-.51.11-.11.25-.29.37-.43.13-.15.17-.25.25-.42.09-.16.04-.31-.02-.43-.06-.13-.56-1.35-.77-1.85-.2-.49-.41-.42-.56-.43h-.48z"/>
        </svg>
        Commander sur WhatsApp
      </button>
      <span class="cart-disclaimer">Paiement à la livraison ou par Wave à la confirmation.</span>
    </div>
  </aside>

  <!-- Modal Détails / Aperçu Rapide -->
  <div class="modal-overlay" id="productModalOverlay">
    <div class="modal-card" id="productModalCard">
      <button type="button" class="close-modal-btn" id="closeModalBtn">&times;</button>
      <div class="modal-body" id="modalProductContent">
        <!-- Rempli par JavaScript -->
      </div>
    </div>
  </div>

  <!-- Pied de page -->
  <footer class="site-footer" id="contact">
    <div class="container footer-grid">
      <!-- Colonne 1: Marque -->
      <div class="footer-col brand-col">
        <h4 class="footer-logo"><?= e($nomMarque) ?></h4>
        <p class="footer-slogan"><?= e($slogan) ?></p>
        <p class="footer-desc">Une sélection d'essences intenses et de pièces raffinées, conçues pour affirmer une présence inoubliable.</p>
        <?php if ($instagram || $tiktok || $facebook): ?>
          <div class="footer-socials">
            <?php if ($instagram): ?>
              <a href="https://instagram.com/<?= e(trim($instagram, '@')) ?>" target="_blank" rel="noopener" aria-label="Instagram">Instagram</a>
            <?php endif; ?>
            <?php if ($tiktok): ?>
              <a href="https://tiktok.com/@<?= e(trim($tiktok, '@')) ?>" target="_blank" rel="noopener" aria-label="TikTok">TikTok</a>
            <?php endif; ?>
            <?php if ($facebook): ?>
              <a href="https://facebook.com/<?= e($facebook) ?>" target="_blank" rel="noopener" aria-label="Facebook">Facebook</a>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Colonne 2: Navigation rapide -->
      <div class="footer-col">
        <h5 class="footer-heading">Collection</h5>
        <ul class="footer-links">
          <li><a href="index.php#parfums">Nos Parfums</a></li>
          <li><a href="index.php#vetements">Nos Vêtements</a></li>
          <li><a href="index.php#univers">Notre Philosophie</a></li>
          <li><a href="https://wa.me/<?= e($whatsapp) ?>" target="_blank" rel="noopener">Conseil personnalisé</a></li>
        </ul>
      </div>

      <!-- Colonne 3: Service client & Commandes -->
      <div class="footer-col">
        <h5 class="footer-heading">Service Client</h5>
        <ul class="footer-info">
          <li><strong>WhatsApp direct :</strong> +<?= e($whatsapp) ?></li>
          <li><strong>Zone :</strong> <?= e($zone) ?></li>
          <li><strong>Minimum commande :</strong> <?= $minCommande ?> flacons</li>
          <?php if ($adresse): ?>
            <li><strong>Atelier :</strong> <?= e($adresse) ?></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>

    <div class="footer-bottom container">
      <p>&copy; <?= date('Y') ?> <?= e($nomMarque) ?>. Tous droits réservés.</p>
      <a href="Admin/login.php" class="admin-link">Espace Collaborateur</a>
    </div>
  </footer>

  <!-- Scripts -->
  <script src="assets/js/script.js"></script>
</body>
</html>
