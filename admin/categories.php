<?php
/**
 * Liste des catégories - BackOffice
 */

require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Catégories';

// Suppression
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $categorie = dbFetchOne("SELECT image FROM categories WHERE id = ?", [$id]);

    if ($categorie) {
        if ($categorie['image']) {
            deleteUploadedFile($categorie['image']);
        }
        dbExecute("DELETE FROM categories WHERE id = ?", [$id]);
        setFlash('success', 'Catégorie supprimée avec succès.');
    }
    redirect(ADMIN_URL . '/categories');
}

// Récupération des catégories avec compte d'articles
$categories = dbFetchAll(
    "SELECT c.*, COUNT(a.id) as nb_articles
     FROM categories c
     LEFT JOIN articles a ON c.id = a.categorie_id
     GROUP BY c.id
     ORDER BY c.ordre, c.nom"
);

include __DIR__ . '/includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Liste des catégories</h2>
        <a href="<?= ADMIN_URL ?>/categorie/nouveau" class="btn btn-primary">
            + Nouvelle catégorie
        </a>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th class="col-order">Ordre</th>
                    <th>Nom</th>
                    <th>Slug</th>
                    <th>Articles</th>
                    <th>Statut</th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="6" class="empty-message-lg">
                            <p class="text-muted">Aucune catégorie.</p>
                            <a href="<?= ADMIN_URL ?>/categorie/nouveau" class="btn btn-primary mt-2">
                                Créer une catégorie
                            </a>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($categories as $categorie): ?>
                        <tr>
                            <td><?= $categorie['ordre'] ?></td>
                            <td>
                                <a href="<?= ADMIN_URL ?>/categorie/<?= $categorie['id'] ?>">
                                    <strong><?= e($categorie['nom']) ?></strong>
                                </a>
                            </td>
                            <td><code><?= e($categorie['slug']) ?></code></td>
                            <td><?= $categorie['nb_articles'] ?></td>
                            <td>
                                <?php if ($categorie['actif']): ?>
                                    <span class="badge badge-success">Active</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="<?= ADMIN_URL ?>/categorie/<?= $categorie['id'] ?>"
                                        class="btn btn-sm btn-outline">✏️</a>
                                    <a href="<?= ADMIN_URL ?>/categories?delete=<?= $categorie['id'] ?>"
                                        class="btn btn-sm btn-danger"
                                        data-confirm="Supprimer cette catégorie ? Les articles associés ne seront pas supprimés.">🗑️</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>