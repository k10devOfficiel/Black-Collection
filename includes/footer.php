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
$logo        = get_param('logo');
?>
  <!-- Tiroir Latéral du Panier (Cart Drawer) -->
  <div class="cart-drawer-overlay" id="cartOverlay"></div>
  <aside class="cart-drawer" id="cartDrawer" data-step="cart" aria-label="Votre sélection">
    <div class="cart-header">
      <div class="cart-header-title">
        <h3>Votre Sélection</h3>
        <span class="cart-subtitle" id="cartSummaryText">0 article</span>
      </div>
      <button type="button" class="close-drawer-btn" id="closeCartBtn" aria-label="Fermer le panier">&times;</button>
    </div>

    <!-- ÉTAPE 1 : panier -->
    <div class="drawer-step step-cart">
      <!-- Alert minimum de commande -->
      <div class="cart-notice">
        <span class="gold-dot">&bull;</span>
        <span>Minimum requis : <strong><?= $minCommande ?> flacons de parfum</strong> (les vêtements n'ont pas de minimum).</span>
      </div>

      <div class="cart-items" id="cartItemsContainer">
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
        <p class="cart-min-warning" id="cartMinWarning"></p>
        <button type="button" class="btn-wa-order" id="btnCheckout" data-min="<?= $minCommande ?>">
          Continuer &rarr; mes coordonnées
        </button>
        <span class="cart-disclaimer">Paiement à la livraison ou par Wave à la confirmation.</span>
      </div>
    </div>

    <!-- ÉTAPE 2 : coordonnées client -->
    <div class="drawer-step step-checkout">
      <form id="checkoutForm" class="checkout-form" novalidate autocomplete="on">
        <button type="button" class="back-link" id="btnBackToCart">&larr; Retour à ma sélection</button>
        <h4 class="checkout-title">Vos coordonnées</h4>
        <p class="checkout-sub">Pour que nous puissions vous livrer et vous recontacter.</p>

        <div class="checkout-errors" id="checkoutErrors" role="alert" hidden></div>

        <div class="field">
          <label for="f_nom">Nom &amp; prénoms *</label>
          <input type="text" id="f_nom" name="nom" maxlength="150" autocomplete="name" placeholder="Ex : Kouassi Aya Marie">
          <span class="field-error" data-for="nom"></span>
        </div>

        <div class="field">
          <label for="f_tel">Numéro de contact (WhatsApp de préférence) *</label>
          <input type="tel" id="f_tel" name="telephone" maxlength="20" autocomplete="tel" inputmode="tel" placeholder="Ex : 05 54 97 15 92">
          <span class="field-error" data-for="telephone"></span>
        </div>

        <div class="field">
          <label for="f_adresse">Lieu de livraison (<?= e($zone) ?>) *</label>
          <input type="text" id="f_adresse" name="adresse" maxlength="255" autocomplete="street-address" placeholder="Commune, quartier, point de repère">
          <span class="field-error" data-for="adresse"></span>
        </div>

        <div class="field">
          <span class="field-label">Paiement souhaité *</span>
          <div class="pay-options">
            <label class="pay-opt"><input type="radio" name="paiement" value="wave"><span>Wave</span></label>
            <label class="pay-opt"><input type="radio" name="paiement" value="especes"><span>Espèces à la livraison</span></label>
          </div>
          <span class="field-error" data-for="paiement"></span>
        </div>

        <div class="field">
          <label for="f_note">Note (facultatif)</label>
          <textarea id="f_note" name="note" rows="2" maxlength="500" placeholder="Horaire souhaité, précision d'adresse..."></textarea>
        </div>

        <!-- Champ piège anti-robots : doit rester vide -->
        <div class="hp-field" aria-hidden="true">
          <label>Ne pas remplir <input type="text" name="site_web" tabindex="-1" autocomplete="off"></label>
        </div>

        <div class="checkout-total"><span>Total</span><strong id="checkoutTotal">0 FCFA</strong></div>
        <button type="submit" class="btn-wa-order" id="btnSendOrder">Envoyer ma commande</button>
      </form>
    </div>

    <!-- ÉTAPE 3 : confirmation -->
    <div class="drawer-step step-done">
      <div class="done-box">
        <div class="done-check">&#10003;</div>
        <h4>Commande enregistrée</h4>
        <p>Référence : <strong id="successRef">-</strong></p>
        <p class="done-hint">WhatsApp s'est ouvert avec votre commande. Si ce n'est pas le cas, utilisez le bouton ci-dessous, puis envoyez le message pour la valider.</p>
        <a class="btn-wa-order" id="waSendLink" href="#" target="_blank" rel="noopener">Ouvrir WhatsApp</a>
        <button type="button" class="btn-outline-gold" id="btnCloseSuccess">Fermer</button>
      </div>
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
        <?php if ($logo !== ''): ?>
          <img class="footer-logo-img" src="<?= e($logo) ?>" alt="<?= e($nomMarque) ?>">
        <?php else: ?>
          <h4 class="footer-logo"><?= e($nomMarque) ?></h4>
        <?php endif; ?>
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
          <li><strong>Minimum parfums :</strong> <?= $minCommande ?> flacons (aucun sur vêtements)</li>
          <?php if ($adresse): ?>
            <li><strong>Atelier :</strong> <?= e($adresse) ?></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>

    <div class="footer-bottom container">
      <p>&copy; <?= date('Y') ?> <?= e($nomMarque) ?>. Tous droits réservés.</p>
      <span class="admin-link">Développé par k10dev</span>
    </div>
  </footer>

  <!-- Scripts -->
  <script src="assets/js/script.js"></script>
</body>
</html>