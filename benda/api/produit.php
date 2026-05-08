²<?php
/**
 * api/produit.php — Retourne un produit en JSON (utilisé par le JS de facturation)
 */
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../includes/fonctions-produits.php';

header('Content-Type: application/json');

exigerConnexion();

$code    = $_GET['code'] ?? '';
$produit = rechercherParCodeBarre($code);

if ($produit) {
    echo json_encode(['ok' => true, 'produit' => $produit]);
} else {
    http_response_code(404);
    echo json_encode(['ok' => false, 'message' => 'Produit introuvable']);
}
