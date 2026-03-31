<?php
/**
 * Création/Édition d'une page - BackOffice
 */

require_once __DIR__ . '/includes/auth.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$isEdit = $id > 0;
$pageTitle = $isEdit ? 'Modifier la page' : 'Nouvelle page';

$page = null;
if ($isEdit) {
    $page = dbFetchOne("SELECT * FROM pages WHERE id = ?", [$id]);
    if (!$page) {
        setFlash('error', 'Page introuvable.');
        redirect(ADMIN_URL . '/pages');
    }
}

$data = [
    'titre' => $page['titre'] ?? '',
    'slug' => $page['slug'] ?? '',
    'contenu' => $page['contenu'] ?? '',
    'meta_titre' => $page['meta_titre'] ?? '',
    'meta_description' => $page['meta_description'] ?? '',
    'actif' => $page['actif'] ?? true
];

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'titre' => sanitize($_POST['titre'] ?? ''),
        'slug' => slugify($_POST['slug'] ?? $_POST['titre'] ?? ''),
        'contenu' => $_POST['contenu'] ?? '',
        'meta_titre' => sanitize($_POST['meta_titre'] ?? ''),
        'meta_description' => sanitize($_POST['meta_description'] ?? ''),
        'actif' => isset($_POST['actif'])
    ];

    if (empty($data['titre']))
        $errors['titre'] = 'Le titre est obligatoire.';
    if (empty($data['contenu']))
        $errors['contenu'] = 'Le contenu est obligatoire.';
    if (empty($data['slug']))
        $data['slug'] = slugify($data['titre']);

    $existingSlug = dbFetchOne("SELECT id FROM pages WHERE slug = ? AND id != ?", [$data['slug'], $id]);
    if ($existingSlug)
        $data['slug'] .= '-' . time();

    if (empty($errors)) {
        if ($isEdit) {
            dbExecute(
                "UPDATE pages SET titre = ?, slug = ?, contenu = ?, meta_titre = ?, meta_description = ?, actif = ?, date_modification = CURRENT_TIMESTAMP WHERE id = ?",
                [$data['titre'], $data['slug'], $data['contenu'], $data['meta_titre'], $data['meta_description'], $data['actif'], $id]
            );
            setFlash('success', 'Page modifiée.');
        } else {
            dbExecute(
                "INSERT INTO pages (titre, slug, contenu, meta_titre, meta_description, actif) VALUES (?, ?, ?, ?, ?, ?)",
                [$data['titre'], $data['slug'], $data['contenu'], $data['meta_titre'], $data['meta_description'], $data['actif']]
            );
            setFlash('success', 'Page créée.');
        }
        redirect(ADMIN_URL . '/pages');
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="">
            <div class="form-group">
                <label for="titre" class="required">Titre</label>
                <input type="text" id="titre" name="titre" value="<?= e($data['titre']) ?>" class="form-control"
                    required>
                <?php if (isset($errors['titre'])): ?>
                    <div class="form-error"><?= e($errors['titre']) ?></div><?php endif; ?>
            </div>

            <div class="form-group">
                <label for="slug">Slug (URL)</label>
                <input type="text" id="slug" name="slug" value="<?= e($data['slug']) ?>" class="form-control">
                <div class="form-hint">URL : /<?= e($data['slug'] ?: 'slug') ?></div>
            </div>

            <div class="form-group">
                <label for="contenu" class="required">Contenu (Markdown)</label>
                <textarea id="contenu" name="contenu" class="form-control" rows="15"
                    required><?= e($data['contenu']) ?></textarea>
                <?php if (isset($errors['contenu'])): ?>
                    <div class="form-error"><?= e($errors['contenu']) ?></div><?php endif; ?>
            </div>

            <div class="form-group">
                <label for="meta_titre">Meta titre</label>
                <input type="text" id="meta_titre" name="meta_titre" value="<?= e($data['meta_titre']) ?>"
                    class="form-control" data-maxlength="70">
            </div>

            <div class="form-group">
                <label for="meta_description">Meta description</label>
                <textarea id="meta_description" name="meta_description" class="form-control" rows="2"
                    data-maxlength="160"><?= e($data['meta_description']) ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-check">
                    <input type="checkbox" name="actif" <?= $data['actif'] ? 'checked' : '' ?>>
                    <span>Page active</span>
                </label>
            </div>

            <div class="d-flex justify-between mt-3">
                <a href="<?= ADMIN_URL ?>/pages" class="btn btn-outline">← Retour</a>
                <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Enregistrer' : 'Créer' ?></button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>