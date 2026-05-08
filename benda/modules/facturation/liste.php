<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/fonctions-factures.php';

exigerConnexion();

$titrePage = 'Liste des factures';
$factures  = array_reverse(chargerFactures()); // plus récentes en premier

require_once __DIR__ . '/../../includes/header.php';
?>

<h1>🧾 Factures</h1>
<a href="<?= BASE_URL ?>/modules/facturation/nouvelle.php" class="btn btn-vert" style="margin-bottom:16px;">➕ Nouvelle facture</a>

<div class="card">
    <?php if (empty($factures)): ?>
        <p style="color:#999;">Aucune facture enregistrée.</p>
    <?php else: ?>
    <table>
        <thead>
            <tr><th>N° Facture</th><th>Client</th><th>Total</th><th>Date</th><th>Action</th></tr>
        </thead>
        <tbody>
        <?php foreach ($factures as $f): ?>
            <tr>
                <td><?= htmlspecialchars($f['id']) ?></td>
                <td><?= htmlspecialchars($f['client_nom']) ?></td>
                <td><?= number_format($f['total'], 0, ',', '.') ?> <?= DEVISE ?></td>
                <td><?= date('d/m/Y H:i', strtotime($f['cree_le'])) ?></td>
                <td>
                    <a href="<?= BASE_URL ?>/modules/facturation/voir.php?id=<?= urlencode($f['id']) ?>"
                       class="btn btn-bleu">👁 Voir</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
