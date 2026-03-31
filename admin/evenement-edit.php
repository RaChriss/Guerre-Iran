<?php
/**
 * Création/Édition d'un événement - BackOffice
 */

require_once __DIR__ . '/includes/auth.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$isEdit = $id > 0;
$pageTitle = $isEdit ? 'Modifier l\'événement' : 'Nouvel événement';

$evenement = null;
if ($isEdit) {
    $evenement = dbFetchOne("SELECT * FROM evenements WHERE id = ?", [$id]);
    if (!$evenement) {
        setFlash('error', 'Événement introuvable.');
        redirect(ADMIN_URL . '/evenements.php');
    }
}

$data = [
    'titre' => $evenement['titre'] ?? '',
    'description' => $evenement['description'] ?? '',
    'date_evenement' => $evenement['date_evenement'] ?? date('Y-m-d'),
    'source' => $evenement['source'] ?? '',
    'alt_image' => $evenement['alt_image'] ?? '',
    'actif' => $evenement['actif'] ?? true,
    'image' => $evenement['image'] ?? ''
];

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'titre' => sanitize($_POST['titre'] ?? ''),
        'description' => $_POST['description'] ?? '',
        'date_evenement' => $_POST['date_evenement'] ?? date('Y-m-d'),
        'source' => sanitize($_POST['source'] ?? ''),
        'alt_image' => sanitize($_POST['alt_image'] ?? ''),
        'actif' => isset($_POST['actif']),
        'image' => $evenement['image'] ?? ''
    ];

    if (empty($data['titre']))
        $errors['titre'] = 'Le titre est obligatoire.';
    if (empty($data['date_evenement']))
        $errors['date_evenement'] = 'La date est obligatoire.';

    if (!empty($_FILES['image']['name'])) {
        $uploadedImage = uploadImage($_FILES['image'], 'evenements');
        if ($uploadedImage) {
            if ($data['image'])
                deleteUploadedFile($data['image']);
            $data['image'] = $uploadedImage;
        }
    }

    if (empty($errors)) {
        if ($isEdit) {
            dbExecute(
                "UPDATE evenements SET titre = ?, description = ?, date_evenement = ?, source = ?, image = ?, alt_image = ?, actif = ? WHERE id = ?",
                [$data['titre'], $data['description'], $data['date_evenement'], $data['source'], $data['image'], $data['alt_image'], $data['actif'], $id]
            );
            setFlash('success', 'Événement modifié.');
        } else {
            dbExecute(
                "INSERT INTO evenements (titre, description, date_evenement, source, image, alt_image, actif) VALUES (?, ?, ?, ?, ?, ?, ?)",
                [$data['titre'], $data['description'], $data['date_evenement'], $data['source'], $data['image'], $data['alt_image'], $data['actif']]
            );
            setFlash('success', 'Événement créé.');
        }

        // Invalider le cache après modification
        cacheFlush();

        redirect(ADMIN_URL . '/evenements.php');
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label for="titre" class="required">Titre de l'événement</label>
                <input type="text" id="titre" name="titre" value="<?= e($data['titre']) ?>" class="form-control"
                    required>
                <?php if (isset($errors['titre'])): ?>
                    <div class="form-error"><?= e($errors['titre']) ?></div><?php endif; ?>
            </div>

            <div class="form-group">
                <label for="date_evenement" class="required">Date</label>
                <input type="date" id="date_evenement" name="date_evenement" value="<?= e($data['date_evenement']) ?>"
                    class="form-control form-control-xl-width" required>
                <?php if (isset($errors['date_evenement'])): ?>
                    <div class="form-error"><?= e($errors['date_evenement']) ?></div><?php endif; ?>
            </div>

            <div class="form-group">
                <label for="description">Description (Markdown)</label>
                <textarea id="description" name="description" class="form-control"
                    rows="6"><?= e($data['description']) ?></textarea>
            </div>

            <div class="form-group">
                <label for="source">Source</label>
                <input type="text" id="source" name="source" value="<?= e($data['source']) ?>" class="form-control"
                    placeholder="Ex: AFP, Reuters, ONU...">
            </div>

            <div class="form-group">
                <label>Image</label>
                <div class="upload-zone">
                    <input type="file" name="image" accept="image/*">
                    <p>📷 Cliquez ou glissez une image</p>
                    <?php if ($data['image']): ?>
                        <div class="upload-preview"><img src="<?= imageUrl($data['image']) ?>" alt=""></div>
                    <?php else: ?>
                        <div class="upload-preview"></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="alt_image">Texte alternatif</label>
                <input type="text" id="alt_image" name="alt_image" value="<?= e($data['alt_image']) ?>"
                    class="form-control">
            </div>

            <div class="form-group">
                <label class="form-check">
                    <input type="checkbox" name="actif" <?= $data['actif'] ? 'checked' : '' ?>>
                    <span>Événement actif</span>
                </label>
            </div>

            <div class="d-flex justify-between mt-3">
                <a href="<?= ADMIN_URL ?>/evenements.php" class="btn btn-outline">← Retour</a>
                <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Enregistrer' : 'Créer' ?></button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>