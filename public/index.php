<?php
/**
 * Routeur principal - FrontOffice
 * Gère toutes les requêtes via l'URL rewriting
 */

require_once __DIR__ . '/../includes/functions.php';

// Récupération des paramètres de la requête
$page = $_GET['page'] ?? 'home';
$slug = $_GET['slug'] ?? '';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$currentPage = max(1, (int) ($_GET['p'] ?? 1));

// Variables globales pour les templates
$metaTitle = '';
$metaDescription = '';
$metaKeywords = '';
$pageClass = '';

// Routage
switch ($page) {
    case 'home':
        $pageClass = 'page-home';
        include TEMPLATES_PATH . '/home.php';
        break;

    case 'articles':
        $pageClass = 'page-articles';
        include TEMPLATES_PATH . '/articles.php';
        break;

    case 'article':
        if (!$id) {
            header("HTTP/1.0 404 Not Found");
            include TEMPLATES_PATH . '/404.php';
            break;
        }
        $pageClass = 'page-article';
        include TEMPLATES_PATH . '/article-single.php';
        break;

    case 'categorie':
        if (!$id) {
            header("HTTP/1.0 404 Not Found");
            include TEMPLATES_PATH . '/404.php';
            break;
        }
        $pageClass = 'page-categorie';
        include TEMPLATES_PATH . '/categorie.php';
        break;

    case 'chronologie':
        $pageClass = 'page-chronologie';
        include TEMPLATES_PATH . '/chronologie.php';
        break;

    case 'static':
        if (!$slug) {
            header("HTTP/1.0 404 Not Found");
            include TEMPLATES_PATH . '/404.php';
            break;
        }
        $pageClass = 'page-static';
        include TEMPLATES_PATH . '/page.php';
        break;

    case '404':
    default:
        header("HTTP/1.0 404 Not Found");
        $pageClass = 'page-404';
        include TEMPLATES_PATH . '/404.php';
        break;
}
