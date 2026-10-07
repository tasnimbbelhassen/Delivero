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


if (!isset($_GET['id']) || empty($_GET['id'])) {
    addFlash('danger', 'ID du plat manquant.');
    header('Location: dishes.php');
    exit();
}

$dish_id = $_GET['id'];


$stmt = $pdo->prepare("
    SELECT d.*, c.name as category_name, r.name as restaurant_name
    FROM dishes d
    LEFT JOIN categories c ON d.category_id = c.id
    LEFT JOIN restaurants r ON d.restaurant_id = r.id
    WHERE d.id = ?
");

$stmt->execute([$dish_id]);
$dish = $stmt->fetch();

if (!$dish) {
    addFlash('danger', 'Plat non trouvé.');
    header('Location: dishes.php');
    exit();
}


$pageTitle = htmlspecialchars($dish['name']);
$pageIcon = "egg-fried";
$pageDescription = "Détails du plat";
$breadcrumbs = [
    ['title' => 'Plats', 'link' => 'dishes.php', 'active' => false],
    ['title' => 'Détails', 'link' => '#', 'active' => true]
];


$template = 'templates/dish_details.phtml';
include 'templates/layout-admin.phtml';
?>