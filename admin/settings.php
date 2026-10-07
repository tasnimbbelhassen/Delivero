<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';


if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . 'login.php');
    exit();
}


if (!isAdmin()) {
    addFlash('danger', 'Accès non autorisé.');
    header('Location: ' . BASE_URL);
    exit();
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $success = true;
    addFlash('success', 'Paramètres mis à jour avec succès !');
}

$pageTitle = "Paramètres";
$pageIcon = "gear";
$pageDescription = "Configurez votre plateforme Delivero";
$breadcrumbs = [
    ['title' => 'Paramètres', 'link' => '#', 'active' => true]
];


$template = 'templates/settings.phtml';
include 'templates/layout-admin.phtml';
?>