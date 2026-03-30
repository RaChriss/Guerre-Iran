<?php
/**
 * Génération du sitemap XML
 */

require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/xml; charset=utf-8');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <!-- Page d'accueil -->
    <url>
        <loc><?= SITE_URL ?>/</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>

    <!-- Page articles -->
    <url>
        <loc><?= SITE_URL ?>/articles.html</loc>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>

    <!-- Chronologie -->
    <url>
        <loc><?= SITE_URL ?>/chronologie.html</loc>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>

    <!-- Articles publiés -->
    <?php
    $articles = dbFetchAll(
        "SELECT slug, id, date_modification FROM articles WHERE statut = 'publie' ORDER BY date_publication DESC"
    );
    foreach ($articles as $article):
        ?>
        <url>
            <loc><?= SITE_URL ?>/article-<?= e($article['slug']) ?>-<?= $article['id'] ?>.html</loc>
            <lastmod><?= date('Y-m-d', strtotime($article['date_modification'])) ?></lastmod>
            <changefreq>monthly</changefreq>
            <priority>0.7</priority>
        </url>
    <?php endforeach; ?>

    <!-- Catégories -->
    <?php
    $categories = dbFetchAll("SELECT slug, id FROM categories WHERE actif = TRUE");
    foreach ($categories as $cat):
        ?>
        <url>
            <loc><?= SITE_URL ?>/categorie-<?= e($cat['slug']) ?>-<?= $cat['id'] ?>.html</loc>
            <changefreq>weekly</changefreq>
            <priority>0.6</priority>
        </url>
    <?php endforeach; ?>

    <!-- Pages statiques -->
    <?php
    $pages = dbFetchAll("SELECT slug, date_modification FROM pages WHERE actif = TRUE");
    foreach ($pages as $pg):
        ?>
        <url>
            <loc><?= SITE_URL ?>/<?= e($pg['slug']) ?>.html</loc>
            <lastmod><?= date('Y-m-d', strtotime($pg['date_modification'])) ?></lastmod>
            <changefreq>monthly</changefreq>
            <priority>0.5</priority>
        </url>
    <?php endforeach; ?>
</urlset>