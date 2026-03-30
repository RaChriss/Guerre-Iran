<?php
/**
 * Sidebar du BackOffice
 */

// Page courante pour la mise en surbrillance
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <a href="<?= ADMIN_URL ?>" class="sidebar-logo">
            <span class="logo-icon">GI</span>
            <span class="logo-text">Guerre Iran</span>
        </a>
    </div>

    <nav class="sidebar-nav">
        <ul class="nav-list">
            <li class="nav-item">
                <a href="<?= ADMIN_URL ?>/index.php"
                    class="nav-link <?= $currentPage === 'index.php' ? 'active' : '' ?>">
                    <span class="nav-icon icon icon-dashboard"></span>
                    <span class="nav-text">Tableau de bord</span>
                </a>
            </li>

            <li class="nav-section">Contenus</li>

            <li class="nav-item">
                <a href="<?= ADMIN_URL ?>/articles.php"
                    class="nav-link <?= in_array($currentPage, ['articles.php', 'article-edit.php']) ? 'active' : '' ?>">
                    <span class="nav-icon icon icon-article"></span>
                    <span class="nav-text">Articles</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="<?= ADMIN_URL ?>/categories.php"
                    class="nav-link <?= in_array($currentPage, ['categories.php', 'categorie-edit.php']) ? 'active' : '' ?>">
                    <span class="nav-icon icon icon-folder"></span>
                    <span class="nav-text">Catégories</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="<?= ADMIN_URL ?>/evenements.php"
                    class="nav-link <?= in_array($currentPage, ['evenements.php', 'evenement-edit.php']) ? 'active' : '' ?>">
                    <span class="nav-icon icon icon-timeline"></span>
                    <span class="nav-text">Chronologie</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="<?= ADMIN_URL ?>/pages.php"
                    class="nav-link <?= in_array($currentPage, ['pages.php', 'page-edit.php']) ? 'active' : '' ?>">
                    <span class="nav-icon icon icon-page"></span>
                    <span class="nav-text">Pages</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="<?= ADMIN_URL ?>/medias.php"
                    class="nav-link <?= $currentPage === 'medias.php' ? 'active' : '' ?>">
                    <span class="nav-icon icon icon-image"></span>
                    <span class="nav-text">Médias</span>
                </a>
            </li>

            <li class="nav-section">Administration</li>

            <li class="nav-item">
                <a href="<?= ADMIN_URL ?>/configuration.php"
                    class="nav-link <?= $currentPage === 'configuration.php' ? 'active' : '' ?>">
                    <span class="nav-icon icon icon-settings"></span>
                    <span class="nav-text">Configuration</span>
                </a>
            </li>

            <?php if (isAdmin()): ?>
                <li class="nav-item">
                    <a href="<?= ADMIN_URL ?>/utilisateurs.php"
                        class="nav-link <?= in_array($currentPage, ['utilisateurs.php', 'utilisateur-edit.php']) ? 'active' : '' ?>">
                        <span class="nav-icon icon icon-users"></span>
                        <span class="nav-text">Utilisateurs</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="sidebar-footer">
        <a href="<?= ADMIN_URL ?>/logout.php" class="nav-link logout-link">
            <span class="nav-icon icon icon-logout"></span>
            <span class="nav-text">Déconnexion</span>
        </a>
    </div>
</aside>