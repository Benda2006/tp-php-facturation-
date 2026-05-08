<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/fonctions-produits.php';

exigerConnexion();

// Suppression (superadmin uniquement)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['supprimer_id'])) {
    $user = utilisateurConnecte();
    if ($user['role'] === 'superadmin') {
        supprimerProduit(trim($_POST['supprimer_id']));
    }
    header('Location: ' . BASE_URL . '/modules/produits/liste.php');
    exit;
}

$titrePage = 'Produits';
$recherche = trim($_GET['q'] ?? '');
$produits  = chargerProduits();

// Filtrage côté serveur si recherche
if ($recherche !== '') {
    $produits = array_filter($produits, function($p) use ($recherche) {
        return stripos($p['nom'], $recherche) !== false
            || stripos($p['code_barre'], $recherche) !== false;
    });
}

require_once __DIR__ . '/../../includes/header.php';
?>

<h1>📦 Produits</h1>

<div style="display:flex;gap:10px;margin-bottom:16px;align-items:center;">
    <form method="GET" action="" style="display:flex;gap:8px;flex:1;">
        <input type="text" name="q" value="<?= htmlspecialchars($recherche) ?>"
               placeholder="Rechercher par nom ou code-barres" style="flex:1;">
        <button class="btn btn-bleu" type="submit">🔍 Rechercher</button>
        <?php if ($recherche): ?>
            <a href="?" class="btn btn-rouge">✕ Effacer</a>
        <?php endif; ?>
    </form>
    <?php if ((utilisateurConnecte()['role'] ?? '') === 'superadmin'): ?>
        <a href="<?= BASE_URL ?>/modules/produits/enregistrer.php" class="btn btn-vert">➕ Nouveau produit</a>
    <?php endif; ?>
</div>

<div class="card">
    <?php if (empty($produits)): ?>
        <p style="color:#999;">Aucun produit trouvé.</p>
    <?php else: ?>
    <table>
        <thead>
            <tr><th>Nom</th><th>Code-barres</th><th>Prix</th><th>Stock</th><th>Catégorie</th>
            <?php if ((utilisateurConnecte()['role'] ?? '') === 'superadmin'): ?><th>Actions</th><?php endif; ?>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($produits as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['nom']) ?></td>
                <td><code><?= htmlspecialchars($p['code_barre']) ?></code></td>
                <td><?= number_format($p['prix_unitaire'], 0, ',', '.') ?> <?= DEVISE ?></td>
                <td style="color:<?= $p['stock'] < 5 ? '#e74c3c' : '#27ae60' ?>;">
                    <?= $p['stock'] ?>
                </td>
                <td><?= htmlspecialchars($p['categorie'] ?? '') ?></td>
                <?php if ((utilisateurConnecte()['role'] ?? '') === 'superadmin'): ?>
                <td>
                    <a href="<?= BASE_URL ?>/modules/produits/enregistrer.php?id=<?= urlencode($p['id']) ?>"
                       class="btn btn-bleu" style="margin-right:4px;">✏️</a>
                    <form method="POST" action="" style="display:inline;"
                          onsubmit="return confirm('Supprimer ce produit ?');">
                        <input type="hidden" name="supprimer_id" value="<?= htmlspecialchars($p['id']) ?>">
                        <button class="btn btn-rouge" type="submit">🗑</button>
                    </form>
                </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
