<?php
/**
 * fonctions-factures.php
 * Création, lecture et recherche de factures.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/fonctions-produits.php';

// ─────────────────────────────────────────────
// Charge toutes les factures
// ─────────────────────────────────────────────
function chargerFactures(): array {
    if (!file_exists(FICHIER_FACTURES)) return [];
    $data = json_decode(file_get_contents(FICHIER_FACTURES), true);
    return is_array($data) ? $data : [];
}

// ─────────────────────────────────────────────
// Crée une nouvelle facture
// $client  : ['nom' => ..., 'email' => ...]
// $lignes  : [['code_barre' => ..., 'quantite' => ...], ...]
// Retourne l'id de la facture créée ou false
// ─────────────────────────────────────────────
function creerFacture(array $client, array $lignes, string $userId): string|false {
    if (empty($lignes)) return false;

    $lignesValidees = [];
    $total = 0;

    foreach ($lignes as $ligne) {
        $code = preg_replace('/\D/', '', $ligne['code_barre'] ?? '');
        $qte  = abs((int)($ligne['quantite'] ?? 1));
        if ($qte < 1 || empty($code)) continue;

        $produit = rechercherParCodeBarre($code);
        if (!$produit) continue;

        // Vérifier le stock disponible
        if ($produit['stock'] < $qte) return false;

        $sousTotal = $produit['prix_unitaire'] * $qte;
        $total    += $sousTotal;

        $lignesValidees[] = [
            'produit_id'    => $produit['id'],
            'nom'           => $produit['nom'],
            'code_barre'    => $code,
            'quantite'      => $qte,
            'prix_unitaire' => $produit['prix_unitaire'],
            'sous_total'    => $sousTotal,
        ];

        // Décrémenter le stock
        $produit['stock'] -= $qte;
        sauvegarderProduit($produit);
    }

    if (empty($lignesValidees)) return false;

    $factures = chargerFactures();
    $id = 'FAC-' . date('Y') . '-' . str_pad(count($factures) + 1, 4, '0', STR_PAD_LEFT);

    $factures[] = [
        'id'           => $id,
        'client_nom'   => htmlspecialchars(trim($client['nom'] ?? 'Client'), ENT_QUOTES),
        'client_email' => htmlspecialchars(trim($client['email'] ?? ''), ENT_QUOTES),
        'lignes'       => $lignesValidees,
        'total'        => $total,
        'statut'       => 'payee',
        'cree_par'     => $userId,
        'cree_le'      => date('Y-m-d\TH:i:s'),
    ];

    return _ecrireJSON(FICHIER_FACTURES, $factures) ? $id : false;
}

// ─────────────────────────────────────────────
// Retourne une facture par son id
// ─────────────────────────────────────────────
function trouverFacture(string $id): ?array {
    foreach (chargerFactures() as $f) {
        if ($f['id'] === $id) return $f;
    }
    return null;
}

// ─────────────────────────────────────────────
// Statistiques rapides pour le tableau de bord
// ─────────────────────────────────────────────
function statsFactures(): array {
    $factures = chargerFactures();
    $total    = array_sum(array_column($factures, 'total'));
    return [
        'nombre' => count($factures),
        'total'  => $total,
    ];
}
