<?php
/**
 * Gestion des médias - BackOffice
 */

require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Médias';

// Suppression
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $media = dbFetchOne("SELECT fichier FROM medias WHERE id = ?", [$id]);
    if ($media) {
        deleteUploadedFile($media['fichier']);
        dbExecute("DELETE FROM medias WHERE id = ?", [$id]);
        setFlash('success', 'Média supprimé.');
    }
    redirect(ADMIN_URL . '/medias.php');
}

// Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['fichier']['name'])) {
    $uploadedFile = uploadImage($_FILES['fichier'], 'medias');
    if ($uploadedFile) {
        $nom = sanitize($_POST['nom'] ?? pathinfo($_FILES['fichier']['name'], PATHINFO_FILENAME));
        $alt = sanitize($_POST['alt_text'] ?? '');
        $type = 'image';
        $taille = $_FILES['fichier']['size'];

        dbExecute(
            "INSERT INTO medias (nom, fichier, alt_text, type, taille) VALUES (?, ?, ?, ?, ?)",
            [$nom, $uploadedFile, $alt, $type, $taille]
        );
        setFlash('success', 'Média uploadé avec succès.');
    } else {
        setFlash('error', 'Erreur lors de l\'upload.');
    }
    redirect(ADMIN_URL . '/medias.php');
}

$medias = dbFetchAll("SELECT * FROM medias ORDER BY date_upload DESC");

include __DIR__ . '/includes/header.php';
?>

<div class="card mb-3">
    <div class="card-header">
        <h2 class="card-title">Uploader un média</h2>
    </div>
    <div class="card-body">
        <form method="POST" action="" enctype="multipart/form-data">
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 16px; align-items: end;">
                <div class="form-group mb-0">
                    <label for="fichier">Fichier image</label>
                    <input type="file" id="fichier" name="fichier" accept="image/*" class="form-control" required>
                </div>
                <div class="form-group mb-0">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" class="form-control" placeholder="Nom du média">
                </div>
                <div class="form-group mb-0">
                    <label for="alt_text">Texte alternatif</label>
                    <input type="text" id="alt_text" name="alt_text" class="form-control" placeholder="Description">
                </div>
                <button type="submit" class="btn btn-primary">📤 Uploader</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Bibliothèque de médias</h2>
        <span class="badge badge-info"><?= count($medias) ?> fichier<?= count($medias) > 1 ? 's' : '' ?></span>
    </div>
    <div class="card-body">
        <?php if (empty($medias)): ?>
            <p class="text-muted text-center" style="padding: 40px;">Aucun média uploadé.</p>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 16px;">
                <?php foreach ($medias as $media): ?>
                    <div style="border: 1px solid var(--border-color); border-radius: var(--border-radius); overflow: hidden;">
                        <div
                            style="height: 120px; background: var(--bg-color); display: flex; align-items: center; justify-content: center; overflow: hidden;">
                            <img src="<?= UPLOADS_URL . '/' . e($media['fichier']) ?>" alt="<?= e($media['alt_text']) ?>"
                                style="max-width: 100%; max-height: 100%; object-fit: cover;">
                        </div>
                        <div style="padding: 10px;">
                            <p
                                style="font-size: 0.875rem; margin: 0 0 4px; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <?= e($media['nom']) ?>
                            </p>
                            <p style="font-size: 0.75rem; color: var(--text-light); margin: 0;">
                                <?= number_format($media['taille'] / 1024, 1) ?> Ko
                            </p>
                            <div class="d-flex gap-1 mt-1">
                                <button class="btn btn-sm btn-outline"
                                    onclick="copyToClipboard('<?= UPLOADS_URL . '/' . e($media['fichier']) ?>')"
                                    title="Copier l'URL">📋</button>
                                <a href="<?= ADMIN_URL ?>/medias.php?delete=<?= $media['id'] ?>" class="btn btn-sm btn-danger"
                                    data-confirm="Supprimer ce média ?">🗑️</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function () {
            showNotification('URL copiée !', 'success');
        });
    }
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>