<?php
/**
 * Template Catégorie
 */

// Récupération de la catégorie (avec cache long)
$categorie = dbFetchOneCached(
    "SELECT * FROM categories WHERE id = ? AND actif = TRUE",
    [$id],
    CACHE_TTL_LONG,
    "categorie:detail:{$id}"
);

if (!$categorie) {
    header("HTTP/1.0 404 Not Found");
    include TEMPLATES_PATH . '/404.php';
    return;
}

$perPage = (int) getConfig('articles_par_page', 10);
$offset = ($currentPage - 1) * $perPage;

// Total articles (avec cache)
$total = dbFetchOneCached(
    "SELECT COUNT(*) as count FROM articles WHERE categorie_id = ? AND statut = 'publie'",
    [$id],
    CACHE_TTL_SHORT,
    "categorie:count:{$id}"
)['count'];

// Articles de la catégorie (avec cache)
$articles = dbFetchAllCached(
    "SELECT a.*, c.nom as categorie_nom
     FROM articles a
     LEFT JOIN categories c ON a.categorie_id = c.id
     WHERE a.categorie_id = ? AND a.statut = 'publie'
     ORDER BY a.date_publication DESC
     LIMIT {$perPage} OFFSET {$offset}",
    [$id],
    CACHE_TTL_SHORT,
    "categorie:articles:{$id}:page{$currentPage}"
);

// Pagination
$pagination = paginate($total, $perPage, $currentPage, SITE_URL . '/categorie-' . $categorie['slug'] . '-' . $id . '/page-{page}.html');

$metaTitle = $categorie['nom'];
$metaDescription = $categorie['description'] ?: 'Découvrez tous les articles de la catégorie ' . $categorie['nom'];

include INCLUDES_PATH . '/header.php';
?>

<div class="container">
    <header class="page-header">
        <nav class="breadcrumb" aria-label="Fil d'Ariane">
            <a href="<?= SITE_URL ?>/">Accueil</a>
            <span>&gt;</span>
            <a href="<?= SITE_URL ?>/articles.html">Articles</a>
            <span>&gt;</span>
            <span aria-current="page"><?= e($categorie['nom']) ?></span>
        </nav>

        <h1><?= e($categorie['nom']) ?></h1>

        <?php if ($categorie['description']): ?>
            <p class="page-description"><?= e($categorie['description']) ?></p>
        <?php endif; ?>

        <p class="page-count"><?= $total ?> article<?= $total > 1 ? 's' : '' ?></p>
    </header>

    <?php if (empty($articles)): ?>
        <div class="empty-state">
            <p>Aucun article dans cette catégorie pour le moment.</p>
            <a href="<?= SITE_URL ?>/articles.html" class="btn btn-primary">Voir tous les articles</a>
        </div>
    <?php else: ?>
        <div class="articles-grid">
            <?php foreach ($articles as $article): ?>
                <article class="article-card">
                    <a href="<?= articleUrl($article) ?>" class="article-card-link">
                        <div class="article-card-image">
                            <img src="<?= imageUrl($article['image_principale']) ?>"
                                alt="<?= e($article['alt_image'] ?: $article['titre']) ?>" loading="lazy">
                        </div>
                        <div class="article-card-content">
                            <h2 class="card-title"><?= e($article['titre']) ?></h2>
                            <p class="card-excerpt">
                                <?= e(truncate($article['chapeau'] ?: strip_tags(markdownToHtml($article['contenu'])), 120)) ?>
                            </p>
                            <time datetime="<?= date('Y-m-d', strtotime($article['date_publication'])) ?>">
                                <?= formatDate($article['date_publication']) ?>
                            </time>
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if ($pagination['total'] > 1): ?>
            <nav class="pagination" aria-label="Pagination">
                <?php if ($pagination['has_prev']): ?>
                    <a href="<?= $pagination['prev_url'] ?>">← Précédent</a>
                <?php endif; ?>

                <div class="pagination-numbers">
                    <?php foreach ($pagination['pages'] as $p): ?>
                        <?php if ($p['active']): ?>
                            <span class="pagination-current" aria-current="page"><?= $p['number'] ?></span>
                        <?php else: ?>
                            <a href="<?= $p['url'] ?>"><?= $p['number'] ?></a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <?php if ($pagination['has_next']): ?>
                    <a href="<?= $pagination['next_url'] ?>">Suivant →</a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>