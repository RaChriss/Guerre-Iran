<?php
/**
 * Fonctions utilitaires
 * Guerre Iran - Site d'information
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/Parsedown.php';

/**
 * Instance globale de Parsedown
 */
function parsedown(): Parsedown
{
    static $instance = null;
    if ($instance === null) {
        $instance = new Parsedown();
        $instance->setSafeMode(true);
    }
    return $instance;
}

/**
 * Convertir du Markdown en HTML
 */
function markdownToHtml(string $markdown): string
{
    return parsedown()->text($markdown);
}

/**
 * Générer un slug à partir d'une chaîne
 */
function slugify(string $text): string
{
    // Remplacer les caractères non-alphanumériques par des tirets
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    // Translittération
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    // Supprimer les caractères indésirables
    $text = preg_replace('~[^-\w]+~', '', $text);
    // Trim
    $text = trim($text, '-');
    // Supprimer les tirets en double
    $text = preg_replace('~-+~', '-', $text);
    // Mettre en minuscules
    $text = strtolower($text);

    return $text ?: 'article';
}

/**
 * Échapper les caractères HTML
 */
function e(string $string): string
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Tronquer un texte à une longueur donnée
 */
function truncate(string $text, int $length = 150, string $suffix = '...'): string
{
    $text = strip_tags($text);
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . $suffix;
}

/**
 * Formater une date en français
 */
function formatDate(string $date, string $format = 'd/m/Y'): string
{
    $timestamp = strtotime($date);
    return date($format, $timestamp);
}

/**
 * Formater une date en format relatif
 */
function formatDateRelative(string $date): string
{
    $timestamp = strtotime($date);
    $diff = time() - $timestamp;

    if ($diff < 60) {
        return 'À l\'instant';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return "Il y a {$minutes} minute" . ($minutes > 1 ? 's' : '');
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return "Il y a {$hours} heure" . ($hours > 1 ? 's' : '');
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return "Il y a {$days} jour" . ($days > 1 ? 's' : '');
    } else {
        return formatDate($date);
    }
}

/**
 * Générer les liens de pagination
 */
function paginate(int $total, int $perPage, int $currentPage, string $baseUrl): array
{
    $totalPages = max(1, ceil($total / $perPage));
    $currentPage = max(1, min($currentPage, $totalPages));

    $pagination = [
        'current' => $currentPage,
        'total' => $totalPages,
        'per_page' => $perPage,
        'total_items' => $total,
        'has_prev' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages,
        'prev_url' => $currentPage > 1 ? str_replace('{page}', $currentPage - 1, $baseUrl) : null,
        'next_url' => $currentPage < $totalPages ? str_replace('{page}', $currentPage + 1, $baseUrl) : null,
        'pages' => []
    ];

    // Générer les numéros de page
    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $currentPage + 2);

    for ($i = $start; $i <= $end; $i++) {
        $pagination['pages'][] = [
            'number' => $i,
            'url' => str_replace('{page}', $i, $baseUrl),
            'active' => $i === $currentPage
        ];
    }

    return $pagination;
}

/**
 * Rediriger vers une URL
 */
function redirect(string $url, int $status = 302): void
{
    header("Location: {$url}", true, $status);
    exit;
}

/**
 * Définir un message flash
 */
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'][$type] = $message;
}

/**
 * Obtenir et supprimer un message flash
 */
function getFlash(string $type): ?string
{
    $message = $_SESSION['flash'][$type] ?? null;
    unset($_SESSION['flash'][$type]);
    return $message;
}

/**
 * Vérifier si un message flash existe
 */
function hasFlash(string $type): bool
{
    return isset($_SESSION['flash'][$type]);
}

/**
 * Générer un token CSRF
 */
function generateCsrfToken(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Vérifier un token CSRF
 */
function verifyCsrfToken(string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Valider et nettoyer une entrée
 */
function sanitize(string $input): string
{
    return trim(strip_tags($input));
}

/**
 * Valider un email
 */
function isValidEmail(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Upload d'un fichier image
 */
function uploadImage(array $file, string $destination): ?string
{
    // Vérifier les erreurs
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    // Vérifier le type MIME
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
        return null;
    }

    // Vérifier la taille
    if ($file['size'] > MAX_FILE_SIZE) {
        return null;
    }

    // Générer un nom unique
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . strtolower($extension);

    // Créer le répertoire si nécessaire
    $fullPath = UPLOADS_PATH . '/' . $destination;
    if (!is_dir($fullPath)) {
        mkdir($fullPath, 0755, true);
    }

    // Déplacer le fichier
    $targetPath = $fullPath . '/' . $filename;
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $destination . '/' . $filename;
    }

    return null;
}

/**
 * Supprimer un fichier uploadé
 */
function deleteUploadedFile(string $path): bool
{
    $fullPath = UPLOADS_PATH . '/' . $path;
    if (file_exists($fullPath)) {
        return unlink($fullPath);
    }
    return false;
}

/**
 * Obtenir la configuration du site (avec cache)
 */
function getConfig(string $key, $default = null)
{
    static $config = null;

    if ($config === null) {
        $config = cacheRemember('config:all', function () {
            $rows = dbFetchAll("SELECT cle, valeur FROM configuration");
            $configData = [];
            foreach ($rows as $row) {
                $configData[$row['cle']] = $row['valeur'];
            }
            return $configData;
        }, CACHE_TTL_LONG);
    }

    return $config[$key] ?? $default;
}

/**
 * Générer le titre de la page
 */
function pageTitle(string $title = ''): string
{
    $siteName = getConfig('nom_site', 'Guerre Iran');
    if ($title) {
        return e($title) . ' | ' . e($siteName);
    }
    return e($siteName);
}

/**
 * Générer l'URL d'un article
 */
function articleUrl(array $article): string
{
    return SITE_URL . '/article-' . $article['slug'] . '-' . $article['id'] . '.html';
}

/**
 * Générer l'URL d'une catégorie
 */
function categoryUrl(array $category): string
{
    return SITE_URL . '/categorie-' . $category['slug'] . '-' . $category['id'] . '.html';
}

/**
 * Générer l'URL d'une image uploadée
 */
function imageUrl(?string $path, string $default = '/assets/images/placeholder.jpg'): string
{
    if ($path && file_exists(UPLOADS_PATH . '/' . $path)) {
        return UPLOADS_URL . '/' . $path;
    }
    return SITE_URL . $default;
}

/**
 * Incrémenter le compteur de vues d'un article
 */
function incrementArticleViews(int $articleId): void
{
    $viewedKey = "viewed_article_{$articleId}";
    if (!isset($_SESSION[$viewedKey])) {
        dbExecute(
            "UPDATE articles SET vues = vues + 1 WHERE id = ?",
            [$articleId]
        );
        $_SESSION[$viewedKey] = true;
    }
}

/**
 * Vérifier si l'utilisateur est connecté (admin)
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['admin_id']) && $_SESSION['admin_id'] > 0;
}

/**
 * Vérifier si l'utilisateur est admin
 */
function isAdmin(): bool
{
    return isLoggedIn() && ($_SESSION['admin_role'] ?? '') === 'admin';
}

/**
 * Obtenir l'utilisateur connecté
 */
function getCurrentUser(): ?array
{
    if (!isLoggedIn()) {
        return null;
    }
    return dbFetchOne(
        "SELECT * FROM administrateurs WHERE id = ? AND actif = TRUE",
        [$_SESSION['admin_id']]
    );
}
