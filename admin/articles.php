<?php
/**
 * Liste des articles - BackOffice
 */

require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Articles';

// Suppression d'un article
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $article = dbFetchOne("SELECT image_principale FROM articles WHERE id = ?", [$id]);

    if ($article) {
        // Supprimer l'image si elle existe
        if ($article['image_principale']) {
            deleteUploadedFile($article['image_principale']);
        }

        dbExecute("DELETE FROM articles WHERE id = ?", [$id]);
        setFlash('success', 'Article supprimé avec succès.');
    }

    redirect(ADMIN_URL . '/articles.php');
}

// Pagination
$page = max(1, (int) ($_GET['p'] ?? 1));
$perPage = 15;
$offset = ($page - 1) * $perPage;

// Filtres
$statut = $_GET['statut'] ?? '';
$categorie = $_GET['categorie'] ?? '';
$search = trim($_GET['q'] ?? '');

// Construction de la requête
$where = [];
$params = [];

if ($statut) {
    $where[] = "a.statut = ?";
    $params[] = $statut;
}

if ($categorie) {
    $where[] = "a.categorie_id = ?";
    $params[] = (int) $categorie;
}

if ($search) {
    $where[] = "(a.titre ILIKE ? OR a.chapeau ILIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Total pour pagination
$total = dbFetchOne(
    "SELECT COUNT(*) as count FROM articles a {$whereClause}",
    $params
)['count'];

// Récupération des articles
$articles = dbFetchAll(
    "SELECT a.*, c.nom as categorie_nom, u.username as auteur_username
     FROM articles a
     LEFT JOIN categories c ON a.categorie_id = c.id
     LEFT JOIN administrateurs u ON a.auteur_id = u.id
     {$whereClause}
     ORDER BY a.date_creation DESC
     LIMIT {$perPage} OFFSET {$offset}",
    $params
);

// Catégories pour le filtre
$categories = dbFetchAll("SELECT id, nom FROM categories WHERE actif = TRUE ORDER BY nom");

// Pagination
$pagination = paginate($total, $perPage, $page, ADMIN_URL . '/articles.php?p={page}' .
    ($statut ? "&statut={$statut}" : '') .
    ($categorie ? "&categorie={$categorie}" : '') .
    ($search ? "&q=" . urlencode($search) : ''));

include __DIR__ . '/includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <div class="d-flex align-center gap-2">
            <h2 class="card-title mb-0">Liste des articles</h2>
            <span class="badge badge-info"><?= $total ?> article<?= $total > 1 ? 's' : '' ?></span>
        </div>
        <a href="<?= ADMIN_URL ?>/article-edit.php" class="btn btn-primary">
            + Nouvel article
        </a>
    </div>

    <!-- Filtres -->
    <div class="card-body card-filters">
        <form method="GET" action="" class="d-flex gap-2 flex-wrap-gap">
            <input type="text" name="q" placeholder="Rechercher..." value="<?= e($search) ?>" class="form-control form-control-xl-width">

            <select name="statut" class="form-control form-control-md-width">
                <option value="">Tous les statuts</option>
                <option value="publie" <?= $statut === 'publie' ? 'selected' : '' ?>>Publié</option>
                <option value="brouillon" <?= $statut === 'brouillon' ? 'selected' : '' ?>>Brouillon</option>
                <option value="archive" <?= $statut === 'archive' ? 'selected' : '' ?>>Archivé</option>
            </select>

            <select name="categorie" class="form-control form-control-lg-width">
                <option value="">Toutes les catégories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $categorie == $cat['id'] ? 'selected' : '' ?>>
                        <?= e($cat['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="btn btn-secondary">Filtrer</button>

            <?php if ($search || $statut || $categorie): ?>
                <a href="<?= ADMIN_URL ?>/articles.php" class="btn btn-outline">Réinitialiser</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th class="col-id">ID</th>
                    <th>Titre</th>
                    <th>Catégorie</th>
                    <th>Statut</th>
                    <th>Vues</th>
                    <th>Date</th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($articles)): ?>
                    <tr>
                        <td colspan="7" class="empty-message-lg">
                            <p class="text-muted">Aucun article trouvé.</p>
                            <a href="<?= ADMIN_URL ?>/article-edit.php" class="btn btn-primary mt-2">
                                Créer un article
                            </a>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($articles as $article): ?>
                        <tr>
                            <td><?= $article['id'] ?></td>
                            <td>
                                <a href="<?= ADMIN_URL ?>/article-edit.php?id=<?= $article['id'] ?>">
                                    <strong><?= e($article['titre']) ?></strong>
                                </a>
                                <?php if ($article['mise_en_avant']): ?>
                                    <span title="Mis en avant">⭐</span>
                                <?php endif; ?>
                                <br>
                                <small class="text-muted">
                                    Par <?= e($article['auteur_username'] ?? 'Anonyme') ?>
                                </small>
                            </td>
                            <td><?= e($article['categorie_nom'] ?? '-') ?></td>
                            <td>
                                <?php if ($article['statut'] === 'publie'): ?>
                                    <span class="badge badge-success">Publié</span>
                                <?php elseif ($article['statut'] === 'brouillon'): ?>
                                    <span class="badge badge-warning">Brouillon</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Archivé</span>
                                <?php endif; ?>
                            </td>
                            <td><?= number_format($article['vues'], 0, ',', ' ') ?></td>
                            <td>
                                <small><?= formatDate($article['date_creation']) ?></small>
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="<?= ADMIN_URL ?>/article-edit.php?id=<?= $article['id'] ?>"
                                        class="btn btn-sm btn-outline" title="Modifier">
                                        ✏️
                                    </a>
                                    <?php if ($article['statut'] === 'publie'): ?>
                                        <a href="<?= articleUrl($article) ?>" target="_blank" class="btn btn-sm btn-outline"
                                            title="Voir">
                                            👁️
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?= ADMIN_URL ?>/articles.php?delete=<?= $article['id'] ?>"
                                        class="btn btn-sm btn-danger"
                                        data-confirm="Êtes-vous sûr de vouloir supprimer cet article ?" title="Supprimer">
                                        🗑️
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pagination['total'] > 1): ?>
        <div class="card-footer">
            <div class="pagination">
                <?php if ($pagination['has_prev']): ?>
                    <a href="<?= $pagination['prev_url'] ?>">← Précédent</a>
                <?php endif; ?>

                <?php foreach ($pagination['pages'] as $p): ?>
                    <?php if ($p['active']): ?>
                        <span class="active"><?= $p['number'] ?></span>
                    <?php else: ?>
                        <a href="<?= $p['url'] ?>"><?= $p['number'] ?></a>
                    <?php endif; ?>
                <?php endforeach; ?>

                <?php if ($pagination['has_next']): ?>
                    <a href="<?= $pagination['next_url'] ?>">Suivant →</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>