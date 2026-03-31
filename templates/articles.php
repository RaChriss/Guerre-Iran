<?php
/**
 * Template Liste des articles
 */

$perPage = (int) getConfig('articles_par_page', 10);
$offset = ($currentPage - 1) * $perPage;

// Filtrage par catégorie si présent
$categorieFilter = isset($_GET['cat']) ? (int) $_GET['cat'] : 0;

$whereClause = "WHERE a.statut = 'publie'";
$params = [];

if ($categorieFilter) {
    $whereClause .= " AND a.categorie_id = ?";
    $params[] = $categorieFilter;
}

// Clé de cache unique basée sur les filtres et pagination
$cacheKey = "articles:list:cat{$categorieFilter}:page{$currentPage}";

// Total articles (avec cache)
$total = dbFetchOneCached(
    "SELECT COUNT(*) as count FROM articles a {$whereClause}",
    $params,
    CACHE_TTL_SHORT,
    "articles:count:cat{$categorieFilter}"
)['count'];

// Récupération des articles (avec cache)
$articles = dbFetchAllCached(
    "SELECT a.*, c.nom as categorie_nom, c.slug as categorie_slug, c.id as categorie_id
     FROM articles a
     LEFT JOIN categories c ON a.categorie_id = c.id
     {$whereClause}
     ORDER BY a.date_publication DESC
     LIMIT {$perPage} OFFSET {$offset}",
    $params,
    CACHE_TTL_SHORT,
    $cacheKey
);

// Catégories pour le filtre (cache long)
$categories = dbFetchAllCached(
    "SELECT c.*, COUNT(a.id) as nb_articles
     FROM categories c
     LEFT JOIN articles a ON c.id = a.categorie_id AND a.statut = 'publie'
     WHERE c.actif = TRUE
     GROUP BY c.id
     ORDER BY c.nom",
    [],
    CACHE_TTL,
    'articles:sidebar_categories'
);

// Pagination
$pagination = paginate($total, $perPage, $currentPage, SITE_URL . '/articles/page/{page}');

$metaTitle = 'Tous les articles';
$metaDescription = 'Découvrez tous nos articles sur la situation en Iran : actualités, analyses, témoignages et plus encore.';

include INCLUDES_PATH . '/header.php';
?>

<div class="container">
    <header class="page-header">
        <h1>Articles</h1>
        <p class="page-description"><?= $total ?> article<?= $total > 1 ? 's' : '' ?>
            disponible<?= $total > 1 ? 's' : '' ?></p>
    </header>

    <div class="page-layout">
        <!-- Contenu principal -->
        <div class="page-content">
            <?php if (empty($articles)): ?>
                <div class="empty-state">
                    <p>Aucun article disponible pour le moment.</p>
                </div>
            <?php else: ?>
                <div class="articles-list">
                    <?php foreach ($articles as $article): ?>
                        <article class="article-card article-card-horizontal">
                            <a href="<?= articleUrl($article) ?>" class="article-card-link">
                                <div class="article-card-image">
                                    <img src="<?= imageUrl($article['image_principale']) ?>"
                                        alt="<?= e($article['alt_image'] ?: $article['titre']) ?>" loading="lazy">
                                </div>
                                <div class="article-card-content">
                                    <?php if ($article['categorie_nom']): ?>
                                        <span class="card-category"><?= e($article['categorie_nom']) ?></span>
                                    <?php endif; ?>
                                    <h2 class="card-title"><?= e($article['titre']) ?></h2>
                                    <p class="card-excerpt">
                                        <?= e(truncate($article['chapeau'] ?: strip_tags(markdownToHtml($article['contenu'])), 150)) ?>
                                    </p>
                                    <div class="card-meta">
                                        <time datetime="<?= date('Y-m-d', strtotime($article['date_publication'])) ?>">
                                            <?= formatDate($article['date_publication']) ?>
                                        </time>
                                        <span class="card-views"><?= number_format($article['vues']) ?> vues</span>
                                    </div>
                                </div>
                            </a>
                        </article>
                    <?php endforeach; ?>
                </div>

                <?php if ($pagination['total'] > 1): ?>
                    <nav class="pagination" aria-label="Pagination des articles">
                        <?php if ($pagination['has_prev']): ?>
                            <a href="<?= $pagination['prev_url'] ?>" class="pagination-prev">← Précédent</a>
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
                            <a href="<?= $pagination['next_url'] ?>" class="pagination-next">Suivant →</a>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <aside class="page-sidebar">
            <div class="sidebar-widget">
                <h2 class="widget-title">Catégories</h2>
                <ul class="category-list">
                    <li>
                        <a href="<?= SITE_URL ?>/articles" class="<?= !$categorieFilter ? 'active' : '' ?>">
                            Toutes les catégories
                        </a>
                    </li>
                    <?php foreach ($categories as $cat): ?>
                        <li>
                            <a href="<?= categoryUrl($cat) ?>"
                                class="<?= $categorieFilter == $cat['id'] ? 'active' : '' ?>">
                                <?= e($cat['nom']) ?>
                                <span class="count">(<?= $cat['nb_articles'] ?>)</span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </aside>
    </div>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>