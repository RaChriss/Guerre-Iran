<?php
/**
 * Header du BackOffice
 */

// Définir le titre de la page si non défini
$pageTitle = $pageTitle ?? 'Administration';
$siteName = getConfig('nom_site', 'Guerre Iran');
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($pageTitle) ?> | <?= e($siteName) ?></title>
    <link rel="stylesheet" href="<?= ADMIN_URL ?>/assets/css/admin.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/icons.css">
</head>

<body class="admin-body">
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <?php include __DIR__ . '/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="admin-main">
            <!-- Top Bar -->
            <header class="admin-header">
                <button class="sidebar-toggle" id="sidebarToggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>

                <div class="header-title">
                    <h1><?= e($pageTitle) ?></h1>
                </div>

                <div class="header-actions">
                    <a href="<?= SITE_URL ?>" target="_blank" class="btn btn-sm btn-outline">
                        Voir le site
                    </a>
                    <div class="user-dropdown">
                        <button class="user-dropdown-toggle">
                            <span class="user-avatar">
                                <?= strtoupper(substr($_SESSION['admin_username'] ?? 'A', 0, 1)) ?>
                            </span>
                            <span
                                class="user-name"><?= e($_SESSION['admin_name'] ?: $_SESSION['admin_username']) ?></span>
                        </button>
                        <div class="user-dropdown-menu">
                            <a href="<?= ADMIN_URL ?>/profil.php">Mon profil</a>
                            <a href="<?= ADMIN_URL ?>/logout.php" class="text-danger">Déconnexion</a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="admin-content">
                <?php if (hasFlash('success')): ?>
                    <div class="alert alert-success">
                        <?= e(getFlash('success')) ?>
                    </div>
                <?php endif; ?>

                <?php if (hasFlash('error')): ?>
                    <div class="alert alert-error">
                        <?= e(getFlash('error')) ?>
                    </div>
                <?php endif; ?>

                <?php if (hasFlash('warning')): ?>
                    <div class="alert alert-warning">
                        <?= e(getFlash('warning')) ?>
                    </div>
                <?php endif; ?>