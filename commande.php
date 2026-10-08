<?php
// =====================================================================
//  BLACK COLLECTION - Enregistrement d'une commande (appelé par script.js)
//  Reçoit du JSON, enregistre client + commande, renvoie le lien WhatsApp.
//  À placer à la RACINE du site (à côté de index.php).
// =====================================================================
require_once __DIR__ . '/includes/db.php';

header('Content-Type: application/json; charset=utf-8');

function repondre(array $data, int $code = 200): void
{
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    repondre(['ok' => false, 'message' => 'Méthode non autorisée.'], 405);
}

$in = json_decode(file_get_contents('php://input'), true);
if (!is_array($in)) {
    repondre(['ok' => false, 'message' => 'Requête invalide.'], 400);
}

// --- Anti-spam : champ piège + limitation par session ---
if (!empty($in['site_web'])) {
    repondre(['ok' => false, 'message' => 'Requête refusée.'], 400);
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$now = time();
$_SESSION['cmd_essais'] = array_filter($_SESSION['cmd_essais'] ?? [], fn ($t) => $now - $t < 600);
if (count($_SESSION['cmd_essais']) >= 5) {
    repondre(['ok' => false, 'message' => 'Trop de commandes en peu de temps. Réessayez dans quelques minutes.'], 429);
}

// --- Validation des coordonnées ---
$nom      = trim((string) ($in['nom'] ?? ''));
$tel      = preg_replace('/\D+/', '', (string) ($in['telephone'] ?? ''));
$adresse  = trim((string) ($in['adresse'] ?? ''));
$paiement = ($in['paiement'] ?? '') === 'wave' ? 'wave' : (($in['paiement'] ?? '') === 'especes' ? 'especes' : '');
$note     = trim((string) ($in['note'] ?? ''));

if (strpos($tel, '00225') === 0) {
    $tel = substr($tel, 2);
}
if (strlen($tel) === 10) {
    $tel = '225' . $tel;
}

$erreurs = [];
if (mb_strlen($nom) < 2 || mb_strlen($nom) > 150) {
    $erreurs['nom'] = 'Indiquez votre nom et prénoms.';
}
if (!preg_match('/^225\d{10}$/', $tel)) {
    $erreurs['telephone'] = 'Numéro invalide : saisissez 10 chiffres (ex : 05 54 97 15 92).';
}
if (mb_strlen($adresse) < 5 || mb_strlen($adresse) > 255) {
    $erreurs['adresse'] = 'Indiquez votre commune, quartier et un point de repère.';
}
if ($paiement === '') {
    $erreurs['paiement'] = 'Choisissez un mode de paiement.';
}
if ($erreurs) {
    repondre(['ok' => false, 'message' => 'Veuillez corriger le formulaire.', 'erreurs' => $erreurs], 422);
}
$note = mb_substr($note, 0, 500);

// --- Panier : on ne fait JAMAIS confiance aux prix envoyés par le navigateur ---
$demande = [];
foreach (($in['items'] ?? []) as $it) {
    $pid = (int) ($it['id'] ?? 0);
    $qte = max(1, min(99, (int) ($it['qte'] ?? 0)));
    if ($pid > 0) {
        $demande[$pid] = ($demande[$pid] ?? 0) + $qte;
    }
}
if (!$demande) {
    repondre(['ok' => false, 'message' => 'Votre sélection est vide.'], 422);
}

$marques = implode(',', array_fill(0, count($demande), '?'));
$st = db()->prepare(
    "SELECT id, nom, type, contenance, prix FROM produits
      WHERE id IN ($marques) AND visible = 1 AND disponibilite <> 'rupture'"
);
$st->execute(array_keys($demande));
$produits = [];
foreach ($st->fetchAll() as $row) {
    $produits[(int) $row['id']] = $row;
}

$indisponibles = array_values(array_diff(array_keys($demande), array_keys($produits)));
if ($indisponibles) {
    repondre([
        'ok' => false,
        'message' => 'Certains articles ne sont plus disponibles. Ils ont été retirés de votre sélection.',
        'indisponibles' => $indisponibles,
    ], 409);
}

$lignes = [];
$total = $nbArticles = $nbParfums = 0;
foreach ($demande as $pid => $qte) {
    $p = $produits[$pid];
    $lignes[] = ['p' => $p, 'qte' => $qte];
    $total      += (int) $p['prix'] * $qte;
    $nbArticles += $qte;
    if ($p['type'] === 'parfum') {
        $nbParfums += $qte;
    }
}

// --- Enregistrement (transaction) ---
try {
    $pdo = db();
    $pdo->beginTransaction();

    // Client : créé ou mis à jour d'après son numéro de téléphone
    $pdo->prepare(
        'INSERT INTO clients (nom, telephone, adresse) VALUES (:n, :t, :a)
         ON DUPLICATE KEY UPDATE nom = VALUES(nom), adresse = VALUES(adresse), id = LAST_INSERT_ID(id)'
    )->execute([':n' => $nom, ':t' => $tel, ':a' => $adresse]);
    $clientId = (int) $pdo->lastInsertId();

    $pdo->prepare(
        'INSERT INTO commandes (reference, client_id, nom_client, telephone, adresse, paiement, note,
                                nb_articles, nb_parfums, total)
         VALUES (:ref, :cid, :n, :t, :a, :pay, :note, :na, :np, :tot)'
    )->execute([
        ':ref' => 'TMP' . bin2hex(random_bytes(6)),
        ':cid' => $clientId, ':n' => $nom, ':t' => $tel, ':a' => $adresse,
        ':pay' => $paiement, ':note' => $note !== '' ? $note : null,
        ':na' => $nbArticles, ':np' => $nbParfums, ':tot' => $total,
    ]);
    $commandeId = (int) $pdo->lastInsertId();
    $reference  = sprintf('BC-%s-%04d', date('ymd'), $commandeId % 10000);
    $pdo->prepare('UPDATE commandes SET reference = ? WHERE id = ?')->execute([$reference, $commandeId]);

    $ins = $pdo->prepare(
        'INSERT INTO commande_items (commande_id, produit_id, nom, type, contenance, prix, qte)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    foreach ($lignes as $l) {
        $p = $l['p'];
        $ins->execute([$commandeId, (int) $p['id'], $p['nom'], $p['type'], $p['contenance'] ?: null, (int) $p['prix'], $l['qte']]);
    }

    $pdo->commit();
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Commande : ' . $e->getMessage());
    repondre(['ok' => false, 'message' => 'Erreur serveur. Réessayez dans un instant.'], 500);
}

$_SESSION['cmd_essais'][] = $now;

// --- Message WhatsApp complet ---
$marque = get_param('nom_marque', 'Black Collection');
$wa     = preg_replace('/\D+/', '', get_param('whatsapp', '2250554971592'));

$msg  = "Bonjour $marque,\nJe souhaite commander les articles suivants :\n\n";
foreach ($lignes as $l) {
    $p = $l['p'];
    $label = $p['nom']
        . ($p['type'] === 'vetement' ? ' [Vêtement]' : ($p['contenance'] ? ' (' . $p['contenance'] . ')' : ''));
    $msg .= '• ' . $l['qte'] . 'x ' . $label . ' — ' . prix((int) $p['prix'] * $l['qte']) . "\n";
}
$msg .= "\n📦 Total articles : $nbArticles (dont $nbParfums parfums)";
$msg .= "\n💰 Total commande : " . prix($total);
$msg .= "\n\n--- Coordonnées client ---";
$msg .= "\n👤 Nom & Prénoms : $nom";
$msg .= "\n📍 Lieu de livraison : $adresse";
$msg .= "\n📞 Contact : +$tel";
$msg .= "\n💳 Paiement souhaité : " . ($paiement === 'wave' ? 'Wave' : 'Espèces');
if ($note !== '') {
    $msg .= "\n📝 Note : $note";
}
$msg .= "\n\nRéférence : $reference";

repondre([
    'ok'        => true,
    'reference' => $reference,
    'wa_url'    => 'https://wa.me/' . $wa . '?text=' . rawurlencode($msg),
]);