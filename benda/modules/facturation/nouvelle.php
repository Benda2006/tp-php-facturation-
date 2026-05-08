<?php
/**
 * nouvelle.php — Création d'une facture
 * Le caissier scanne les produits un par un, puis valide.
 */
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/fonctions-factures.php';

exigerConnexion(); // caissier ou superadmin

$titrePage = 'Nouvelle facture';
$message   = '';
$erreur    = '';
$factureId = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client = [
        'nom'   => trim($_POST['client_nom']   ?? 'Client'),
        'email' => trim($_POST['client_email'] ?? ''),
    ];

    // Lignes envoyées en JSON depuis le JS
    $lignesJson = $_POST['lignes_json'] ?? '[]';
    $lignes     = json_decode($lignesJson, true);

    if (!is_array($lignes) || empty($lignes)) {
        $erreur = 'Ajoutez au moins un produit à la facture.';
    } else {
        $user = utilisateurConnecte();
        $id   = creerFacture($client, $lignes, $user['id']);
        if ($id) {
            $message   = "Facture $id créée avec succès.";
            $factureId = $id;
        } else {
            $erreur = 'Erreur lors de la création (stock insuffisant ou produit introuvable).';
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<h1>🧾 Nouvelle facture</h1>

<?php if ($message): ?>
    <div class="alerte-succes">
        <?= htmlspecialchars($message) ?>
        — <a href="<?= BASE_URL ?>/modules/facturation/voir.php?id=<?= urlencode($factureId) ?>">Voir la facture</a>
    </div>
<?php endif; ?>
<?php if ($erreur): ?>
    <div class="alerte-erreur"><?= htmlspecialchars($erreur) ?></div>
<?php endif; ?>

<div class="card">
    <h2>Informations client</h2>
    <form id="form-facture" method="POST" action="">
        <label for="client_nom">Nom du client</label>
        <input type="text" id="client_nom" name="client_nom" placeholder="Ex : Jean Mukendi" required>

        <label for="client_email">Email (optionnel)</label>
        <input type="email" id="client_email" name="client_email" placeholder="client@email.com">

        <!-- Lignes sérialisées en JSON avant soumission -->
        <input type="hidden" id="lignes_json" name="lignes_json" value="[]">
    </form>
</div>

<div class="card">
    <h2>Scanner un produit</h2>

    <label for="champ_code">Code-barres</label>
    <div style="display:flex;gap:8px;margin-top:4px;">
        <input type="text" id="champ_code" placeholder="Scanner ou saisir le code" style="flex:1;">
        <button class="btn btn-vert" onclick="toggleScanner()">📷 Caméra</button>
        <button class="btn btn-bleu" onclick="ajouterLigne()">➕ Ajouter</button>
    </div>
    <div id="reader" style="margin-top:10px;"></div>
    <div id="msg-produit" style="margin-top:8px;font-size:13px;color:#666;"></div>
</div>

<div class="card">
    <h2>Lignes de la facture</h2>
    <table id="table-lignes">
        <thead>
            <tr><th>Produit</th><th>Code-barres</th><th>Qté</th><th>Prix unit.</th><th>Sous-total</th><th></th></tr>
        </thead>
        <tbody id="corps-lignes">
            <tr id="ligne-vide"><td colspan="6" style="text-align:center;color:#999;">Aucun produit ajouté</td></tr>
        </tbody>
    </table>
    <div style="text-align:right;margin-top:12px;font-size:18px;font-weight:bold;">
        Total : <span id="total-affiche">0</span> <?= DEVISE ?>
    </div>
    <button class="btn btn-vert" style="margin-top:16px;width:100%;" onclick="validerFacture()">💾 Valider la facture</button>
</div>

<!-- Html5-QRCode -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
const BASE_URL  = '<?= BASE_URL ?>';
let lignes      = [];   // tableau des lignes en mémoire
let scanner     = null;
let scannerActif= false;

// ── Scanner caméra ────────────────────────────────────────────────────────────
function toggleScanner() {
    scannerActif ? arreterScanner() : demarrerScanner();
}
function demarrerScanner() {
    scanner = new Html5Qrcode("reader");
    scanner.start(
        { facingMode: "environment" },
        { fps: 10, qrbox: { width: 300, height: 120 } },
        (code) => { document.getElementById('champ_code').value = code; arreterScanner(); ajouterLigne(); },
        () => {}
    ).catch(e => alert('Caméra inaccessible : ' + e));
    scannerActif = true;
}
function arreterScanner() {
    if (scanner) scanner.stop().then(() => { scanner.clear(); document.getElementById('reader').innerHTML = ''; });
    scannerActif = false;
}

// ── Recherche produit via AJAX (PHP endpoint) ─────────────────────────────────
async function ajouterLigne() {
    const code = document.getElementById('champ_code').value.trim();
    if (!code) return;

    const rep  = await fetch(BASE_URL + '/api/produit.php?code=' + encodeURIComponent(code));
    const data = await rep.json();

    if (!data.ok) {
        document.getElementById('msg-produit').textContent = '⚠️ Produit introuvable : ' + code;
        return;
    }

    // Vérifier si déjà dans la liste → incrémenter la quantité
    const existant = lignes.find(l => l.code_barre === data.produit.code_barre);
    if (existant) {
        existant.quantite++;
        existant.sous_total = existant.quantite * existant.prix_unitaire;
    } else {
        lignes.push({
            code_barre    : data.produit.code_barre,
            nom           : data.produit.nom,
            quantite      : 1,
            prix_unitaire : data.produit.prix_unitaire,
            sous_total    : data.produit.prix_unitaire,
        });
    }

    document.getElementById('champ_code').value = '';
    document.getElementById('msg-produit').textContent = '✅ ' + data.produit.nom + ' ajouté.';
    rafraichirTableau();
}

// ── Affichage du tableau ──────────────────────────────────────────────────────
function rafraichirTableau() {
    const corps = document.getElementById('corps-lignes');
    corps.innerHTML = '';

    if (lignes.length === 0) {
        corps.innerHTML = '<tr id="ligne-vide"><td colspan="6" style="text-align:center;color:#999;">Aucun produit ajouté</td></tr>';
        document.getElementById('total-affiche').textContent = '0';
        return;
    }

    let total = 0;
    lignes.forEach((l, i) => {
        total += l.sous_total;
        corps.innerHTML += `<tr>
            <td>${l.nom}</td>
            <td>${l.code_barre}</td>
            <td><input type="number" min="1" value="${l.quantite}" style="width:60px;"
                 onchange="changerQte(${i}, this.value)"></td>
            <td>${l.prix_unitaire.toLocaleString('fr-CD')} <?= DEVISE ?></td>
            <td>${l.sous_total.toLocaleString('fr-CD')} <?= DEVISE ?></td>
            <td><button class="btn btn-rouge" onclick="supprimerLigne(${i})">✕</button></td>
        </tr>`;
    });
    document.getElementById('total-affiche').textContent = total.toLocaleString('fr-CD');
}

function changerQte(index, val) {
    lignes[index].quantite  = Math.max(1, parseInt(val) || 1);
    lignes[index].sous_total= lignes[index].quantite * lignes[index].prix_unitaire;
    rafraichirTableau();
}

function supprimerLigne(index) {
    lignes.splice(index, 1);
    rafraichirTableau();
}

// ── Soumission du formulaire ──────────────────────────────────────────────────
function validerFacture() {
    if (lignes.length === 0) { alert('Ajoutez au moins un produit.'); return; }
    document.getElementById('lignes_json').value = JSON.stringify(lignes);
    document.getElementById('form-facture').submit();
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
