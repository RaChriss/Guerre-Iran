<?php
/**
 * Configuration du site - BackOffice
 */

require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Configuration';

// Sauvegarde
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $configs = [
        'nom_site' => sanitize($_POST['nom_site'] ?? ''),
        'description_site' => sanitize($_POST['description_site'] ?? ''),
        'email_contact' => sanitize($_POST['email_contact'] ?? ''),
        'articles_par_page' => (int) ($_POST['articles_par_page'] ?? 10),
        'facebook' => sanitize($_POST['facebook'] ?? ''),
        'twitter' => sanitize($_POST['twitter'] ?? ''),
        'instagram' => sanitize($_POST['instagram'] ?? '')
    ];

    foreach ($configs as $cle => $valeur) {
        dbExecute(
            "INSERT INTO configuration (cle, valeur) VALUES (?, ?)
             ON CONFLICT (cle) DO UPDATE SET valeur = EXCLUDED.valeur",
            [$cle, $valeur]
        );
    }

    // Invalider le cache de configuration
    cacheInvalidateConfig();

    setFlash('success', 'Configuration enregistrée.');
    redirect(ADMIN_URL . '/configuration.php');
}

// Récupération des valeurs
$config = [];
$rows = dbFetchAll("SELECT cle, valeur FROM configuration");
foreach ($rows as $row) {
    $config[$row['cle']] = $row['valeur'];
}

include __DIR__ . '/includes/header.php';
?>

<form method="POST" action="">
    <div class="card mb-3">
        <div class="card-header">
            <h2 class="card-title">Informations générales</h2>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label for="nom_site">Nom du site</label>
                <input type="text" id="nom_site" name="nom_site" value="<?= e($config['nom_site'] ?? '') ?>"
                    class="form-control">
            </div>

            <div class="form-group">
                <label for="description_site">Description du site</label>
                <textarea id="description_site" name="description_site" class="form-control"
                    rows="3"><?= e($config['description_site'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label for="email_contact">Email de contact</label>
                <input type="email" id="email_contact" name="email_contact"
                    value="<?= e($config['email_contact'] ?? '') ?>" class="form-control">
            </div>

            <div class="form-group">
                <label for="articles_par_page">Articles par page</label>
                <input type="number" id="articles_par_page" name="articles_par_page"
                    value="<?= e($config['articles_par_page'] ?? 10) ?>" class="form-control form-control-sm-width"
                    min="1" max="50">
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <h2 class="card-title">Réseaux sociaux</h2>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label for="facebook">Facebook</label>
                <input type="url" id="facebook" name="facebook" value="<?= e($config['facebook'] ?? '') ?>"
                    class="form-control" placeholder="https://facebook.com/...">
            </div>

            <div class="form-group">
                <label for="twitter">Twitter / X</label>
                <input type="url" id="twitter" name="twitter" value="<?= e($config['twitter'] ?? '') ?>"
                    class="form-control" placeholder="https://twitter.com/...">
            </div>

            <div class="form-group">
                <label for="instagram">Instagram</label>
                <input type="url" id="instagram" name="instagram" value="<?= e($config['instagram'] ?? '') ?>"
                    class="form-control" placeholder="https://instagram.com/...">
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">💾 Enregistrer la configuration</button>
</form>

<?php include __DIR__ . '/includes/footer.php'; ?>