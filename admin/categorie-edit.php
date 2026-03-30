<?php
/**
 * Création/Édition d'une catégorie - BackOffice
 */

require_once __DIR__ . '/includes/auth.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$isEdit = $id > 0;

$pageTitle = $isEdit ? 'Modifier la catégorie' : 'Nouvelle catégorie';

$categorie = null;
if ($isEdit) {
    $categorie = dbFetchOne("SELECT * FROM categories WHERE id = ?", [$id]);
    if (!$categorie) {
        setFlash('error', 'Catégorie introuvable.');
        redirect(ADMIN_URL . '/categories.php');
    }
}

$data = [
    'nom' => $categorie['nom'] ?? '',
    'slug' => $categorie['slug'] ?? '',
    'description' => $categorie['description'] ?? '',
    'ordre' => $categorie['ordre'] ?? 0,
    'actif' => $categorie['actif'] ?? true,
    'image' => $categorie['image'] ?? ''
];

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nom' => sanitize($_POST['nom'] ?? ''),
        'slug' => slugify($_POST['slug'] ?? $_POST['nom'] ?? ''),
        'description' => sanitize($_POST['description'] ?? ''),
        'ordre' => (int) ($_POST['ordre'] ?? 0),
        'actif' => isset($_POST['actif']),
        'image' => $categorie['image'] ?? ''
    ];

    if (empty($data['nom'])) {
        $errors['nom'] = 'Le nom est obligatoire.';
    }

    if (empty($data['slug'])) {
        $data['slug'] = slugify($data['nom']);
    }

    // Vérifier l'unicité du slug
    $existingSlug = dbFetchOne(
        "SELECT id FROM categories WHERE slug = ? AND id != ?",
        [$data['slug'], $id]
    );
    if ($existingSlug) {
        $data['slug'] .= '-' . time();
    }

    // Upload d'image
    if (!empty($_FILES['image']['name'])) {
        $uploadedImage = uploadImage($_FILES['image'], 'categories');
        if ($uploadedImage) {
            if ($data['image']) {
                deleteUploadedFile($data['image']);
            }
            $data['image'] = $uploadedImage;
        }
    }

    if (empty($errors)) {
        if ($isEdit) {
            dbExecute(
                "UPDATE categories SET nom = ?, slug = ?, description = ?, image = ?, ordre = ?, actif = ?
                 WHERE id = ?",
                [$data['nom'], $data['slug'], $data['description'], $data['image'], $data['ordre'], $data['actif'], $id]
            );
            setFlash('success', 'Catégorie modifiée avec succès.');
        } else {
            dbExecute(
                "INSERT INTO categories (nom, slug, description, image, ordre, actif)
                 VALUES (?, ?, ?, ?, ?, ?)",
                [$data['nom'], $data['slug'], $data['description'], $data['image'], $data['ordre'], $data['actif']]
            );
            setFlash('success', 'Catégorie créée avec succès.');
        }
        redirect(ADMIN_URL . '/categories.php');
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nom" class="required">Nom de la catégorie</label>
                <input type="text" id="nom" name="nom" value="<?= e($data['nom']) ?>" class="form-control" required>
                <?php if (isset($errors['nom'])): ?>
                    <div class="form-error"><?= e($errors['nom']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="slug">Slug</label>
                <input type="text" id="slug" name="slug" value="<?= e($data['slug']) ?>" class="form-control">
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control"
                    rows="3"><?= e($data['description']) ?></textarea>
            </div>

            <div class="form-group">
                <label for="ordre">Ordre d'affichage</label>
                <input type="number" id="ordre" name="ordre" value="<?= $data['ordre'] ?>" class="form-control"
                    style="max-width: 100px;">
            </div>

            <div class="form-group">
                <label>Image</label>
                <div class="upload-zone">
                    <input type="file" name="image" accept="image/*">
                    <p>📷 Cliquez ou glissez une image</p>
                    <?php if ($data['image']): ?>
                        <div class="upload-preview">
                            <img src="<?= imageUrl($data['image']) ?>" alt="Image">
                        </div>
                    <?php else: ?>
                        <div class="upload-preview"></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label class="form-check">
                    <input type="checkbox" name="actif" <?= $data['actif'] ? 'checked' : '' ?>>
                    <span>Catégorie active</span>
                </label>
            </div>

            <div class="d-flex justify-between mt-3">
                <a href="<?= ADMIN_URL ?>/categories.php" class="btn btn-outline">← Retour</a>
                <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Enregistrer' : 'Créer' ?></button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>