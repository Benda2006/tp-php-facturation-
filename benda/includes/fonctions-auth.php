<?php
/**
 * fonctions-auth.php
 * Gestion des comptes utilisateurs (ajout, liste, suppression).
 */

require_once __DIR__ . '/../config/config.php';

function chargerUtilisateurs(): array {
    if (!file_exists(FICHIER_USERS)) return [];
    $data = json_decode(file_get_contents(FICHIER_USERS), true);
    return is_array($data) ? $data : [];
}

// ─────────────────────────────────────────────
// Crée un nouvel utilisateur
// Rôles acceptés : superadmin, caissier
// ─────────────────────────────────────────────
function creerUtilisateur(string $nom, string $email, string $mdp, string $role): bool {
    $rolesValides = ['superadmin', 'caissier'];
    if (!in_array($role, $rolesValides, true)) return false;

    $email = filter_var(trim($email), FILTER_VALIDATE_EMAIL);
    if (!$email || empty($nom) || strlen($mdp) < 6) return false;

    $utilisateurs = chargerUtilisateurs();

    // Vérifier unicité de l'email
    foreach ($utilisateurs as $u) {
        if ($u['email'] === $email) return false;
    }

    $utilisateurs[] = [
        'id'          => 'USR-' . strtoupper(uniqid()),
        'nom'         => htmlspecialchars(trim($nom), ENT_QUOTES),
        'email'       => $email,
        'mot_de_passe'=> password_hash($mdp, PASSWORD_BCRYPT),
        'role'        => $role,
        'actif'       => true,
        'cree_le'     => date('Y-m-d\TH:i:s'),
    ];

    return _ecrireUsersJSON($utilisateurs);
}

// ─────────────────────────────────────────────
// Active ou désactive un compte
// ─────────────────────────────────────────────
function toggleUtilisateur(string $id): bool {
    $utilisateurs = chargerUtilisateurs();
    foreach ($utilisateurs as &$u) {
        if ($u['id'] === $id) {
            $u['actif'] = !$u['actif'];
            return _ecrireUsersJSON($utilisateurs);
        }
    }
    return false;
}

// Écriture sécurisée du fichier utilisateurs
function _ecrireUsersJSON(array $data): bool {
    $fp = fopen(FICHIER_USERS, 'c+');
    if (!$fp) return false;
    if (flock($fp, LOCK_EX)) {
        ftruncate($fp, 0); rewind($fp);
        fwrite($fp, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        flock($fp, LOCK_UN);
    }
    fclose($fp);
    return true;
}
