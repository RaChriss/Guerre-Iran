<?php
/**
 * Page de connexion au BackOffice
 */

require_once __DIR__ . '/../includes/functions.php';

// Si déjà connecté, rediriger vers le dashboard
if (isLoggedIn()) {
    redirect(ADMIN_URL);
}

$error = '';

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';

    // Vérification du token CSRF
    if (!verifyCsrfToken($csrfToken)) {
        $error = 'Session expirée. Veuillez réessayer.';
    } elseif (empty($username) || empty($password)) {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        // Recherche de l'utilisateur
        $user = dbFetchOne(
            "SELECT * FROM administrateurs WHERE username = ? AND actif = TRUE",
            [$username]
        );

        if ($user && password_verify($password, $user['password'])) {
            // Connexion réussie
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['admin_role'] = $user['role'];
            $_SESSION['admin_name'] = trim($user['prenom'] . ' ' . $user['nom']);

            // Mettre à jour la dernière connexion
            dbExecute(
                "UPDATE administrateurs SET derniere_connexion = CURRENT_TIMESTAMP WHERE id = ?",
                [$user['id']]
            );

            // Régénérer l'ID de session pour la sécurité
            session_regenerate_id(true);

            setFlash('success', 'Bienvenue ' . e($_SESSION['admin_name'] ?: $user['username']) . ' !');
            redirect(ADMIN_URL);
        } else {
            $error = 'Identifiants incorrects.';
        }
    }
}

$csrfToken = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Connexion | Administration - Guerre Iran</title>
    <link rel="stylesheet" href="assets/css/admin.css">
</head>

<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1>Administration</h1>
                <p>Guerre Iran - Site d'information</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <?php if (hasFlash('error')): ?>
                <div class="alert alert-error">
                    <?= e(getFlash('error')) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="login-form">
                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

                <div class="form-group">
                    <label for="username">Nom d'utilisateur</label>
                    <input type="text" id="username" name="username" value="<?= e($username ?? '') ?>" required
                        autofocus autocomplete="username">
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    Se connecter
                </button>
            </form>

            <div class="login-footer">
                <a href="<?= SITE_URL ?>">← Retour au site</a>
            </div>
        </div>
    </div>
</body>

</html>