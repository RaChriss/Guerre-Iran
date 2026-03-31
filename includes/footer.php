<?php
/**
 * Footer FrontOffice
 */

$siteName = getConfig('nom_site', 'Guerre Iran');
$facebook = getConfig('facebook', '');
$twitter = getConfig('twitter', '');
$instagram = getConfig('instagram', '');
?>
</main>

<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-section">
                <h2 class="footer-title"><?= e($siteName) ?></h2>
                <p class="footer-description">
                    <?= e(getConfig('description_site', 'Site d\'information sur la situation en Iran')) ?>
                </p>
            </div>

            <div class="footer-section">
                <h2 class="footer-title">Navigation</h2>
                <ul class="footer-links">
                    <li><a href="<?= SITE_URL ?>/">Accueil</a></li>
                    <li><a href="<?= SITE_URL ?>/articles">Articles</a></li>
                    <li><a href="<?= SITE_URL ?>/chronologie">Chronologie</a></li>
                    <li><a href="<?= SITE_URL ?>/a-propos">À propos</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h2 class="footer-title">Informations</h2>
                <ul class="footer-links">
                    <li><a href="<?= SITE_URL ?>/contact">Contact</a></li>
                    <li><a href="<?= SITE_URL ?>/mentions-legales">Mentions légales</a></li>
                </ul>
            </div>

            <?php if ($facebook || $twitter || $instagram): ?>
                <div class="footer-section">
                    <h2 class="footer-title">Suivez-nous</h2>
                    <div class="social-links">
                        <?php if ($facebook): ?>
                            <a href="<?= e($facebook) ?>" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                                <span class="social-icon">FB</span>
                            </a>
                        <?php endif; ?>
                        <?php if ($twitter): ?>
                            <a href="<?= e($twitter) ?>" target="_blank" rel="noopener noreferrer" aria-label="Twitter">
                                <span class="social-icon">TW</span>
                            </a>
                        <?php endif; ?>
                        <?php if ($instagram): ?>
                            <a href="<?= e($instagram) ?>" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                                <span class="social-icon">IG</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> <?= e($siteName) ?>. Tous droits réservés.</p>
        </div>
    </div>
</footer>

<script src="<?= SITE_URL ?>/assets/js/main.js"></script>
</body>

</html>