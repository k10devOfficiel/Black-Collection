/* =====================================================================
   BLACK COLLECTION - Script Public / Panier & Interaction WhatsApp
   ===================================================================== */
(function () {
  'use strict';

  // Clef de stockage local
  var STORAGE_KEY = 'bc_cart_items';

  /* ---------- 1. GESTION DU PANIER (LOCALSTORAGE) ---------- */
  function getCart() {
    try {
      return JSON.parse(localStorage.getItem(STORAGE_KEY)) || [];
    } catch (e) {
      return [];
    }
  }

  function saveCart(cart) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(cart));
    renderCart();
  }

  function addToCart(item) {
    var cart = getCart();
    var existing = cart.find(function (p) { return p.id === item.id; });
    if (existing) {
      existing.qte += 1;
    } else {
      item.qte = 1;
      cart.push(item);
    }
    saveCart(cart);
    openCart();
  }

  function updateQty(id, delta) {
    var cart = getCart();
    var item = cart.find(function (p) { return p.id === id; });
    if (item) {
      item.qte += delta;
      if (item.qte <= 0) {
        cart = cart.filter(function (p) { return p.id !== id; });
      }
    }
    saveCart(cart);
  }

  function removeFromCart(id) {
    var cart = getCart().filter(function (p) { return p.id !== id; });
    saveCart(cart);
  }

  function formatPrix(montant) {
    return montant.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' FCFA';
  }

  /* ---------- 2. RENDU VISUEL DU PANIER ---------- */
  var cartCountEl       = document.getElementById('cartCount');
  var cartDrawerEl      = document.getElementById('cartDrawer');
  var cartOverlayEl     = document.getElementById('cartOverlay');
  var cartTriggerEl     = document.getElementById('cartTrigger');
  var closeCartBtnEl    = document.getElementById('closeCartBtn');
  var cartItemsBox      = document.getElementById('cartItemsContainer');
  var cartSubtotalEl    = document.getElementById('cartSubtotal');
  var cartSummaryText   = document.getElementById('cartSummaryText');
  var cartMinWarningEl  = document.getElementById('cartMinWarning');
  var btnWhatsappOrder  = document.getElementById('btnWhatsappOrder');

  function openCart() {
    document.body.classList.add('cart-open');
  }

  function closeCart() {
    document.body.classList.remove('cart-open');
  }

  if (cartTriggerEl) cartTriggerEl.addEventListener('click', openCart);
  if (closeCartBtnEl) closeCartBtnEl.addEventListener('click', closeCart);
  if (cartOverlayEl) cartOverlayEl.addEventListener('click', closeCart);

  function renderCart() {
    var cart = getCart();
    var totalArticles = 0;
    var sousTotal = 0;

    cart.forEach(function (it) {
      totalArticles += it.qte;
      sousTotal += (it.prix * it.qte);
    });

    if (cartCountEl) cartCountEl.textContent = totalArticles;
    if (cartSummaryText) cartSummaryText.textContent = totalArticles + (totalArticles > 1 ? ' articles' : ' article');
    if (cartSubtotalEl) cartSubtotalEl.textContent = formatPrix(sousTotal);

    if (!cartItemsBox) return;

    if (cart.length === 0) {
      cartItemsBox.innerHTML = '<div class="empty-cart-state"><p>Votre sélection est vide.</p><button type="button" class="btn-outline-gold" onclick="document.body.classList.remove(\'cart-open\');">Découvrir le catalogue</button></div>';
      if (cartMinWarningEl) cartMinWarningEl.style.display = 'none';
      return;
    }

    var html = '';
    cart.forEach(function (item) {
      var photoHtml = item.photo 
        ? '<img src="' + item.photo + '" alt="' + item.nom + '">' 
        : '<div style="width:100%;height:100%;display:grid;place-items:center;color:#666;font-size:10px;">BC</div>';

      html += '<div class="cart-item-row">';
      html += '  <div class="cart-item-thumb">' + photoHtml + '</div>';
      html += '  <div class="cart-item-info">';
      html += '    <h4 class="cart-item-title">' + item.nom + '</h4>';
      html += '    <div class="cart-item-meta">' + formatPrix(item.prix) + (item.contenance ? ' &bull; ' + item.contenance : '') + '</div>';
      html += '    <div class="cart-item-controls">';
      html += '      <button type="button" class="qty-btn js-minus" data-id="' + item.id + '">-</button>';
      html += '      <span class="qty-display">' + item.qte + '</span>';
      html += '      <button type="button" class="qty-btn js-plus" data-id="' + item.id + '">+</button>';
      html += '    </div>';
      html += '  </div>';
      html += '  <button type="button" class="cart-item-remove js-remove" data-id="' + item.id + '" title="Supprimer">&times;</button>';
      html += '</div>';
    });

    cartItemsBox.innerHTML = html;

    // Événements sur les boutons de quantité
    cartItemsBox.querySelectorAll('.js-minus').forEach(function (btn) {
      btn.addEventListener('click', function () {
        updateQty(parseInt(this.getAttribute('data-id'), 10), -1);
      });
    });
    cartItemsBox.querySelectorAll('.js-plus').forEach(function (btn) {
      btn.addEventListener('click', function () {
        updateQty(parseInt(this.getAttribute('data-id'), 10), 1);
      });
    });
    cartItemsBox.querySelectorAll('.js-remove').forEach(function (btn) {
      btn.addEventListener('click', function () {
        removeFromCart(parseInt(this.getAttribute('data-id'), 10));
      });
    });

    // Vérification du minimum requis
    var minRequis = btnWhatsappOrder ? parseInt(btnWhatsappOrder.getAttribute('data-min'), 10) || 1 : 1;
    if (cartMinWarningEl) {
      if (totalArticles < minRequis && totalArticles > 0) {
        cartMinWarningEl.textContent = 'Encore ' + (minRequis - totalArticles) + ' article(s) pour atteindre le minimum requis de ' + minRequis + ' flacons.';
        cartMinWarningEl.style.display = 'block';
      } else {
        cartMinWarningEl.style.display = 'none';
      }
    }
  }

  /* ---------- 3. COMMANDE WHATSAPP FORMATÉE ---------- */
  if (btnWhatsappOrder) {
    btnWhatsappOrder.addEventListener('click', function () {
      var cart = getCart();
      if (cart.length === 0) {
        alert('Votre sélection est vide. Ajoutez au moins un produit.');
        return;
      }

      var totalArticles = cart.reduce(function (acc, p) { return acc + p.qte; }, 0);
      var minRequis = parseInt(this.getAttribute('data-min'), 10) || 1;

      if (totalArticles < minRequis) {
        if (!confirm('Le minimum recommandé pour la livraison est de ' + minRequis + ' flacons (actuel: ' + totalArticles + '). Souhaitez-vous continuer quand même ?')) {
          return;
        }
      }

      var waNumber = this.getAttribute('data-wa') || '2250554971592';
      var sousTotal = cart.reduce(function (acc, p) { return acc + (p.prix * p.qte); }, 0);

      var texte = "Bonjour Black Collection,\n";
      texte += "Je souhaite commander les articles suivants :\n\n";

      cart.forEach(function (it) {
        var format = it.contenance ? " (" + it.contenance + ")" : "";
        texte += "• " + it.qte + "x " + it.nom + format + " — " + formatPrix(it.prix * it.qte) + "\n";
      });

      texte += "\n📦 Total articles : " + totalArticles;
      texte += "\n💰 Total commande : " + formatPrix(sousTotal);
      texte += "\n\n--- Coordonnées Client ---";
      texte += "\n👤 Nom & Prénoms : ";
      texte += "\n📍 Lieu de livraison (Abidjan) : ";
      texte += "\n📱 Numéro de contact : ";
      texte += "\n💳 Paiement souhaité : Wave / Espèces";

      var url = "https://wa.me/" + waNumber + "?text=" + encodeURIComponent(texte);
      window.open(url, '_blank');
    });
  }

  /* ---------- 4. ÉCOUTEURS D'AJOUT AU PANIER ---------- */
  document.querySelectorAll('.js-add-to-cart').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      var id         = parseInt(this.getAttribute('data-id'), 10);
      var nom        = this.getAttribute('data-nom');
      var prix       = parseInt(this.getAttribute('data-prix'), 10);
      var photo      = this.getAttribute('data-photo');
      var contenance = this.getAttribute('data-contenance');

      addToCart({
        id: id,
        nom: nom,
        prix: prix,
        photo: photo,
        contenance: contenance
      });
    });
  });

  /* ---------- 5. FILTRES DU CATALOGUE ---------- */
  var filterBtns = document.querySelectorAll('#catalogFilters .tab-btn');
  var productCards = document.querySelectorAll('#mainProductGrid .product-card');

  filterBtns.forEach(function (btn) {
    btn.addEventListener('click', function () {
      filterBtns.forEach(function (b) { b.classList.remove('active'); });
      this.classList.add('active');

      var filter = this.getAttribute('data-filter');

      productCards.forEach(function (card) {
        if (filter === 'all') {
          card.style.display = '';
        } else if (filter === 'parfum' || filter === 'vetement') {
          card.style.display = (card.getAttribute('data-type') === filter) ? '' : 'none';
        } else if (filter.indexOf('cat-') === 0) {
          card.style.display = (card.getAttribute('data-category') === filter) ? '' : 'none';
        }
      });
    });
  });

  /* ---------- 6. MODAL APERÇU RAPIDE / NOTES OLFACTIVES ---------- */
  var modalOverlay   = document.getElementById('productModalOverlay');
  var modalContent   = document.getElementById('modalProductContent');
  var closeModalBtn  = document.getElementById('closeModalBtn');

  function openModal(card) {
    var nom        = card.getAttribute('data-nom');
    var prix       = parseInt(card.getAttribute('data-prix'), 10);
    var contenance = card.getAttribute('data-contenance');
    var photo      = card.getAttribute('data-photo');
    var notes      = card.getAttribute('data-notes');
    var desc       = card.getAttribute('data-desc');
    var id         = parseInt(card.getAttribute('data-id'), 10);
    var rupture    = card.getAttribute('data-rupture') === '1';

    var photoHtml = photo 
      ? '<img src="' + photo + '" alt="' + nom + '">'
      : '<div style="color:var(--gold);font-family:var(--serif);font-size:24px;">BLACK COLLECTION</div>';

    var html = '<div class="modal-grid">';
    html += '  <div class="modal-img-box">' + photoHtml + '</div>';
    html += '  <div class="modal-details">';
    html += '    <span class="card-category" style="margin-bottom:8px;">' + (contenance ? contenance : 'Haute Parfumerie') + '</span>';
    html += '    <h2 style="font-family:var(--serif);font-size:28px;color:#fff;margin-bottom:12px;">' + nom + '</h2>';
    html += '    <div style="font-family:var(--serif);font-size:22px;color:var(--gold);margin-bottom:20px;">' + formatPrix(prix) + '</div>';
    
    if (notes) {
      html += '  <div style="margin-bottom:16px;background:#181818;padding:12px;border-left:2px solid var(--gold);">';
      html += '    <strong style="display:block;font-size:11px;font-family:var(--caps);letter-spacing:.2em;color:var(--gold);margin-bottom:4px;text-transform:uppercase;">Accords Olfactifs</strong>';
      html += '    <p style="font-size:13px;color:#ccc;margin:0;">' + notes + '</p>';
      html += '  </div>';
    }

    if (desc) {
      html += '  <p style="font-size:14px;color:var(--txt-sub);line-height:1.7;margin-bottom:24px;">' + desc + '</p>';
    }

    if (rupture) {
      html += '  <button type="button" class="btn-card-disabled" style="padding:14px;" disabled>Actuellement en rupture de stock</button>';
    } else {
      html += '  <button type="button" class="btn-gold js-modal-add" style="width:100%;">Ajouter à ma sélection</button>';
    }

    html += '  </div>';
    html += '</div>';

    modalContent.innerHTML = html;
    document.body.classList.add('modal-open');

    var addBtn = modalContent.querySelector('.js-modal-add');
    if (addBtn) {
      addBtn.addEventListener('click', function () {
        addToCart({
          id: id,
          nom: nom,
          prix: prix,
          photo: photo,
          contenance: contenance
        });
        document.body.classList.remove('modal-open');
      });
    }
  }

  document.querySelectorAll('.js-quick-view').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var card = this.closest('.product-card');
      if (card) openModal(card);
    });
  });

  if (closeModalBtn) {
    closeModalBtn.addEventListener('click', function () {
      document.body.classList.remove('modal-open');
    });
  }
  if (modalOverlay) {
    modalOverlay.addEventListener('click', function (e) {
      if (e.target === modalOverlay) {
        document.body.classList.remove('modal-open');
      }
    });
  }

  /* ---------- 7. MENU MOBILE ---------- */
  var navToggle = document.getElementById('navToggle');
  if (navToggle) {
    navToggle.addEventListener('click', function () {
      document.body.classList.toggle('mobile-nav-open');
    });
  }

  // Initialisation du panier au chargement
  renderCart();

})();
