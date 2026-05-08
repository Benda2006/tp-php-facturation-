<?php
/**
 * session.php
 * Démarre la session et vérifie l'accès selon le rôle requis.
 * À inclure en haut de chaque page protégée.
 *
 * Usage :
 *   require_once __DIR__ . '/../auth/session.php';
 *   exigerConnexion();               // page accessible à tout utilisateur connecté
 *   exigerConnexion('superadmin');   // page réservée au superadmin
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ─────────────────────────────────────────────
// Vérifie que l'utilisateur est connecté.
// Si $role est fourni, vérifie aussi que son rôle correspond.
// Redirige vers login.php si la condition n'est pas remplie.
// ─────────────────────────────────────────────
function exigerConnexion(string $role = ''): void {
    if (empty($_SESSION['utilisateur'])) {
        header('Location: /benda/auth/login.php');
        exit;
    }

    if ($role !== '' && ($_SESSION['utilisateur']['role'] ?? '') !== $role) {
        http_response_code(403);
        exit('Accès refusé : vous n\'avez pas les droits nécessaires.');
    }
}

// ─────────────────────────────────────────────
// Retourne l'utilisateur connecté ou null
// ─────────────────────────────────────────────
function utilisateurConnecte(): ?array {
    return $_SESSION['utilisateur'] ?? null;
}

// ─────────────────────────────────────────────
// Connecte un utilisateur : vérifie email + mot de passe
// Retourne true si succès, false sinon
// ─────────────────────────────────────────────
function connecterUtilisateur(string $email, string $motDePasse): bool {
    $chemin = __DIR__ . '/../data/utilisateurs.json';
    if (!file_exists($chemin)) return false;

    $utilisateurs = json_decode(file_get_contents($chemin), true) ?? [];

    foreach ($utilisateurs as $u) {
        if ($u['email'] === $email && ($u['actif'] ?? false)) {
            if (password_verify($motDePasse, $u['mot_de_passe'])) {
                // Ne jamais stocker le hash en session
                unset($u['mot_de_passe']);
                $_SESSION['utilisateur'] = $u;
                session_regenerate_id(true); // prévenir la fixation de session
                return true;
            }
        }
    }
    return false;
}

// ─────────────────────────────────────────────
// Déconnecte l'utilisateur et détruit la session
// ─────────────────────────────────────────────
function deconnecterUtilisateur(): void {
    $_SESSION = [];
    session_destroy();
    header('Location: /benda/auth/login.php');
    exit;
}
