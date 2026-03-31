<?php
/**
 * Template Page 404
 */

$metaTitle = 'Page non trouvée';
$metaDescription = 'La page que vous recherchez n\'existe pas ou a été déplacée.';

include INCLUDES_PATH . '/header.php';
?>

<div class="container">
    <div class="error-page">
        <div class="error-content">
            <h1>404</h1>
            <h2>Page non trouvée</h2>
            <p>La page que vous recherchez n'existe pas ou a été déplacée.</p>

            <div class="error-actions">
                <a href="<?= SITE_URL ?>/" class="btn btn-primary">Retour à l'accueil</a>
                <a href="<?= SITE_URL ?>/articles" class="btn btn-outline">Voir les articles</a>
            </div>
        </div>
    </div>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>