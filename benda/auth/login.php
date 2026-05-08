<?php
/**
 * login.php — Formulaire de connexion
 */
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../config/config.php';

// Déjà connecté → rediriger
if (utilisateurConnecte()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $mdp   = $_POST['mot_de_passe'] ?? '';

    if (connecterUtilisateur($email, $mdp)) {
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
    $erreur = 'Email ou mot de passe incorrect.';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion — <?= APP_NOM ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; background: #f0f2f5; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .boite { background: #fff; padding: 36px 32px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,.12); width: 100%; max-width: 380px; }
        h1 { text-align: center; color: #2c3e50; margin-bottom: 24px; font-size: 20px; }
        label { display: block; margin-top: 14px; font-weight: bold; font-size: 14px; }
        input { width: 100%; padding: 9px 10px; margin-top: 4px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; box-sizing: border-box; }
        button { width: 100%; margin-top: 20px; padding: 10px; background: #2980b9; color: #fff; border: none; border-radius: 4px; font-size: 15px; cursor: pointer; }
        button:hover { background: #1a6fa0; }
        .erreur { background: #f8d7da; color: #721c24; padding: 9px 12px; border-radius: 4px; margin-bottom: 10px; font-size: 14px; }
        .logo { text-align: center; font-size: 40px; margin-bottom: 8px; }
    </style>
</head>
<body>
<div class="boite">
    <div class="logo">📦</div>
    <h1><?= APP_NOM ?></h1>

    <?php if ($erreur): ?>
        <div class="erreur"><?= htmlspecialchars($erreur) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="email">Adresse email</label>
        <input type="email" id="email" name="email"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
               placeholder="admin@upc.cd" required autofocus>

        <label for="mot_de_passe">Mot de passe</label>
        <input type="password" id="mot_de_passe" name="mot_de_passe"
               placeholder="••••••••" required>

        <button type="submit">Se connecter</button>
    </form>
</div>
</body>
</html>
