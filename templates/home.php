<?php
/**
 * Template Page d'accueil
 */

// Articles mis en avant (cache court car mis à jour fréquemment)
$articlesEnAvant = dbFetchAllCached(
    "SELECT a.*, c.nom as categorie_nom, c.slug as categorie_slug
     FROM articles a
     LEFT JOIN categories c ON a.categorie_id = c.id
     WHERE a.statut = 'publie' AND a.mise_en_avant = TRUE
     ORDER BY a.date_publication DESC
     LIMIT 3",
    [],
    CACHE_TTL_SHORT,
    'home:articles_en_avant'
);

// Derniers articles (cache court)
$derniersArticles = dbFetchAllCached(
    "SELECT a.*, c.nom as categorie_nom, c.slug as categorie_slug
     FROM articles a
     LEFT JOIN categories c ON a.categorie_id = c.id
     WHERE a.statut = 'publie'
     ORDER BY a.date_publication DESC
     LIMIT 6",
    [],
    CACHE_TTL_SHORT,
    'home:derniers_articles'
);

// Catégories actives (cache long car rarement modifié)
$categories = dbFetchAllCached(
    "SELECT c.*, COUNT(a.id) as nb_articles
     FROM categories c
     LEFT JOIN articles a ON c.id = a.categorie_id AND a.statut = 'publie'
     WHERE c.actif = TRUE
     GROUP BY c.id
     ORDER BY c.ordre, c.nom",
    [],
    CACHE_TTL,
    'home:categories'
);

// Derniers événements (cache moyen)
$evenements = dbFetchAllCached(
    "SELECT * FROM evenements WHERE actif = TRUE ORDER BY date_evenement DESC LIMIT 4",
    [],
    CACHE_TTL_SHORT,
    'home:evenements'
);

$metaTitle = '';
$metaDescription = getConfig('description_site', 'Site d\'information sur la situation en Iran');

include INCLUDES_PATH . '/header.php';
?>

<div class="container">
    <!-- Hero Section avec articles en avant -->
    <?php if (!empty($articlesEnAvant)): ?>
        <section class="hero-section" aria-labelledby="hero-title">
            <h1 id="hero-title" class="visually-hidden">À la une</h1>
            <div class="hero-grid">
                <?php $first = true;
                foreach ($articlesEnAvant as $article): ?>
                    <article class="hero-card <?= $first ? 'hero-main' : 'hero-secondary' ?>">
                        <a href="<?= articleUrl($article) ?>" class="hero-card-link">
                            <div class="hero-card-image">
                                <img src="<?= imageUrl($article['image_principale']) ?>"
                                    alt="<?= e($article['alt_image'] ?: $article['titre']) ?>"
                                    loading="<?= $first ? 'eager' : 'lazy' ?>">
                            </div>
                            <div class="hero-card-content">
                                <?php if ($article['categorie_nom']): ?>
                                    <span class="card-category"><?= e($article['categorie_nom']) ?></span>
                                <?php endif; ?>
                                <h2 class="card-title"><?= e($article['titre']) ?></h2>
                                <?php if ($first && $article['chapeau']): ?>
                                    <p class="card-excerpt"><?= e(truncate($article['chapeau'], 150)) ?></p>
                                <?php endif; ?>
                                <time class="card-date"
                                    datetime="<?= date('Y-m-d', strtotime($article['date_publication'])) ?>">
                                    <?= formatDate($article['date_publication']) ?>
                                </time>
                            </div>
                        </a>
                    </article>
                    <?php $first = false; endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- Derniers articles -->
    <section class="section" aria-labelledby="latest-articles">
        <div class="section-header">
            <h2 id="latest-articles">Derniers articles</h2>
            <a href="<?= SITE_URL ?>/articles" class="section-link">Voir tous les articles →</a>
        </div>

        <div class="articles-grid">
            <?php foreach ($derniersArticles as $article): ?>
                <article class="article-card">
                    <a href="<?= articleUrl($article) ?>" class="article-card-link">
                        <div class="article-card-image">
                            <img src="<?= imageUrl($article['image_principale']) ?>"
                                alt="<?= e($article['alt_image'] ?: $article['titre']) ?>" loading="lazy">
                        </div>
                        <div class="article-card-content">
                            <?php if ($article['categorie_nom']): ?>
                                <span class="card-category"><?= e($article['categorie_nom']) ?></span>
                            <?php endif; ?>
                            <h3 class="card-title"><?= e($article['titre']) ?></h3>
                            <p class="card-excerpt">
                                <?= e(truncate($article['chapeau'] ?: strip_tags(markdownToHtml($article['contenu'])), 100)) ?>
                            </p>
                            <time class="card-date"
                                datetime="<?= date('Y-m-d', strtotime($article['date_publication'])) ?>">
                                <?= formatDate($article['date_publication']) ?>
                            </time>
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Catégories -->
    <?php if (!empty($categories)): ?>
        <section class="section section-categories" aria-labelledby="categories-title">
            <h2 id="categories-title">Explorer par catégorie</h2>

            <div class="categories-grid">
                <?php foreach ($categories as $cat): ?>
                    <a href="<?= categoryUrl($cat) ?>" class="category-card">
                        <h3 class="category-name"><?= e($cat['nom']) ?></h3>
                        <p class="category-count"><?= $cat['nb_articles'] ?> article<?= $cat['nb_articles'] > 1 ? 's' : '' ?>
                        </p>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- Chronologie -->
    <?php if (!empty($evenements)): ?>
        <section class="section section-timeline" aria-labelledby="timeline-title">
            <div class="section-header">
                <h2 id="timeline-title">Chronologie récente</h2>
                <a href="<?= SITE_URL ?>/chronologie" class="section-link">Voir la chronologie complète →</a>
            </div>

            <div class="timeline-preview">
                <?php foreach ($evenements as $evt): ?>
                    <div class="timeline-item">
                        <div class="timeline-date">
                            <span class="date-day"><?= date('d', strtotime($evt['date_evenement'])) ?></span>
                            <span class="date-month"><?= date('M Y', strtotime($evt['date_evenement'])) ?></span>
                        </div>
                        <div class="timeline-content">
                            <h3><?= e($evt['titre']) ?></h3>
                            <?php if ($evt['description']): ?>
                                <p><?= e(truncate(strip_tags(markdownToHtml($evt['description'])), 100)) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>