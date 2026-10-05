<?php
// =====================================================================
//  BLACK COLLECTION - Suppression d'un produit
// =====================================================================
require_once __DIR__ . '/includes/auth.php';
exiger_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (csrf_valide()) {
        $id = (int) ($_POST['id'] ?? 0);

        if ($id > 0) {
            // Récupérer les images associées pour les supprimer du disque
            $stImages = db()->prepare('SELECT chemin FROM produit_images WHERE produit_id = ?');
            $stImages->execute([$id]);
            $images = $stImages->fetchAll(PDO::FETCH_COLUMN);

            $dossierRacine = dirname(__DIR__) . '/';
            foreach ($images as $imgChemin) {
                $fichier = $dossierRacine . $imgChemin;
                if (file_exists($fichier) && is_file($fichier)) {
                    @unlink($fichier);
                }
            }

            // Suppression en base (la contrainte ON DELETE CASCADE nettoie produit_images)
            $st = db()->prepare('DELETE FROM produits WHERE id = ?');
            $st->execute([$id]);
        }
    }
}

rediriger('produits.php?msg=supprime');
