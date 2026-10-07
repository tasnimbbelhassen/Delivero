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
    addFlash('danger', 'ID du restaurant manquant.');
    header('Location: restaurants.php');
    exit();
}

$restaurant_id = $_GET['id'];


$stmt = $pdo->prepare("
    SELECT * FROM restaurants 
    WHERE id = ?
");
$stmt->execute([$restaurant_id]);
$restaurant = $stmt->fetch();

if (!$restaurant) {
    addFlash('danger', 'Restaurant non trouvé.');
    header('Location: restaurants.php');
    exit();
}


$dishes_stmt = $pdo->prepare("
    SELECT d.*, c.name as category_name
    FROM dishes d
    LEFT JOIN categories c ON d.category_id = c.id
    WHERE d.restaurant_id = ?
    ORDER BY d.created_at DESC
");
$dishes_stmt->execute([$restaurant_id]);
$dishes = $dishes_stmt->fetchAll();


$stats_stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_dishes,
        SUM(CASE WHEN is_available = 1 THEN 1 ELSE 0 END) as available_dishes,
        SUM(CASE WHEN is_special = 1 THEN 1 ELSE 0 END) as special_dishes,
        SUM(order_count) as total_orders,
        AVG(rating) as avg_dish_rating
    FROM dishes 
    WHERE restaurant_id = ?
");
$stats_stmt->execute([$restaurant_id]);
$stats = $stats_stmt->fetch();


$pageTitle = htmlspecialchars($restaurant['name']);
$pageIcon = "shop";
$pageDescription = "Détails et statistiques du restaurant";
$breadcrumbs = [
    ['title' => 'Restaurants', 'link' => 'restaurants.php', 'active' => false],
    ['title' => 'Détails', 'link' => '#', 'active' => true]
];


$template = 'templates/restaurant_details.phtml';
include 'templates/layout-admin.phtml';
?>