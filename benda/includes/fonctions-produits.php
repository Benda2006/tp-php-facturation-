<?php
/**
 * fonctions-produits.php
 * Fonctions de gestion des produits (lecture, sauvegarde, recherche)
 * UPC - Système de Facturation avec Codes-Barres
 */

define('FICHIER_PRODUITS', __DIR__ . '/../data/produits.json');

// ─────────────────────────────────────────────
// Charge et retourne tous les produits depuis le JSON
// ─────────────────────────────────────────────
function chargerProduits(): array {
    if (!file_exists(FICHIER_PRODUITS)) return [];

    $contenu = file_get_contents(FICHIER_PRODUITS);
    $produits = json_decode($contenu, true);

    // json_decode retourne null si le fichier est vide ou corrompu
    return is_array($produits) ? $produits : [];
}

// ─────────────────────────────────────────────
// Ajoute un nouveau produit ou modifie un existant (par id)
// $donnees doit contenir : nom, code_barre, prix_unitaire, stock, categorie
// ─────────────────────────────────────────────
function sauvegarderProduit(array $donnees): bool {
    // Nettoyage des entrées
    $donnees['nom']          = htmlspecialchars(trim($donnees['nom'] ?? ''), ENT_QUOTES);
    $donnees['code_barre']   = preg_replace('/\D/', '', $donnees['code_barre'] ?? '');
    $donnees['prix_unitaire']= abs((float)($donnees['prix_unitaire'] ?? 0));
    $donnees['stock']        = abs((int)($donnees['stock'] ?? 0));
    $donnees['categorie']    = htmlspecialchars(trim($donnees['categorie'] ?? ''), ENT_QUOTES);

    if (empty($donnees['nom']) || empty($donnees['code_barre'])) return false;

    $produits = chargerProduits();
    $trouve   = false;

    // Mise à jour si le produit existe déjà (même id)
    foreach ($produits as &$p) {
        if (isset($donnees['id']) && $p['id'] === $donnees['id']) {
            $p       = array_merge($p, $donnees);
            $trouve  = true;
            break;
        }
    }
    unset($p); // libérer la référence

    // Nouveau produit : générer un id unique
    if (!$trouve) {
        $donnees['id']      = 'PROD-' . strtoupper(uniqid());
        $donnees['cree_le'] = date('Y-m-d\TH:i:s');
        $produits[]         = $donnees;
    }

    return _ecrireJSON(FICHIER_PRODUITS, $produits);
}

// ─────────────────────────────────────────────
// Recherche un produit par son code-barres
// Retourne le produit trouvé ou null
// ─────────────────────────────────────────────
function rechercherParCodeBarre(string $code): ?array {
    $code = preg_replace('/\D/', '', $code); // garder uniquement les chiffres
    if (empty($code)) return null;

    foreach (chargerProduits() as $produit) {
        if ($produit['code_barre'] === $code) return $produit;
    }
    return null;
}

// ─────────────────────────────────────────────
// Supprime un produit par son id
// ─────────────────────────────────────────────
function supprimerProduit(string $id): bool {
    $produits = chargerProduits();
    $filtres  = array_filter($produits, fn($p) => $p['id'] !== $id);

    if (count($filtres) === count($produits)) return false; // id introuvable

    return _ecrireJSON(FICHIER_PRODUITS, array_values($filtres));
}

// ─────────────────────────────────────────────
// Fonction interne : écrit un tableau dans un fichier JSON
// Utilise flock() pour éviter les conflits d'écriture simultanée
// ─────────────────────────────────────────────
function _ecrireJSON(string $chemin, array $donnees): bool {
    $fp = fopen($chemin, 'c+'); // ouvre sans écraser, crée si absent
    if (!$fp) return false;

    if (flock($fp, LOCK_EX)) {           // verrou exclusif
        ftruncate($fp, 0);               // vider le fichier
        rewind($fp);
        fwrite($fp, json_encode($donnees, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        flock($fp, LOCK_UN);             // libérer le verrou
    } else {
        fclose($fp);
        return false;
    }

    fclose($fp);
    return true;
}
