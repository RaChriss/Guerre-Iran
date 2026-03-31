<?php
/**
 * Création/Édition d'un utilisateur - BackOffice
 */

require_once __DIR__ . '/includes/auth.php';

if (!isAdmin()) {
    setFlash('error', 'Accès non autorisé.');
    redirect(ADMIN_URL . '/index.php');
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$isEdit = $id > 0;
$pageTitle = $isEdit ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur';

$user = null;
if ($isEdit) {
    $user = dbFetchOne("SELECT * FROM administrateurs WHERE id = ?", [$id]);
    if (!$user) {
        setFlash('error', 'Utilisateur introuvable.');
        redirect(ADMIN_URL . '/utilisateurs.php');
    }
}

$data = [
    'username' => $user['username'] ?? '',
    'email' => $user['email'] ?? '',
    'nom' => $user['nom'] ?? '',
    'prenom' => $user['prenom'] ?? '',
    'role' => $user['role'] ?? 'editeur',
    'actif' => $user['actif'] ?? true
];

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'username' => sanitize($_POST['username'] ?? ''),
        'email' => sanitize($_POST['email'] ?? ''),
        'nom' => sanitize($_POST['nom'] ?? ''),
        'prenom' => sanitize($_POST['prenom'] ?? ''),
        'role' => $_POST['role'] ?? 'editeur',
        'actif' => isset($_POST['actif']),
        'password' => $_POST['password'] ?? ''
    ];

    if (empty($data['username']))
        $errors['username'] = 'Le nom d\'utilisateur est obligatoire.';
    if (empty($data['email']))
        $errors['email'] = 'L\'email est obligatoire.';
    if (!$isEdit && empty($data['password']))
        $errors['password'] = 'Le mot de passe est obligatoire.';

    // Vérifier l'unicité
    $existing = dbFetchOne("SELECT id FROM administrateurs WHERE (username = ? OR email = ?) AND id != ?", [$data['username'], $data['email'], $id]);
    if ($existing)
        $errors['username'] = 'Ce nom d\'utilisateur ou email existe déjà.';

    if (empty($errors)) {
        if ($isEdit) {
            $sql = "UPDATE administrateurs SET username = ?, email = ?, nom = ?, prenom = ?, role = ?, actif = ?";
            $params = [$data['username'], $data['email'], $data['nom'], $data['prenom'], $data['role'], $data['actif']];

            if (!empty($data['password'])) {
                $sql .= ", password = ?";
                $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
            }

            $sql .= " WHERE id = ?";
            $params[] = $id;

            dbExecute($sql, $params);
            setFlash('success', 'Utilisateur modifié.');
        } else {
            dbExecute(
                "INSERT INTO administrateurs (username, password, email, nom, prenom, role, actif) VALUES (?, ?, ?, ?, ?, ?, ?)",
                [$data['username'], password_hash($data['password'], PASSWORD_DEFAULT), $data['email'], $data['nom'], $data['prenom'], $data['role'], $data['actif']]
            );
            setFlash('success', 'Utilisateur créé.');
        }
        redirect(ADMIN_URL . '/utilisateurs.php');
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="">
            <div class="grid-form-2-cols">
                <div class="form-group">
                    <label for="username" class="required">Nom d'utilisateur</label>
                    <input type="text" id="username" name="username" value="<?= e($data['username']) ?>"
                        class="form-control" required>
                    <?php if (isset($errors['username'])): ?>
                        <div class="form-error"><?= e($errors['username']) ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="email" class="required">Email</label>
                    <input type="email" id="email" name="email" value="<?= e($data['email']) ?>" class="form-control"
                        required>
                    <?php if (isset($errors['email'])): ?>
                        <div class="form-error"><?= e($errors['email']) ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="prenom">Prénom</label>
                    <input type="text" id="prenom" name="prenom" value="<?= e($data['prenom']) ?>" class="form-control">
                </div>

                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" value="<?= e($data['nom']) ?>" class="form-control">
                </div>

                <div class="form-group">
                    <label
                        for="password"><?= $isEdit ? 'Nouveau mot de passe (laisser vide pour ne pas changer)' : 'Mot de passe' ?></label>
                    <input type="password" id="password" name="password" class="form-control" <?= !$isEdit ? 'required' : '' ?>>
                    <?php if (isset($errors['password'])): ?>
                        <div class="form-error"><?= e($errors['password']) ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="role">Rôle</label>
                    <select id="role" name="role" class="form-control">
                        <option value="editeur" <?= $data['role'] === 'editeur' ? 'selected' : '' ?>>Éditeur</option>
                        <option value="admin" <?= $data['role'] === 'admin' ? 'selected' : '' ?>>Administrateur</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-check">
                    <input type="checkbox" name="actif" <?= $data['actif'] ? 'checked' : '' ?>>
                    <span>Compte actif</span>
                </label>
            </div>

            <div class="d-flex justify-between mt-3">
                <a href="<?= ADMIN_URL ?>/utilisateurs.php" class="btn btn-outline">← Retour</a>
                <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Enregistrer' : 'Créer' ?></button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>