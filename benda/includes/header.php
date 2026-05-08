<?php
/**
 * header.php — En-tête commun (navigation)
 * Inclure APRÈS session.php et exigerConnexion()
 */
require_once __DIR__ . '/../config/config.php';
$user = utilisateurConnecte();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titrePage ?? APP_NOM) ?></title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body   { font-family: Arial, sans-serif; background: #f0f2f5; color: #333; }
        nav    { background: #2c3e50; padding: 12px 24px; display: flex; align-items: center; justify-content: space-between; }
        nav a  { color: #ecf0f1; text-decoration: none; margin-right: 16px; font-size: 14px; }
        nav a:hover { text-decoration: underline; }
        .nav-user { color: #bdc3c7; font-size: 13px; }
        main   { max-width: 960px; margin: 32px auto; padding: 0 16px; }
        .card  { background: #fff; border-radius: 6px; padding: 24px; box-shadow: 0 1px 4px rgba(0,0,0,.1); margin-bottom: 20px; }
        h1     { font-size: 22px; margin-bottom: 16px; color: #2c3e50; }
        h2     { font-size: 18px; margin-bottom: 12px; color: #34495e; }
        table  { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px 12px; text-align: left; border-bottom: 1px solid #eee; font-size: 14px; }
        th     { background: #f8f9fa; font-weight: bold; }
        .btn        { display: inline-block; padding: 8px 18px; border-radius: 4px; border: none; cursor: pointer; font-size: 14px; text-decoration: none; }
        .btn-bleu   { background: #2980b9; color: #fff; }
        .btn-vert   { background: #27ae60; color: #fff; }
        .btn-rouge  { background: #e74c3c; color: #fff; }
        .btn:hover  { opacity: .85; }
        .alerte-succes { background: #d4edda; color: #155724; padding: 10px 14px; border-radius: 4px; margin-bottom: 14px; }
        .alerte-erreur { background: #f8d7da; color: #721c24; padding: 10px 14px; border-radius: 4px; margin-bottom: 14px; }
        label  { display: block; margin-top: 12px; font-weight: bold; font-size: 14px; }
        input, select { width: 100%; padding: 8px 10px; margin-top: 4px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; }
        input:focus, select:focus { outline: 2px solid #2980b9; border-color: transparent; }
    </style>
</head>
<body>
<nav>
    <div>
        <strong style="color:#fff;margin-right:24px;">📦 <?= APP_NOM ?></strong>
        <a href="<?= BASE_URL ?>/index.php">Tableau de bord</a>
        <a href="<?= BASE_URL ?>/modules/facturation/nouvelle.php">Nouvelle facture</a>
        <a href="<?= BASE_URL ?>/modules/facturation/liste.php">Factures</a>
        <a href="<?= BASE_URL ?>/modules/produits/liste.php">Produits</a>
        <?php if (($user['role'] ?? '') === 'superadmin'): ?>
            <a href="<?= BASE_URL ?>/modules/admin/utilisateurs.php">Utilisateurs</a>
        <?php endif; ?>
    </div>
    <div class="nav-user">
        👤 <?= htmlspecialchars($user['nom'] ?? '') ?> (<?= htmlspecialchars($user['role'] ?? '') ?>)
        &nbsp;|&nbsp;
        <a href="<?= BASE_URL ?>/auth/logout.php">Déconnexion</a>
    </div>
</nav>
<main>
