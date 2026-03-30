<?php
/**
 * Tableau de bord du BackOffice
 */

require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Tableau de bord';

// Statistiques
$stats = [
    'articles' => dbCount('articles'),
    'articles_publies' => dbCount('articles', "statut = 'publie'"),
    'categories' => dbCount('categories', "actif = TRUE"),
    'evenements' => dbCount('evenements', "actif = TRUE"),
    'vues_total' => dbFetchOne("SELECT COALESCE(SUM(vues), 0) as total FROM articles")['total']
];

// Derniers articles
$derniersArticles = dbFetchAll(
    "SELECT a.*, c.nom as categorie_nom
     FROM articles a
     LEFT JOIN categories c ON a.categorie_id = c.id
     ORDER BY a.date_creation DESC
     LIMIT 5"
);

// Derniers événements
$derniersEvenements = dbFetchAll(
    "SELECT * FROM evenements
     ORDER BY date_creation DESC
     LIMIT 5"
);

include __DIR__ . '/includes/header.php';
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">📰</div>
        <div class="stat-value"><?= $stats['articles'] ?></div>
        <div class="stat-label">Articles au total</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">✅</div>
        <div class="stat-value"><?= $stats['articles_publies'] ?></div>
        <div class="stat-label">Articles publiés</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">📁</div>
        <div class="stat-value"><?= $stats['categories'] ?></div>
        <div class="stat-label">Catégories</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">👁️</div>
        <div class="stat-value"><?= number_format($stats['vues_total'], 0, ',', ' ') ?></div>
        <div class="stat-label">Vues totales</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
    <!-- Derniers Articles -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Derniers articles</h2>
            <a href="<?= ADMIN_URL ?>/article-edit.php" class="btn btn-sm btn-primary">+ Nouveau</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($derniersArticles)): ?>
                <p style="padding: 20px; text-align: center; color: var(--text-light);">
                    Aucun article pour le moment.
                </p>
            <?php else: ?>
                <table class="table">
                    <tbody>
                        <?php foreach ($derniersArticles as $article): ?>
                            <tr>
                                <td>
                                    <a href="<?= ADMIN_URL ?>/article-edit.php?id=<?= $article['id'] ?>">
                                        <strong><?= e($article['titre']) ?></strong>
                                    </a>
                                    <br>
                                    <small class="text-muted">
                                        <?= e($article['categorie_nom'] ?? 'Sans catégorie') ?> •
                                        <?= formatDateRelative($article['date_creation']) ?>
                                    </small>
                                </td>
                                <td style="width: 100px; text-align: right;">
                                    <?php if ($article['statut'] === 'publie'): ?>
                                        <span class="badge badge-success">Publié</span>
                                    <?php elseif ($article['statut'] === 'brouillon'): ?>
                                        <span class="badge badge-warning">Brouillon</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Archivé</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <div class="card-footer">
            <a href="<?= ADMIN_URL ?>/articles.php">Voir tous les articles →</a>
        </div>
    </div>

    <!-- Derniers Événements -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Chronologie récente</h2>
            <a href="<?= ADMIN_URL ?>/evenement-edit.php" class="btn btn-sm btn-primary">+ Nouveau</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($derniersEvenements)): ?>
                <p style="padding: 20px; text-align: center; color: var(--text-light);">
                    Aucun événement pour le moment.
                </p>
            <?php else: ?>
                <table class="table">
                    <tbody>
                        <?php foreach ($derniersEvenements as $evenement): ?>
                            <tr>
                                <td>
                                    <a href="<?= ADMIN_URL ?>/evenement-edit.php?id=<?= $evenement['id'] ?>">
                                        <strong><?= e($evenement['titre']) ?></strong>
                                    </a>
                                    <br>
                                    <small class="text-muted">
                                        <?= formatDate($evenement['date_evenement'], 'd/m/Y') ?>
                                    </small>
                                </td>
                                <td style="width: 80px; text-align: right;">
                                    <?php if ($evenement['actif']): ?>
                                        <span class="badge badge-success">Actif</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Inactif</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <div class="card-footer">
            <a href="<?= ADMIN_URL ?>/evenements.php">Voir tous les événements →</a>
        </div>
    </div>
</div>

<!-- Actions rapides -->
<div class="card mt-3">
    <div class="card-header">
        <h2 class="card-title">Actions rapides</h2>
    </div>
    <div class="card-body">
        <div style="display: flex; gap: 16px; flex-wrap: wrap;">
            <a href="<?= ADMIN_URL ?>/article-edit.php" class="btn btn-primary">
                📝 Nouvel article
            </a>
            <a href="<?= ADMIN_URL ?>/evenement-edit.php" class="btn btn-secondary">
                📅 Nouvel événement
            </a>
            <a href="<?= ADMIN_URL ?>/medias.php" class="btn btn-outline">
                🖼️ Gérer les médias
            </a>
            <a href="<?= ADMIN_URL ?>/configuration.php" class="btn btn-outline">
                ⚙️ Configuration
            </a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>