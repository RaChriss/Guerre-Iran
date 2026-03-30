<?php
/**
 * Header FrontOffice
 */

$siteName = getConfig('nom_site', 'Guerre Iran');
$siteDescription = getConfig('description_site', 'Site d\'information sur la situation en Iran');

// Récupération des catégories pour le menu
$menuCategories = dbFetchAll(
    "SELECT id, nom, slug FROM categories WHERE actif = TRUE ORDER BY ordre, nom LIMIT 6"
);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e($metaDescription ?: $siteDescription) ?>">
    <?php if ($metaKeywords): ?>
        <meta name="keywords" content="<?= e($metaKeywords) ?>">
    <?php endif; ?>
    <meta name="author" content="<?= e($siteName) ?>">
    <meta name="robots" content="index, follow">

    <!-- Open Graph -->
    <meta property="og:title" content="<?= e($metaTitle ?: $siteName) ?>">
    <meta property="og:description" content="<?= e($metaDescription ?: $siteDescription) ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= e(SITE_URL . $_SERVER['REQUEST_URI']) ?>">
    <?php if (isset($ogImage)): ?>
        <meta property="og:image" content="<?= e($ogImage) ?>">
    <?php endif; ?>

    <title><?= pageTitle($metaTitle) ?></title>

    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/icons.css">
</head>

<body class="<?= e($pageClass ?? '') ?>">
    <!-- Skip to content for accessibility -->
    <a href="#main-content" class="skip-link">Aller au contenu principal</a>

    <header class="site-header">
        <div class="container">
            <div class="header-inner">
                <a href="<?= SITE_URL ?>" class="site-logo">
                    <span class="logo-text"><?= e($siteName) ?></span>
                </a>

                <button class="menu-toggle" aria-label="Menu" aria-expanded="false">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>

                <nav class="main-nav" role="navigation" aria-label="Navigation principale">
                    <ul class="nav-list">
                        <li><a href="<?= SITE_URL ?>/" class="nav-link">Accueil</a></li>
                        <li><a href="<?= SITE_URL ?>/articles.html" class="nav-link">Articles</a></li>
                        <?php foreach ($menuCategories as $cat): ?>
                            <li>
                                <a href="<?= categoryUrl($cat) ?>" class="nav-link">
                                    <?= e($cat['nom']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                        <li><a href="<?= SITE_URL ?>/chronologie.html" class="nav-link">Chronologie</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main id="main-content" class="site-main">