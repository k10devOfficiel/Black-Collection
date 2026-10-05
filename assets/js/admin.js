/* =====================================================================
   BLACK COLLECTION - JavaScript de l'administration
   1. Menu mobile   2. Filtre de la liste des derniers produits
   ===================================================================== */
(function () {
  'use strict';

  /* ---------- 1. Menu mobile (sidebar) ---------- */
  var body = document.body;
  function ouvrirMenu()  { body.classList.add('menu-ouvert'); }
  function fermerMenu()  { body.classList.remove('menu-ouvert'); }

  var btnOuvrir  = document.getElementById('menu-open');
  var btnFermer  = document.getElementById('menu-close');
  var overlay    = document.getElementById('overlay');

  if (btnOuvrir) btnOuvrir.addEventListener('click', ouvrirMenu);
  if (btnFermer) btnFermer.addEventListener('click', fermerMenu);
  if (overlay)   overlay.addEventListener('click', fermerMenu);
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') fermerMenu();
  });

  /* ---------- 2. Filtre des produits (tableau de bord) ---------- */
  var champ     = document.getElementById('filtre');
  var btnRupt   = document.getElementById('filtre-rupture');
  var liste     = document.getElementById('liste-prod');
  var aucun     = document.getElementById('aucun-resultat');

  if (liste && champ) {
    var lignes = Array.prototype.slice.call(liste.querySelectorAll('.row-prod'));
    var seulementRupture = false;

    function filtrer() {
      var q = champ.value.trim().toLowerCase();
      var visibles = 0;
      lignes.forEach(function (li) {
        var okNom  = q === '' || (li.getAttribute('data-nom') || '').indexOf(q) !== -1;
        var okRupt = !seulementRupture || li.getAttribute('data-rupture') === '1';
        li.hidden = !(okNom && okRupt);
        if (!li.hidden) visibles++;
      });
      if (aucun) aucun.hidden = visibles !== 0;
    }

    champ.addEventListener('input', filtrer);
    if (btnRupt) {
      btnRupt.addEventListener('click', function () {
        seulementRupture = !seulementRupture;
        btnRupt.setAttribute('aria-pressed', seulementRupture ? 'true' : 'false');
        filtrer();
      });
    }
  }
})();