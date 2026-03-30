<?php
/**
 * Vérification de l'authentification
 * À inclure en haut de chaque page protégée du BackOffice
 */

require_once __DIR__ . '/../../includes/functions.php';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    setFlash('error', 'Veuillez vous connecter pour accéder à cette page.');
    redirect(ADMIN_URL . '/login.php');
}

// Vérifier si l'utilisateur existe toujours et est actif
$currentUser = getCurrentUser();
if (!$currentUser) {
    session_destroy();
    redirect(ADMIN_URL . '/login.php');
}

// Mettre à jour la dernière connexion
dbExecute(
    "UPDATE administrateurs SET derniere_connexion = CURRENT_TIMESTAMP WHERE id = ?",
    [$currentUser['id']]
);
