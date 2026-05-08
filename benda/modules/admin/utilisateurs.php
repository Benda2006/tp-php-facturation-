<?php
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/fonctions-auth.php';

exigerConnexion('superadmin');

$message = '';
$erreur  = '';

// Créer un utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'creer') {
        $ok = creerUtilisateur(
            trim($_POST['nom']   ?? ''),
            trim($_POST['email'] ?? ''),
            $_POST['mot_de_passe'] ?? '',
            $_POST['role'] ?? ''
        );
        $message = $ok ? 'Utilisateur créé.' : 'Erreur : email déjà utilisé, mot de passe trop court, ou rôle invalide.';
        if (!$ok) $erreur = $message; $message = $ok ? $message : '';
    } elseif ($_POST['action'] === 'toggle') {
        toggleUtilisateur(trim($_POST['user_id'] ?? ''));
        header('Location: ' . BASE_URL . '/modules/admin/utilisateurs.php');
        exit;
    }
}

$titrePage    = 'Gestion des utilisateurs';
$utilisateurs = chargerUtilisateurs();

require_once __DIR__ . '/../../includes/header.php';
?>

<h1>👥 Utilisateurs</h1>

<?php if ($message): ?><div class="alerte-succes"><?= htmlspecialchars($message) ?></div><?php endif; ?>
<?php if ($erreur):  ?><div class="alerte-erreur"><?= htmlspecialchars($erreur)  ?></div><?php endif; ?>

<div class="card">
    <h2>Ajouter un compte</h2>
    <form method="POST" action="">
        <input type="hidden" name="action" value="creer">

        <label>Nom complet</label>
        <input type="text" name="nom" placeholder="Ex : Marie Kabila" required>

        <label>Email</label>
        <input type="email" name="email" placeholder="marie@upc.cd" required>

        <label>Mot de passe (min. 6 caractères)</label>
        <input type="password" name="mot_de_passe" required>

        <label>Rôle</label>
        <select name="role">
            <option value="caissier">Caissier</option>
            <option value="superadmin">Super Admin</option>
        </select>

        <button class="btn btn-vert" type="submit" style="margin-top:16px;">➕ Créer</button>
    </form>
</div>

<div class="card">
    <h2>Comptes existants</h2>
    <table>
        <thead>
            <tr><th>Nom</th><th>Email</th><th>Rôle</th><th>Statut</th><th>Action</th></tr>
        </thead>
        <tbody>
        <?php foreach ($utilisateurs as $u): ?>
            <tr>
                <td><?= htmlspecialchars($u['nom']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['role']) ?></td>
                <td style="color:<?= $u['actif'] ? '#27ae60' : '#e74c3c' ?>;">
                    <?= $u['actif'] ? '✅ Actif' : '🚫 Inactif' ?>
                </td>
                <td>
                    <form method="POST" action="" style="display:inline;">
                        <input type="hidden" name="action"  value="toggle">
                        <input type="hidden" name="user_id" value="<?= htmlspecialchars($u['id']) ?>">
                        <button class="btn <?= $u['actif'] ? 'btn-rouge' : 'btn-vert' ?>" type="submit">
                            <?= $u['actif'] ? '🚫 Désactiver' : '✅ Activer' ?>
                        </button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
