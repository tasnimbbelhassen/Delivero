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
    addFlash('danger', 'ID de la catégorie manquant.');
    header('Location: categories.php');
    exit();
}

$category_id = $_GET['id'];


$stmt = $pdo->prepare("
    SELECT c.*,
           COUNT(d.id) as dish_count,
           SUM(d.order_count) as total_orders,
           AVG(d.rating) as avg_rating,
           SUM(d.order_count * d.price) as total_revenue
    FROM categories c
    LEFT JOIN dishes d ON c.id = d.category_id
    WHERE c.id = ?
    GROUP BY c.id
");
$stmt->execute([$category_id]);
$category = $stmt->fetch();

if (!$category) {
    addFlash('danger', 'Catégorie non trouvée.');
    header('Location: categories.php');
    exit();
}


$dishes_stmt = $pdo->prepare("
    SELECT d.*, r.name as restaurant_name
    FROM dishes d
    LEFT JOIN restaurants r ON d.restaurant_id = r.id
    WHERE d.category_id = ?
    ORDER BY d.order_count DESC, d.created_at DESC
    LIMIT 10
");
$dishes_stmt->execute([$category_id]);
$dishes = $dishes_stmt->fetchAll();


$pageTitle = htmlspecialchars($category['name']);
$pageIcon = "tags";
$pageDescription = "Détails et statistiques de la catégorie";
$breadcrumbs = [
    ['title' => 'Catégories', 'link' => 'categories.php', 'active' => false],
    ['title' => 'Détails', 'link' => '#', 'active' => true]
];


$template = 'templates/category_details.phtml';
include 'templates/layout-admin.phtml';
?>