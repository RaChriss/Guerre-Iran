<?php
/**
 * Gestion des utilisateurs - BackOffice (Admin seulement)
 */

require_once __DIR__ . '/includes/auth.php';

// Vérifier les droits admin
if (!isAdmin()) {
    setFlash('error', 'Accès non autorisé.');
    redirect(ADMIN_URL);
}

$pageTitle = 'Utilisateurs';

// Suppression
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    if ($id !== $_SESSION['admin_id']) {
        dbExecute("DELETE FROM administrateurs WHERE id = ?", [$id]);
        setFlash('success', 'Utilisateur supprimé.');
    } else {
        setFlash('error', 'Vous ne pouvez pas vous supprimer vous-même.');
    }
    redirect(ADMIN_URL . '/utilisateurs');
}

$utilisateurs = dbFetchAll("SELECT * FROM administrateurs ORDER BY date_creation DESC");

include __DIR__ . '/includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Liste des utilisateurs</h2>
        <a href="<?= ADMIN_URL ?>/utilisateur/nouveau" class="btn btn-primary">+ Nouvel utilisateur</a>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Utilisateur</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Statut</th>
                    <th>Dernière connexion</th>
                    <th class="col-actions-sm">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($utilisateurs as $user): ?>
                    <tr>
                        <td>
                            <strong><?= e($user['username']) ?></strong>
                            <br>
                            <small class="text-muted"><?= e(trim($user['prenom'] . ' ' . $user['nom'])) ?></small>
                        </td>
                        <td><?= e($user['email']) ?></td>
                        <td>
                            <?php if ($user['role'] === 'admin'): ?>
                                <span class="badge badge-info">Admin</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Éditeur</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($user['actif']): ?>
                                <span class="badge badge-success">Actif</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Inactif</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= $user['derniere_connexion'] ? formatDateRelative($user['derniere_connexion']) : '-' ?>
                        </td>
                        <td>
                            <div class="actions">
                                <a href="<?= ADMIN_URL ?>/utilisateur/<?= $user['id'] ?>"
                                    class="btn btn-sm btn-outline">✏️</a>
                                <?php if ($user['id'] !== $_SESSION['admin_id']): ?>
                                    <a href="<?= ADMIN_URL ?>/utilisateurs?delete=<?= $user['id'] ?>"
                                        class="btn btn-sm btn-danger" data-confirm="Supprimer cet utilisateur ?">🗑️</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>