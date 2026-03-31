<?php
/**
 * Liste des pages statiques - BackOffice
 */

require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Pages';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    dbExecute("DELETE FROM pages WHERE id = ?", [$id]);
    setFlash('success', 'Page supprimée.');
    redirect(ADMIN_URL . '/pages.php');
}

$pages = dbFetchAll("SELECT * FROM pages ORDER BY titre");

include __DIR__ . '/includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Pages statiques</h2>
        <a href="<?= ADMIN_URL ?>/page-edit.php" class="btn btn-primary">+ Nouvelle page</a>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>URL</th>
                    <th>Statut</th>
                    <th>Modifiée le</th>
                    <th class="col-actions-sm">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pages)): ?>
                    <tr>
                        <td colspan="5" class="empty-message-lg">
                            <p class="text-muted">Aucune page.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pages as $pg): ?>
                        <tr>
                            <td>
                                <a href="<?= ADMIN_URL ?>/page-edit.php?id=<?= $pg['id'] ?>">
                                    <strong><?= e($pg['titre']) ?></strong>
                                </a>
                            </td>
                            <td><code>/<?= e($pg['slug']) ?>.html</code></td>
                            <td>
                                <?php if ($pg['actif']): ?>
                                    <span class="badge badge-success">Active</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td><?= formatDate($pg['date_modification']) ?></td>
                            <td>
                                <div class="actions">
                                    <a href="<?= ADMIN_URL ?>/page-edit.php?id=<?= $pg['id'] ?>"
                                        class="btn btn-sm btn-outline">✏️</a>
                                    <a href="<?= ADMIN_URL ?>/pages.php?delete=<?= $pg['id'] ?>" class="btn btn-sm btn-danger"
                                        data-confirm="Supprimer cette page ?">🗑️</a>
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