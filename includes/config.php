<?php
/**
 * Configuration générale du site
 * Guerre Iran - Site d'information
 */

// Mode debug (à désactiver en production)
define('DEBUG_MODE', true);

// Configuration de l'affichage des erreurs
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Configuration de la base de données
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'guerre_iran');
define('DB_USER', getenv('DB_USER') ?: 'admin');
define('DB_PASS', getenv('DB_PASS') ?: 'admin123');
define('DB_PORT', getenv('DB_PORT') ?: '5432');

// Chemins du site
define('BASE_PATH', dirname(__DIR__));
define('INCLUDES_PATH', BASE_PATH . '/includes');
define('TEMPLATES_PATH', BASE_PATH . '/templates');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('UPLOADS_PATH', PUBLIC_PATH . '/uploads');
define('ADMIN_PATH', BASE_PATH . '/admin');

// URL du site (à adapter selon l'environnement)
define('SITE_URL', 'http://localhost:8080');
define('ADMIN_URL', SITE_URL . '/admin');
define('UPLOADS_URL', SITE_URL . '/uploads');

// Configuration de la pagination
define('ARTICLES_PER_PAGE', 10);
define('EVENTS_PER_PAGE', 20);

// Configuration des uploads
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10 Mo
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_IMAGE_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Configuration de la session
define('SESSION_LIFETIME', 3600 * 24); // 24 heures

// Fuseau horaire
date_default_timezone_set('Europe/Paris');

// Encodage
mb_internal_encoding('UTF-8');

// Démarrage de la session sécurisé
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => SESSION_LIFETIME,
        'cookie_httponly' => true,
        'cookie_secure' => false, // Mettre à true en HTTPS
        'cookie_samesite' => 'Lax'
    ]);
}
