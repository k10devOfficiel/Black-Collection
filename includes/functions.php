<?php
// =====================================================================
//  BLACK COLLECTION - Fonctions utilitaires
// =====================================================================

/**
 * Échappe une chaîne pour un affichage HTML sécurisé contre les failles XSS.
 */
function e(?string $valeur): string
{
    return htmlspecialchars((string) $valeur, ENT_QUOTES, 'UTF-8');
}

/**
 * Formate un montant en Franc CFA (XOF).
 * Ex: 35000 -> "35 000 FCFA"
 */
function prix(int|float $montant): string
{
    return number_format((float) $montant, 0, ',', ' ') . ' FCFA';
}

/**
 * Récupère un paramètre du site depuis la table `parametres`.
 */
function get_param(string $cle, string $defaut = ''): string
{
    static $cache = null;

    if ($cache === null) {
        try {
            $stmt = db()->query('SELECT cle, valeur FROM parametres');
            $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            $cache = is_array($rows) ? $rows : [];
        } catch (Throwable $e) {
            $cache = [];
        }
    }

    return $cache[$cle] ?? $defaut;
}

/**
 * Met à jour ou insère un paramètre dans la table `parametres`.
 */
function set_param(string $cle, ?string $valeur): bool
{
    try {
        $st = db()->prepare('INSERT INTO parametres (cle, valeur) VALUES (:k, :v) 
                             ON DUPLICATE KEY UPDATE valeur = VALUES(valeur)');
        return $st->execute([':k' => $cle, ':v' => $valeur]);
    } catch (Throwable $e) {
        return false;
    }
}

/**
 * Génère un slug propre pour les URL à partir d'un texte.
 * Ex: "Black Opium 50ml" -> "black-opium-50ml"
 */
function slugify(string $texte): string
{
    $texte = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $texte) ?: strtolower($texte);
    $texte = preg_replace('/[^a-z0-9]+/i', '-', $texte);
    $texte = trim($texte, '-');
    return $texte ?: 'item-' . time();
}

/**
 * Redirige vers une URL et stoppe le script.
 */
function rediriger(string $url): void
{
    header('Location: ' . $url);
    exit;
}
