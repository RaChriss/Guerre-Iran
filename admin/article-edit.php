<?php
/**
 * Création/Édition d'un article - BackOffice
 */

require_once __DIR__ . '/includes/auth.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$isEdit = $id > 0;

$pageTitle = $isEdit ? 'Modifier l\'article' : 'Nouvel article';

// Récupérer l'article existant
$article = null;
if ($isEdit) {
    $article = dbFetchOne("SELECT * FROM articles WHERE id = ?", [$id]);
    if (!$article) {
        setFlash('error', 'Article introuvable.');
        redirect(ADMIN_URL . '/articles.php');
    }
}

// Catégories pour le select
$categories = dbFetchAll("SELECT id, nom FROM categories WHERE actif = TRUE ORDER BY nom");

// Valeurs par défaut
$data = [
    'titre' => $article['titre'] ?? '',
    'slug' => $article['slug'] ?? '',
    'chapeau' => $article['chapeau'] ?? '',
    'contenu' => $article['contenu'] ?? '',
    'categorie_id' => $article['categorie_id'] ?? '',
    'statut' => $article['statut'] ?? 'brouillon',
    'mise_en_avant' => $article['mise_en_avant'] ?? false,
    'meta_titre' => $article['meta_titre'] ?? '',
    'meta_description' => $article['meta_description'] ?? '',
    'meta_keywords' => $article['meta_keywords'] ?? '',
    'alt_image' => $article['alt_image'] ?? '',
    'image_principale' => $article['image_principale'] ?? ''
];

$errors = [];

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données
    $data = [
        'titre' => sanitize($_POST['titre'] ?? ''),
        'slug' => slugify($_POST['slug'] ?? $_POST['titre'] ?? ''),
        'chapeau' => sanitize($_POST['chapeau'] ?? ''),
        'contenu' => $_POST['contenu'] ?? '', // Ne pas sanitize le Markdown
        'categorie_id' => !empty($_POST['categorie_id']) ? (int) $_POST['categorie_id'] : null,
        'statut' => $_POST['statut'] ?? 'brouillon',
        'mise_en_avant' => isset($_POST['mise_en_avant']),
        'meta_titre' => sanitize($_POST['meta_titre'] ?? ''),
        'meta_description' => sanitize($_POST['meta_description'] ?? ''),
        'meta_keywords' => sanitize($_POST['meta_keywords'] ?? ''),
        'alt_image' => sanitize($_POST['alt_image'] ?? ''),
        'image_principale' => $article['image_principale'] ?? ''
    ];

    // Validation
    if (empty($data['titre'])) {
        $errors['titre'] = 'Le titre est obligatoire.';
    }

    if (empty($data['contenu'])) {
        $errors['contenu'] = 'Le contenu est obligatoire.';
    }

    if (empty($data['slug'])) {
        $data['slug'] = slugify($data['titre']);
    }

    // Vérifier l'unicité du slug
    $existingSlug = dbFetchOne(
        "SELECT id FROM articles WHERE slug = ? AND id != ?",
        [$data['slug'], $id]
    );
    if ($existingSlug) {
        $data['slug'] .= '-' . time();
    }

    // Upload de l'image
    if (!empty($_FILES['image']['name'])) {
        $uploadedImage = uploadImage($_FILES['image'], 'articles');
        if ($uploadedImage) {
            // Supprimer l'ancienne image
            if ($data['image_principale']) {
                deleteUploadedFile($data['image_principale']);
            }
            $data['image_principale'] = $uploadedImage;
        } else {
            $errors['image'] = 'Erreur lors de l\'upload de l\'image. Vérifiez le format et la taille.';
        }
    }

    // Date de publication
    $datePublication = null;
    if ($data['statut'] === 'publie') {
        if ($isEdit && $article['date_publication']) {
            $datePublication = $article['date_publication'];
        } else {
            $datePublication = date('Y-m-d H:i:s');
        }
    }

    // Enregistrement
    if (empty($errors)) {
        if ($isEdit) {
            dbExecute(
                "UPDATE articles SET
                    titre = ?, slug = ?, chapeau = ?, contenu = ?,
                    image_principale = ?, alt_image = ?, categorie_id = ?,
                    statut = ?, mise_en_avant = ?, date_publication = ?,
                    meta_titre = ?, meta_description = ?, meta_keywords = ?,
                    date_modification = CURRENT_TIMESTAMP
                 WHERE id = ?",
                [
                    $data['titre'],
                    $data['slug'],
                    $data['chapeau'],
                    $data['contenu'],
                    $data['image_principale'],
                    $data['alt_image'],
                    $data['categorie_id'],
                    $data['statut'],
                    $data['mise_en_avant'] ? 'true' : 'false',
                    $datePublication,
                    $data['meta_titre'],
                    $data['meta_description'],
                    $data['meta_keywords'],
                    $id
                ]
            );
            setFlash('success', 'Article modifié avec succès.');
        } else {
            dbExecute(
                "INSERT INTO articles (titre, slug, chapeau, contenu, image_principale, alt_image,
                    categorie_id, auteur_id, statut, mise_en_avant, date_publication,
                    meta_titre, meta_description, meta_keywords)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $data['titre'],
                    $data['slug'],
                    $data['chapeau'],
                    $data['contenu'],
                    $data['image_principale'],
                    $data['alt_image'],
                    $data['categorie_id'],
                    $_SESSION['admin_id'],
                    $data['statut'],
                    $data['mise_en_avant'] ? 'true' : 'false',
                    $datePublication,
                    $data['meta_titre'],
                    $data['meta_description'],
                    $data['meta_keywords']
                ]
            );
            $id = dbLastInsertId('articles_id_seq');
            setFlash('success', 'Article créé avec succès.');
        }

        // Invalider le cache après modification
        cacheFlush();

        redirect(ADMIN_URL . '/article-edit.php?id=' . $id);
    }
}

$csrfToken = generateCsrfToken();

include __DIR__ . '/includes/header.php';
?>

<form method="POST" action="" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

    <div class="grid-2-1-cols">
        <!-- Colonne principale -->
        <div>
            <div class="card mb-3">
                <div class="card-body">
                    <div class="form-group">
                        <label for="titre" class="required">Titre de l'article</label>
                        <input type="text" id="titre" name="titre" value="<?= e($data['titre']) ?>" class="form-control"
                            required>
                        <?php if (isset($errors['titre'])): ?>
                            <div class="form-error"><?= e($errors['titre']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="slug">Slug (URL)</label>
                        <input type="text" id="slug" name="slug" value="<?= e($data['slug']) ?>" class="form-control"
                            placeholder="Généré automatiquement">
                        <div class="form-hint">L'URL sera : /article-<em>slug</em>-<?= $id ?: 'ID' ?>.html</div>
                    </div>

                    <div class="form-group">
                        <label for="chapeau">Chapeau (résumé)</label>
                        <textarea id="chapeau" name="chapeau" class="form-control" rows="3"
                            placeholder="Brève introduction de l'article..."><?= e($data['chapeau']) ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="contenu" class="required">Contenu (Markdown)</label>
                        <textarea id="contenu" name="contenu" class="form-control" rows="20" required placeholder="# Titre

Votre contenu en Markdown...

## Sous-titre

Paragraphe avec **gras** et *italique*.

- Liste
- à puces"><?= e($data['contenu']) ?></textarea>
                        <?php if (isset($errors['contenu'])): ?>
                            <div class="form-error"><?= e($errors['contenu']) ?></div>
                        <?php endif; ?>
                        <div class="form-hint">
                            Utilisez la syntaxe Markdown : # pour les titres, ** pour le gras, * pour l'italique, - pour
                            les listes.
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEO -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Référencement (SEO)</h2>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="meta_titre">Meta titre</label>
                        <input type="text" id="meta_titre" name="meta_titre" value="<?= e($data['meta_titre']) ?>"
                            class="form-control" data-maxlength="70" placeholder="Titre pour les moteurs de recherche">
                        <div class="form-hint">Maximum 70 caractères recommandés.</div>
                    </div>

                    <div class="form-group">
                        <label for="meta_description">Meta description</label>
                        <textarea id="meta_description" name="meta_description" class="form-control" rows="2"
                            data-maxlength="160"
                            placeholder="Description pour les moteurs de recherche"><?= e($data['meta_description']) ?></textarea>
                        <div class="form-hint">Maximum 160 caractères recommandés.</div>
                    </div>

                    <div class="form-group">
                        <label for="meta_keywords">Mots-clés</label>
                        <input type="text" id="meta_keywords" name="meta_keywords"
                            value="<?= e($data['meta_keywords']) ?>" class="form-control"
                            placeholder="mot-clé1, mot-clé2, mot-clé3">
                    </div>
                </div>
            </div>
        </div>

        <!-- Colonne latérale -->
        <div>
            <!-- Publication -->
            <div class="card mb-3">
                <div class="card-header">
                    <h2 class="card-title">Publication</h2>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="statut">Statut</label>
                        <select id="statut" name="statut" class="form-control">
                            <option value="brouillon" <?= $data['statut'] === 'brouillon' ? 'selected' : '' ?>>
                                Brouillon
                            </option>
                            <option value="publie" <?= $data['statut'] === 'publie' ? 'selected' : '' ?>>
                                Publié
                            </option>
                            <option value="archive" <?= $data['statut'] === 'archive' ? 'selected' : '' ?>>
                                Archivé
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="categorie_id">Catégorie</label>
                        <select id="categorie_id" name="categorie_id" class="form-control">
                            <option value="">-- Sans catégorie --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $data['categorie_id'] == $cat['id'] ? 'selected' : '' ?>>
                                    <?= e($cat['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-check">
                            <input type="checkbox" name="mise_en_avant" <?= $data['mise_en_avant'] ? 'checked' : '' ?>>
                            <span>Mettre en avant</span>
                        </label>
                        <div class="form-hint">L'article apparaîtra en vedette sur la page d'accueil.</div>
                    </div>

                    <?php if ($isEdit && $article['date_publication']): ?>
                        <div class="form-group">
                            <small class="text-muted">
                                Publié le <?= formatDate($article['date_publication'], 'd/m/Y à H:i') ?>
                            </small>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-block">
                        <?= $isEdit ? 'Enregistrer les modifications' : 'Créer l\'article' ?>
                    </button>
                </div>
            </div>

            <!-- Image -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Image principale</h2>
                </div>
                <div class="card-body">
                    <div class="upload-zone">
                        <input type="file" name="image" accept="image/*">
                        <p>📷 Cliquez ou glissez une image ici</p>
                        <small class="text-muted">JPG, PNG, GIF, WebP - Max 10 Mo</small>
                        <?php if ($data['image_principale']): ?>
                            <div class="upload-preview mt-2">
                                <img src="<?= imageUrl($data['image_principale']) ?>" alt="Image actuelle">
                            </div>
                        <?php else: ?>
                            <div class="upload-preview"></div>
                        <?php endif; ?>
                    </div>
                    <?php if (isset($errors['image'])): ?>
                        <div class="form-error"><?= e($errors['image']) ?></div>
                    <?php endif; ?>

                    <div class="form-group mt-2">
                        <label for="alt_image">Texte alternatif (alt)</label>
                        <input type="text" id="alt_image" name="alt_image" value="<?= e($data['alt_image']) ?>"
                            class="form-control" placeholder="Description de l'image">
                        <div class="form-hint">Important pour l'accessibilité et le SEO.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3 d-flex justify-between">
        <a href="<?= ADMIN_URL ?>/articles.php" class="btn btn-outline">← Retour à la liste</a>
        <button type="submit" class="btn btn-primary">
            <?= $isEdit ? 'Enregistrer' : 'Créer' ?>
        </button>
    </div>
</form>

<?php include __DIR__ . '/includes/footer.php'; ?>