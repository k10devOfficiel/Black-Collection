/* =====================================================================
   BLACK COLLECTION - Script public
   Panier -> formulaire client -> enregistrement (commande.php) -> WhatsApp
   ===================================================================== */
(function () {
  'use strict';

  var STORAGE_KEY = 'bc_cart_items';
  var CLIENT_KEY  = 'bc_client_info';

  /* ---------- Utilitaires ---------- */
  function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  function formatPrix(montant) {
    return String(montant).replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' FCFA';
  }

  /* ---------- 1. PANIER (localStorage) ---------- */
  function getCart() {
    try {
      var c = JSON.parse(localStorage.getItem(STORAGE_KEY));
      return Array.isArray(c) ? c : [];
    } catch (e) {
      return [];
    }
  }

  function saveCart(cart) {
    try { localStorage.setItem(STORAGE_KEY, JSON.stringify(cart)); } catch (e) { /* stockage plein ou bloqué */ }
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
    setStep('cart');
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
    saveCart(getCart().filter(function (p) { return p.id !== id; }));
  }

  function countParfums(cart) {
    return cart.filter(function (p) { return (p.type || 'parfum') === 'parfum'; })
               .reduce(function (acc, p) { return acc + p.qte; }, 0);
  }

  function cartTotal(cart) {
    return cart.reduce(function (acc, p) { return acc + (p.prix * p.qte); }, 0);
  }

  /* ---------- 2. TIROIR PANIER : étapes (cart / checkout / done) ---------- */
  var drawerEl         = document.getElementById('cartDrawer');
  var cartCountEl      = document.getElementById('cartCount');
  var cartOverlayEl    = document.getElementById('cartOverlay');
  var cartTriggerEl    = document.getElementById('cartTrigger');
  var closeCartBtnEl   = document.getElementById('closeCartBtn');
  var cartItemsBox     = document.getElementById('cartItemsContainer');
  var cartSubtotalEl   = document.getElementById('cartSubtotal');
  var cartSummaryText  = document.getElementById('cartSummaryText');
  var cartMinWarningEl = document.getElementById('cartMinWarning');
  var btnCheckout      = document.getElementById('btnCheckout');
  var checkoutTotalEl  = document.getElementById('checkoutTotal');

  function setStep(step) {
    if (drawerEl) drawerEl.setAttribute('data-step', step);
  }

  function openCart() {
    document.body.classList.add('cart-open');
  }

  function closeCart() {
    document.body.classList.remove('cart-open');
    setTimeout(function () { setStep('cart'); }, 380);
  }

  if (cartTriggerEl)  cartTriggerEl.addEventListener('click', function () { setStep('cart'); openCart(); });
  if (closeCartBtnEl) closeCartBtnEl.addEventListener('click', closeCart);
  if (cartOverlayEl)  cartOverlayEl.addEventListener('click', closeCart);

  function renderCart() {
    var cart = getCart();
    var totalArticles = 0;
    cart.forEach(function (it) { totalArticles += it.qte; });
    var sousTotal = cartTotal(cart);

    if (cartCountEl)     cartCountEl.textContent = totalArticles;
    if (cartSummaryText) cartSummaryText.textContent = totalArticles + (totalArticles > 1 ? ' articles' : ' article');
    if (cartSubtotalEl)  cartSubtotalEl.textContent = formatPrix(sousTotal);
    if (checkoutTotalEl) checkoutTotalEl.textContent = formatPrix(sousTotal);

    if (!cartItemsBox) return;

    if (cart.length === 0) {
      cartItemsBox.innerHTML = '<div class="empty-cart-state"><p>Votre sélection est vide.</p><button type="button" class="btn-outline-gold" id="emptyCloseBtn">Découvrir le catalogue</button></div>';
      var emptyBtn = document.getElementById('emptyCloseBtn');
      if (emptyBtn) emptyBtn.addEventListener('click', closeCart);
      if (cartMinWarningEl) cartMinWarningEl.style.display = 'none';
      return;
    }

    var html = '';
    cart.forEach(function (item) {
      var photoHtml = item.photo
        ? '<img src="' + esc(item.photo) + '" alt="' + esc(item.nom) + '">'
        : '<div style="width:100%;height:100%;display:grid;place-items:center;color:#666;font-size:10px;">BC</div>';

      html += '<div class="cart-item-row">';
      html += '  <div class="cart-item-thumb">' + photoHtml + '</div>';
      html += '  <div class="cart-item-info">';
      html += '    <h4 class="cart-item-title">' + esc(item.nom) + '</h4>';
      html += '    <div class="cart-item-meta">' + formatPrix(item.prix) + (item.contenance ? ' &bull; ' + esc(item.contenance) : '') + '</div>';
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

    cartItemsBox.querySelectorAll('.js-minus').forEach(function (btn) {
      btn.addEventListener('click', function () { updateQty(parseInt(this.getAttribute('data-id'), 10), -1); });
    });
    cartItemsBox.querySelectorAll('.js-plus').forEach(function (btn) {
      btn.addEventListener('click', function () { updateQty(parseInt(this.getAttribute('data-id'), 10), 1); });
    });
    cartItemsBox.querySelectorAll('.js-remove').forEach(function (btn) {
      btn.addEventListener('click', function () { removeFromCart(parseInt(this.getAttribute('data-id'), 10)); });
    });

    // Minimum requis : concerne uniquement les parfums
    var totalParfums = countParfums(cart);
    var minRequis = btnCheckout ? parseInt(btnCheckout.getAttribute('data-min'), 10) || 5 : 5;
    if (cartMinWarningEl) {
      if (totalParfums > 0 && totalParfums < minRequis) {
        cartMinWarningEl.textContent = 'Livraison à partir de ' + minRequis + ' parfums : encore ' + (minRequis - totalParfums) + ' parfum(s) pour atteindre le minimum (aucun minimum pour les vêtements).';
        cartMinWarningEl.style.display = 'block';
      } else {
        cartMinWarningEl.style.display = 'none';
      }
    }
  }

  /* ---------- 3. FORMULAIRE CLIENT ---------- */
  var formEl      = document.getElementById('checkoutForm');
  var errorsBox   = document.getElementById('checkoutErrors');
  var btnSend     = document.getElementById('btnSendOrder');
  var btnBack     = document.getElementById('btnBackToCart');
  var successRef  = document.getElementById('successRef');
  var waSendLink  = document.getElementById('waSendLink');
  var btnCloseOk  = document.getElementById('btnCloseSuccess');

  function loadClientInfo() {
    try { return JSON.parse(localStorage.getItem(CLIENT_KEY)) || {}; } catch (e) { return {}; }
  }

  function saveClientInfo(d) {
    try {
      localStorage.setItem(CLIENT_KEY, JSON.stringify({
        nom: d.nom, telephone: d.telephone, adresse: d.adresse, paiement: d.paiement
      }));
    } catch (e) { /* ignoré */ }
  }

  function prefillForm() {
    if (!formEl) return;
    var info = loadClientInfo();
    if (info.nom && !formEl.nom.value)               formEl.nom.value = info.nom;
    if (info.telephone && !formEl.telephone.value)   formEl.telephone.value = info.telephone;
    if (info.adresse && !formEl.adresse.value)       formEl.adresse.value = info.adresse;
    if (info.paiement) {
      var radio = formEl.querySelector('input[name="paiement"][value="' + info.paiement + '"]');
      if (radio) radio.checked = true;
    }
  }

  function clearErrors() {
    if (errorsBox) { errorsBox.hidden = true; errorsBox.textContent = ''; }
    if (formEl) {
      formEl.querySelectorAll('.field-error').forEach(function (el) { el.textContent = ''; });
      formEl.querySelectorAll('.has-error').forEach(function (el) { el.classList.remove('has-error'); });
    }
  }

  function showGeneralError(msg) {
    if (!errorsBox) return;
    errorsBox.textContent = msg;
    errorsBox.hidden = false;
  }

  function showFieldErrors(erreurs) {
    var first = null;
    Object.keys(erreurs).forEach(function (key) {
      var span = formEl.querySelector('.field-error[data-for="' + key + '"]');
      if (span) {
        span.textContent = erreurs[key];
        if (span.parentNode) span.parentNode.classList.add('has-error');
        if (!first) first = span.parentNode;
      } else {
        showGeneralError(erreurs[key]);
      }
    });
    if (first && first.scrollIntoView) first.scrollIntoView({ block: 'center', behavior: 'smooth' });
  }

  function readForm() {
    var radio = formEl.querySelector('input[name="paiement"]:checked');
    return {
      nom:       formEl.nom.value.trim(),
      telephone: formEl.telephone.value.trim(),
      adresse:   formEl.adresse.value.trim(),
      paiement:  radio ? radio.value : '',
      note:      formEl.note.value.trim(),
      site_web:  formEl.site_web.value
    };
  }

  function validateClient(d) {
    var err = {};
    if (d.nom.length < 2) err.nom = 'Indiquez votre nom et prénoms.';
    var digits = d.telephone.replace(/\D+/g, '');
    if (digits.indexOf('00225') === 0) digits = digits.slice(2);
    if (!(digits.length === 10 || (digits.length === 13 && digits.indexOf('225') === 0))) {
      err.telephone = 'Numéro invalide : saisissez 10 chiffres (ex : 05 54 97 15 92).';
    }
    if (d.adresse.length < 5) err.adresse = 'Indiquez votre commune, quartier et un point de repère.';
    if (!d.paiement) err.paiement = 'Choisissez un mode de paiement.';
    return Object.keys(err).length ? err : null;
  }

  // Étape 1 -> 2
  if (btnCheckout) {
    btnCheckout.addEventListener('click', function () {
      var cart = getCart();
      if (cart.length === 0) {
        alert('Votre sélection est vide. Ajoutez au moins un produit.');
        return;
      }
      var totalParfums = countParfums(cart);
      var minRequis = parseInt(this.getAttribute('data-min'), 10) || 5;
      if (totalParfums > 0 && totalParfums < minRequis) {
        if (!confirm('La livraison est disponible à partir de ' + minRequis + ' parfums (actuellement : ' + totalParfums + '). Les vêtements ne sont pas soumis à ce minimum.\n\nSouhaitez-vous continuer quand même ?')) {
          return;
        }
      }
      clearErrors();
      prefillForm();
      renderCart();
      setStep('checkout');
      if (formEl && formEl.nom && !formEl.nom.value) formEl.nom.focus();
    });
  }

  if (btnBack) btnBack.addEventListener('click', function () { clearErrors(); setStep('cart'); });
  if (btnCloseOk) btnCloseOk.addEventListener('click', closeCart);

  function setLoading(on) {
    if (!btnSend) return;
    btnSend.disabled = on;
    btnSend.textContent = on ? 'Enregistrement...' : 'Envoyer ma commande';
  }

  // Étape 2 -> 3 : enregistrement serveur puis WhatsApp
  if (formEl) {
    formEl.addEventListener('submit', function (e) {
      e.preventDefault();
      clearErrors();

      var cart = getCart();
      if (cart.length === 0) {
        showGeneralError('Votre sélection est vide.');
        return;
      }

      var data = readForm();
      var localErrors = validateClient(data);
      if (localErrors) {
        showFieldErrors(localErrors);
        return;
      }

      var payload = {
        nom: data.nom, telephone: data.telephone, adresse: data.adresse,
        paiement: data.paiement, note: data.note, site_web: data.site_web,
        items: cart.map(function (it) { return { id: it.id, qte: it.qte }; })
      };

      setLoading(true);

      fetch('commande.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify(payload),
        credentials: 'same-origin'
      })
        .then(function (r) {
          return r.json().then(function (j) { return j; }, function () {
            return { ok: false, message: 'Réponse inattendue du serveur. Réessayez dans un instant.' };
          });
        })
        .then(function (res) {
          if (res.ok) {
            saveClientInfo(data);
            try { localStorage.removeItem(STORAGE_KEY); } catch (er) { /* ignoré */ }
            renderCart();

            if (successRef) successRef.textContent = res.reference;
            if (waSendLink) waSendLink.setAttribute('href', res.wa_url);
            setStep('done');
            try { window.open(res.wa_url, '_blank'); } catch (er) { /* bouton de secours affiché */ }
            return;
          }

          if (res.indisponibles && res.indisponibles.length) {
            var retirer = res.indisponibles.map(function (n) { return parseInt(n, 10); });
            saveCart(getCart().filter(function (p) { return retirer.indexOf(p.id) === -1; }));
            setStep('cart');
            alert(res.message || 'Certains articles ne sont plus disponibles.');
            return;
          }

          if (res.erreurs) showFieldErrors(res.erreurs);
          showGeneralError(res.message || 'La commande n\'a pas pu être enregistrée.');
        })
        .catch(function () {
          showGeneralError('Connexion impossible. Vérifiez votre réseau et réessayez.');
        })
        .then(function () { setLoading(false); });
    });
  }

  /* ---------- 4. AJOUT AU PANIER ---------- */
  document.querySelectorAll('.js-add-to-cart').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      addToCart({
        id:         parseInt(this.getAttribute('data-id'), 10),
        type:       this.getAttribute('data-type') || 'parfum',
        nom:        this.getAttribute('data-nom'),
        prix:       parseInt(this.getAttribute('data-prix'), 10),
        photo:      this.getAttribute('data-photo'),
        contenance: this.getAttribute('data-contenance')
      });
    });
  });

  /* ---------- 5. FILTRES DU CATALOGUE ---------- */
  var filterBtns   = document.querySelectorAll('#catalogFilters .tab-btn');
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

  // Liens du menu / pied de page : "Vêtements" et "Parfums" appliquent le bon filtre
  function goToCatalog(filter) {
    var btn = document.querySelector('#catalogFilters [data-filter="' + filter + '"]');
    if (btn) btn.click();
    var target = document.getElementById('parfums');
    if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    document.body.classList.remove('mobile-nav-open');
  }
  [['vetements', 'vetement'], ['parfums', 'parfum']].forEach(function (pair) {
    document.querySelectorAll('a[href$="#' + pair[0] + '"]').forEach(function (a) {
      a.addEventListener('click', function (e) {
        if (!document.getElementById('catalogFilters')) return;
        e.preventDefault();
        goToCatalog(pair[1]);
      });
    });
  });
  if (location.hash === '#vetements') goToCatalog('vetement');

  /* ---------- 6. APERÇU RAPIDE ---------- */
  var modalOverlay  = document.getElementById('productModalOverlay');
  var modalContent  = document.getElementById('modalProductContent');
  var closeModalBtn = document.getElementById('closeModalBtn');

  function openModal(card) {
    var type       = card.getAttribute('data-type') || 'parfum';
    var nom        = card.getAttribute('data-nom');
    var prix       = parseInt(card.getAttribute('data-prix'), 10);
    var contenance = card.getAttribute('data-contenance');
    var photo      = card.getAttribute('data-photo');
    var notes      = card.getAttribute('data-notes');
    var desc       = card.getAttribute('data-desc');
    var id         = parseInt(card.getAttribute('data-id'), 10);
    var rupture    = card.getAttribute('data-rupture') === '1';

    var photoHtml = photo
      ? '<img src="' + esc(photo) + '" alt="' + esc(nom) + '">'
      : '<div style="color:var(--gold);font-family:var(--serif);font-size:24px;">BLACK COLLECTION</div>';

    var html = '<div class="modal-grid">';
    html += '  <div class="modal-img-box">' + photoHtml + '</div>';
    html += '  <div class="modal-details">';
    html += '    <span class="card-category" style="margin-bottom:8px;">' + esc(contenance ? contenance : 'Haute Parfumerie') + '</span>';
    html += '    <h2 style="font-family:var(--serif);font-size:28px;color:#fff;margin-bottom:12px;">' + esc(nom) + '</h2>';
    html += '    <div style="font-family:var(--serif);font-size:22px;color:var(--gold);margin-bottom:20px;">' + formatPrix(prix) + '</div>';

    if (notes && type === 'parfum') {
      html += '  <div style="margin-bottom:16px;background:#181818;padding:12px;border-left:2px solid var(--gold);">';
      html += '    <strong style="display:block;font-size:11px;font-family:var(--caps);letter-spacing:.2em;color:var(--gold);margin-bottom:4px;text-transform:uppercase;">Accords Olfactifs</strong>';
      html += '    <p style="font-size:13px;color:#ccc;margin:0;">' + esc(notes) + '</p>';
      html += '  </div>';
    }
    if (desc) {
      html += '  <p style="font-size:14px;color:var(--txt-sub);line-height:1.7;margin-bottom:24px;">' + esc(desc) + '</p>';
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
        addToCart({ id: id, type: type, nom: nom, prix: prix, photo: photo, contenance: contenance });
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
    closeModalBtn.addEventListener('click', function () { document.body.classList.remove('modal-open'); });
  }
  if (modalOverlay) {
    modalOverlay.addEventListener('click', function (e) {
      if (e.target === modalOverlay) document.body.classList.remove('modal-open');
    });
  }

  /* ---------- 7. MENU MOBILE ---------- */
  var navToggle = document.getElementById('navToggle');
  if (navToggle) {
    navToggle.addEventListener('click', function () { document.body.classList.toggle('mobile-nav-open'); });
  }

  // Initialisation
  renderCart();

})();