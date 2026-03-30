<?php
/**
 * Template Page statique
 */

// Récupération de la page
$page = dbFetchOne(
    "SELECT * FROM pages WHERE slug = ? AND actif = TRUE",
    [$slug]
);

if (!$page) {
    header("HTTP/1.0 404 Not Found");
    include TEMPLATES_PATH . '/404.php';
    return;
}

// Convertir le contenu Markdown en HTML
$contenuHtml = markdownToHtml($page['contenu']);

$metaTitle = $page['meta_titre'] ?: $page['titre'];
$metaDescription = $page['meta_description'] ?: truncate(strip_tags($contenuHtml), 160);

include INCLUDES_PATH . '/header.php';
?>

<div class="container container-narrow">
    <article class="page-content">
        <header class="page-header">
            <h1><?= e($page['titre']) ?></h1>
        </header>

        <div class="prose">
            <?= $contenuHtml ?>
        </div>
    </article>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>