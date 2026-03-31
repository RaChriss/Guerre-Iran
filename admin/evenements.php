<?php
/**
 * Liste des événements (chronologie) - BackOffice
 */

require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Chronologie';

// Suppression
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $evenement = dbFetchOne("SELECT image FROM evenements WHERE id = ?", [$id]);
    if ($evenement) {
        if ($evenement['image'])
            deleteUploadedFile($evenement['image']);
        dbExecute("DELETE FROM evenements WHERE id = ?", [$id]);
        setFlash('success', 'Événement supprimé.');
    }
    redirect(ADMIN_URL . '/evenements.php');
}

$evenements = dbFetchAll(
    "SELECT * FROM evenements ORDER BY date_evenement DESC"
);

include __DIR__ . '/includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Chronologie des événements</h2>
        <a href="<?= ADMIN_URL ?>/evenement-edit.php" class="btn btn-primary">+ Nouvel événement</a>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Titre</th>
                    <th>Source</th>
                    <th>Statut</th>
                    <th class="col-actions-sm">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($evenements)): ?>
                    <tr>
                        <td colspan="5" class="empty-message-lg">
                            <p class="text-muted">Aucun événement.</p>
                            <a href="<?= ADMIN_URL ?>/evenement-edit.php" class="btn btn-primary mt-2">Ajouter un
                                événement</a>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($evenements as $evt): ?>
                        <tr>
                            <td><strong><?= formatDate($evt['date_evenement'], 'd/m/Y') ?></strong></td>
                            <td>
                                <a href="<?= ADMIN_URL ?>/evenement-edit.php?id=<?= $evt['id'] ?>">
                                    <?= e($evt['titre']) ?>
                                </a>
                            </td>
                            <td><?= e($evt['source'] ?? '-') ?></td>
                            <td>
                                <?php if ($evt['actif']): ?>
                                    <span class="badge badge-success">Actif</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Inactif</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="<?= ADMIN_URL ?>/evenement-edit.php?id=<?= $evt['id'] ?>"
                                        class="btn btn-sm btn-outline">✏️</a>
                                    <a href="<?= ADMIN_URL ?>/evenements.php?delete=<?= $evt['id'] ?>"
                                        class="btn btn-sm btn-danger" data-confirm="Supprimer cet événement ?">🗑️</a>
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