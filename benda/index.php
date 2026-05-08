<?php
require_once __DIR__ . '/auth/session.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/fonctions-produits.php';
require_once __DIR__ . '/includes/fonctions-factures.php';

exigerConnexion();

$titrePage = 'Tableau de bord';
$stats = statsFactures();
$nbProduits = count(chargerProduits());

require_once __DIR__ . '/includes/header.php';
?>

<h1>🏠 Tableau de bord</h1>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:24px;">
    <div class="card" style="text-align:center;">
        <div style="font-size:36px;">🧾</div>
        <div style="font-size:28px;font-weight:bold;color:#2980b9;"><?= $stats['nombre'] ?></div>
        <div style="color:#666;">Factures émises</div>
    </div>
    <div class="card" style="text-align:center;">
        <div style="font-size:36px;">💰</div>
        <div style="font-size:28px;font-weight:bold;color:#27ae60;"><?= number_format($stats['total'], 0, ',', '.') ?> <?= DEVISE ?></div>
        <div style="color:#666;">Chiffre d'affaires</div>
    </div>
    <div class="card" style="text-align:center;">
        <div style="font-size:36px;">📦</div>
        <div style="font-size:28px;font-weight:bold;color:#8e44ad;"><?= $nbProduits ?></div>
        <div style="color:#666;">Produits enregistrés</div>
    </div>
</div>

<div class="card">
    <h2>Actions rapides</h2>
    <a href="<?= BASE_URL ?>/modules/facturation/nouvelle.php" class="btn btn-vert" style="margin-right:10px;">➕ Nouvelle facture</a>
    <a href="<?= BASE_URL ?>/modules/produits/enregistrer.php" class="btn btn-bleu" style="margin-right:10px;">📦 Ajouter un produit</a>
    <a href="<?= BASE_URL ?>/modules/facturation/liste.php" class="btn btn-bleu">📋 Voir les factures</a>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
