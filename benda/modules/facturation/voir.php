<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/fonctions-factures.php';

exigerConnexion();

$id      = $_GET['id'] ?? '';
$facture = trouverFacture($id);

if (!$facture) {
    http_response_code(404);
    exit('Facture introuvable.');
}

$titrePage = 'Facture ' . $facture['id'];
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="card" id="zone-impression">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
        <div>
            <h1>📄 <?= htmlspecialchars($facture['id']) ?></h1>
            <p>Date : <?= date('d/m/Y à H:i', strtotime($facture['cree_le'])) ?></p>
        </div>
        <div style="text-align:right;">
            <strong><?= APP_NOM ?></strong><br>
            Université Protestante au Congo
        </div>
    </div>

    <hr style="margin:16px 0;">

    <p><strong>Client :</strong> <?= htmlspecialchars($facture['client_nom']) ?></p>
    <?php if ($facture['client_email']): ?>
        <p><strong>Email :</strong> <?= htmlspecialchars($facture['client_email']) ?></p>
    <?php endif; ?>

    <table style="margin-top:16px;">
        <thead>
            <tr><th>Produit</th><th>Code-barres</th><th>Qté</th><th>Prix unit.</th><th>Sous-total</th></tr>
        </thead>
        <tbody>
        <?php foreach ($facture['lignes'] as $ligne): ?>
            <tr>
                <td><?= htmlspecialchars($ligne['nom']) ?></td>
                <td><?= htmlspecialchars($ligne['code_barre']) ?></td>
                <td><?= $ligne['quantite'] ?></td>
                <td><?= number_format($ligne['prix_unitaire'], 0, ',', '.') ?> <?= DEVISE ?></td>
                <td><?= number_format($ligne['sous_total'],    0, ',', '.') ?> <?= DEVISE ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" style="text-align:right;font-weight:bold;">TOTAL</td>
                <td style="font-weight:bold;"><?= number_format($facture['total'], 0, ',', '.') ?> <?= DEVISE ?></td>
            </tr>
        </tfoot>
    </table>
</div>

<button class="btn btn-bleu" onclick="window.print()" style="margin-right:8px;">🖨 Imprimer</button>
<a href="<?= BASE_URL ?>/modules/facturation/liste.php" class="btn btn-rouge">← Retour</a>

<style>
@media print {
    nav, footer, button, a.btn { display: none !important; }
    body { background: #fff; }
    main { margin: 0; padding: 0; max-width: 100%; }
}
</style>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
