<?php
/**
 * Header FrontOffice
 */

$siteName = getConfig('nom_site', 'Guerre Iran');
$siteDescription = getConfig('description_site', 'Site d\'information sur la situation en Iran');

// Récupération des catégories pour le menu (avec cache)
$menuCategories = dbFetchAllCached(
    "SELECT id, nom, slug FROM categories WHERE actif = TRUE ORDER BY ordre, nom LIMIT 6",
    [],
    CACHE_TTL_LONG,
    'menu:categories'
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

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&family=Open+Sans:wght@400;600&display=swap">
    <link
        href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&family=Open+Sans:wght@400;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/icons.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>

<body class="<?= e($pageClass ?? '') ?>">
    <!-- Skip to content for accessibility -->
    <a href="#main-content" class="skip-link">Aller au contenu principal</a>

    <header class="site-header">
        <div class="container">
            <div class="header-inner">
                <a href="<?= SITE_URL ?>" class="site-logo" aria-label="Accueil <?= e($siteName) ?>">
                    <span class="logo-mark">GI</span>
                    <span class="logo-text"><?= e($siteName) ?></span>
                </a>

                <button class="menu-toggle" aria-label="Menu" aria-expanded="false" aria-controls="main-navigation">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>

                <nav class="main-nav" id="main-navigation" role="navigation" aria-label="Navigation principale">
                    <ul class="nav-list">
                        <li class="nav-item"><a href="<?= SITE_URL ?>/" class="nav-link">Accueil</a></li>
                        <li class="nav-item"><a href="<?= SITE_URL ?>/articles" class="nav-link">Articles</a></li>
                        <li class="nav-item nav-dropdown">
                            <button class="nav-link nav-dropdown-toggle" type="button" aria-expanded="false"
                                aria-controls="nav-categories-menu">
                                Catégories
                            </button>
                            <ul class="nav-dropdown-menu" id="nav-categories-menu" role="menu"
                                aria-label="Catégories d'articles">
                                <?php foreach ($menuCategories as $cat): ?>
                                    <li role="none">
                                        <a role="menuitem" href="<?= categoryUrl($cat) ?>" class="nav-dropdown-item">
                                            <?= e($cat['nom']) ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                        <li class="nav-item"><a href="<?= SITE_URL ?>/chronologie" class="nav-link">Chronologie</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
        <div class="nav-backdrop" aria-hidden="true"></div>
    </header>

    <main id="main-content" class="site-main">