<?php
/**
 * config.php — Constantes globales du système
 */

define('APP_NOM',     'UPC Facturation');
define('APP_VERSION', '1.0.0');
define('BASE_URL',    '/benda');

// Chemins absolus vers les fichiers de données
define('DATA_DIR',          __DIR__ . '/../data/');
define('FICHIER_USERS',     DATA_DIR . 'utilisateurs.json');
define('FICHIER_PRODUITS',  DATA_DIR . 'produits.json');
define('FICHIER_FACTURES',  DATA_DIR . 'factures.json');

// Devise
define('DEVISE', 'FC');
