<?php
/**
 * Template Article détail
 */

// Récupération de l'article
$article = dbFetchOne(
    "SELECT a.*, c.nom as categorie_nom, c.slug as categorie_slug, c.id as categorie_id,
            u.username as auteur_username, u.prenom as auteur_prenom, u.nom as auteur_nom
     FROM articles a
     LEFT JOIN categories c ON a.categorie_id = c.id
     LEFT JOIN administrateurs u ON a.auteur_id = u.id
     WHERE a.id = ? AND a.statut = 'publie'",
    [$id]
);

if (!$article) {
    header("HTTP/1.0 404 Not Found");
    include TEMPLATES_PATH . '/404.php';
    return;
}

// Incrémenter les vues
incrementArticleViews($article['id']);

// Articles similaires (même catégorie)
$articlesSimilaires = [];
if ($article['categorie_id']) {
    $articlesSimilaires = dbFetchAll(
        "SELECT a.*, c.nom as categorie_nom
         FROM articles a
         LEFT JOIN categories c ON a.categorie_id = c.id
         WHERE a.statut = 'publie' AND a.categorie_id = ? AND a.id != ?
         ORDER BY a.date_publication DESC
         LIMIT 3",
        [$article['categorie_id'], $article['id']]
    );
}

// Convertir le contenu Markdown en HTML
$contenuHtml = markdownToHtml($article['contenu']);

// Meta données
$metaTitle = $article['meta_titre'] ?: $article['titre'];
$metaDescription = $article['meta_description'] ?: truncate($article['chapeau'] ?: strip_tags($contenuHtml), 160);
$metaKeywords = $article['meta_keywords'];
$ogImage = $article['image_principale'] ? imageUrl($article['image_principale']) : null;

include INCLUDES_PATH . '/header.php';
?>

<article class="article-single">
    <div class="container container-narrow">
        <!-- Header de l'article -->
        <header class="article-header">
            <?php if ($article['categorie_nom']): ?>
                <a href="<?= categoryUrl(['id' => $article['categorie_id'], 'slug' => $article['categorie_slug']]) ?>"
                    class="article-category">
                    <?= e($article['categorie_nom']) ?>
                </a>
            <?php endif; ?>

            <h1><?= e($article['titre']) ?></h1>

            <?php if ($article['chapeau']): ?>
                <p class="article-lead"><?= e($article['chapeau']) ?></p>
            <?php endif; ?>

            <div class="article-meta">
                <span class="article-author">
                    Par
                    <?= e(trim($article['auteur_prenom'] . ' ' . $article['auteur_nom']) ?: $article['auteur_username'] ?: 'La rédaction') ?>
                </span>
                <time datetime="<?= date('Y-m-d', strtotime($article['date_publication'])) ?>">
                    Publié le <?= formatDate($article['date_publication'], 'd F Y') ?>
                </time>
                <span class="article-views"><?= number_format($article['vues']) ?> lectures</span>
            </div>
        </header>

        <!-- Image principale -->
        <?php if ($article['image_principale']): ?>
            <figure class="article-featured-image">
                <img src="<?= imageUrl($article['image_principale']) ?>"
                    alt="<?= e($article['alt_image'] ?: $article['titre']) ?>">
                <?php if ($article['alt_image']): ?>
                    <figcaption><?= e($article['alt_image']) ?></figcaption>
                <?php endif; ?>
            </figure>
        <?php endif; ?>

        <!-- Contenu de l'article -->
        <div class="article-content prose">
            <?= $contenuHtml ?>
        </div>

        <!-- Partage -->
        <footer class="article-footer">
            <div class="article-share">
                <span>Partager :</span>
                <a href="https://twitter.com/intent/tweet?url=<?= urlencode(articleUrl($article)) ?>&text=<?= urlencode($article['titre']) ?>"
                    target="_blank" rel="noopener noreferrer" aria-label="Partager sur Twitter">
                    Twitter
                </a>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(articleUrl($article)) ?>"
                    target="_blank" rel="noopener noreferrer" aria-label="Partager sur Facebook">
                    Facebook
                </a>
                <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?= urlencode(articleUrl($article)) ?>&title=<?= urlencode($article['titre']) ?>"
                    target="_blank" rel="noopener noreferrer" aria-label="Partager sur LinkedIn">
                    LinkedIn
                </a>
            </div>
        </footer>
    </div>

    <!-- Articles similaires -->
    <?php if (!empty($articlesSimilaires)): ?>
        <section class="related-articles" aria-labelledby="related-title">
            <div class="container">
                <h2 id="related-title">Articles similaires</h2>

                <div class="articles-grid">
                    <?php foreach ($articlesSimilaires as $similar): ?>
                        <article class="article-card">
                            <a href="<?= articleUrl($similar) ?>" class="article-card-link">
                                <div class="article-card-image">
                                    <img src="<?= imageUrl($similar['image_principale']) ?>"
                                        alt="<?= e($similar['alt_image'] ?: $similar['titre']) ?>" loading="lazy">
                                </div>
                                <div class="article-card-content">
                                    <h3 class="card-title"><?= e($similar['titre']) ?></h3>
                                    <time datetime="<?= date('Y-m-d', strtotime($similar['date_publication'])) ?>">
                                        <?= formatDate($similar['date_publication']) ?>
                                    </time>
                                </div>
                            </a>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>
</article>

<?php include INCLUDES_PATH . '/footer.php'; ?>