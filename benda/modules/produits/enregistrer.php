<?php
/**
 * enregistrer.php
 * Formulaire d'ajout / modification d'un produit avec scanner de code-barres.
 */

require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../includes/fonctions-produits.php';

exigerConnexion('superadmin'); // seul le superadmin peut gérer les produits

$message = '';
$erreur  = '';

// ── Traitement du formulaire (POST) ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $donnees = [
        'id'            => trim($_POST['id'] ?? ''),          // vide = nouveau produit
        'nom'           => trim($_POST['nom'] ?? ''),
        'code_barre'    => trim($_POST['code_barre'] ?? ''),
        'prix_unitaire' => $_POST['prix_unitaire'] ?? 0,
        'stock'         => $_POST['stock'] ?? 0,
        'categorie'     => trim($_POST['categorie'] ?? ''),
    ];

    if (sauvegarderProduit($donnees)) {
        $message = 'Produit enregistré avec succès.';
    } else {
        $erreur = 'Erreur : vérifiez que le nom et le code-barres sont renseignés.';
    }
}

// ── Pré-remplissage si modification (GET ?id=...) ────────────────────────────
$produit = null;
if (!empty($_GET['id'])) {
    foreach (chargerProduits() as $p) {
        if ($p['id'] === $_GET['id']) { $produit = $p; break; }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Enregistrer un produit – UPC Facturation</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Html5-QRCode via CDN (pas de serveur complexe requis) -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 40px auto; padding: 0 16px; }
        h1   { color: #2c3e50; }
        label { display: block; margin-top: 12px; font-weight: bold; }
        input, select { width: 100%; padding: 8px; margin-top: 4px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        button { margin-top: 16px; padding: 10px 24px; background: #2980b9; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #1a6fa0; }
        #btn-scanner { background: #27ae60; }
        #btn-scanner:hover { background: #1e8449; }
        #reader { margin-top: 12px; border: 2px dashed #27ae60; border-radius: 4px; }
        .alerte-succes { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 12px; }
        .alerte-erreur { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 12px; }
    </style>
</head>
<body>

<h1><?= $produit ? 'Modifier le produit' : 'Nouveau produit' ?></h1>

<?php if ($message): ?>
    <div class="alerte-succes"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>
<?php if ($erreur): ?>
    <div class="alerte-erreur"><?= htmlspecialchars($erreur) ?></div>
<?php endif; ?>

<form method="POST" action="">
    <!-- Champ caché pour l'id (modification) -->
    <input type="hidden" name="id" value="<?= htmlspecialchars($produit['id'] ?? '') ?>">

    <!-- ── Code-barres ── -->
    <label for="code_barre">Code-barres</label>
    <input type="text" id="code_barre" name="code_barre"
           value="<?= htmlspecialchars($produit['code_barre'] ?? '') ?>"
           placeholder="Scanner ou saisir manuellement" required>

    <!-- Bouton pour activer la caméra -->
    <button type="button" id="btn-scanner" onclick="toggleScanner()">📷 Scanner avec la caméra</button>
    <div id="reader"></div>

    <!-- ── Informations produit ── -->
    <label for="nom">Nom du produit</label>
    <input type="text" id="nom" name="nom"
           value="<?= htmlspecialchars($produit['nom'] ?? '') ?>"
           placeholder="Ex : Cahier 100 pages" required>

    <label for="prix_unitaire">Prix unitaire (FC)</label>
    <input type="number" id="prix_unitaire" name="prix_unitaire" min="0" step="0.01"
           value="<?= htmlspecialchars($produit['prix_unitaire'] ?? '') ?>"
           placeholder="Ex : 1500" required>

    <label for="stock">Stock disponible</label>
    <input type="number" id="stock" name="stock" min="0"
           value="<?= htmlspecialchars($produit['stock'] ?? '') ?>"
           placeholder="Ex : 50" required>

    <label for="categorie">Catégorie</label>
    <input type="text" id="categorie" name="categorie"
           value="<?= htmlspecialchars($produit['categorie'] ?? '') ?>"
           placeholder="Ex : Fournitures">

    <button type="submit">💾 Enregistrer</button>
</form>

<script>
/**
 * scanner.js (intégré ici pour simplicité)
 * Utilise Html5-QRCode pour lire un code-barres via la caméra.
 */
let scanner = null;
let scannerActif = false;

function toggleScanner() {
    scannerActif ? arreterScanner() : demarrerScanner();
}

function demarrerScanner() {
    scanner = new Html5Qrcode("reader");
    scanner.start(
        { facingMode: "environment" },          // caméra arrière sur mobile
        { fps: 10, qrbox: { width: 300, height: 150 } },
        (codeDetecte) => {
            document.getElementById('code_barre').value = codeDetecte;
            arreterScanner();
        },
        (erreur) => { /* ignorer les erreurs de frame */ }
    ).catch(err => alert('Impossible d\'accéder à la caméra : ' + err));

    scannerActif = true;
    document.getElementById('btn-scanner').textContent = '⏹ Arrêter le scanner';
}

function arreterScanner() {
    if (scanner) {
        scanner.stop().then(() => {
            scanner.clear();
            document.getElementById('reader').innerHTML = '';
        });
    }
    scannerActif = false;
    document.getElementById('btn-scanner').textContent = '📷 Scanner avec la caméra';
}
</script>

</body>
</html>
