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


$stats = [
    'total_dishes' => $pdo->query("SELECT COUNT(*) FROM dishes")->fetchColumn(),
    'total_restaurants' => $pdo->query("SELECT COUNT(*) FROM restaurants")->fetchColumn(),
    'total_categories' => $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn(),
    'pending_orders' => $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn(),
    'total_orders' => $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
    'revenue_today' => $pdo->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE DATE(created_at) = CURDATE()")->fetchColumn(),
    'available_dishes' => $pdo->query("SELECT COUNT(*) FROM dishes WHERE is_available = 1")->fetchColumn(),
    'special_dishes' => $pdo->query("SELECT COUNT(*) FROM dishes WHERE is_special = 1")->fetchColumn()
];


$popularDishes = $pdo->query("
    SELECT d.*, c.name as category_name, r.name as restaurant_name
    FROM dishes d
    LEFT JOIN categories c ON d.category_id = c.id
    LEFT JOIN restaurants r ON d.restaurant_id = r.id
    ORDER BY d.order_count DESC, d.created_at DESC
    LIMIT 5
")->fetchAll();


$recentOrders = $pdo->query("
    SELECT o.*, 
           COUNT(oi.id) as items_count,
           (SELECT GROUP_CONCAT(d.name SEPARATOR ', ') 
            FROM order_items oi2 
            JOIN dishes d ON oi2.dish_id = d.id 
            WHERE oi2.order_id = o.id LIMIT 2) as dish_names
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    GROUP BY o.id
    ORDER BY o.created_at DESC
    LIMIT 5
")->fetchAll();


$recentDishes = $pdo->query("
    SELECT d.*, c.name as category_name, r.name as restaurant_name
    FROM dishes d
    LEFT JOIN categories c ON d.category_id = c.id
    LEFT JOIN restaurants r ON d.restaurant_id = r.id
    ORDER BY d.created_at DESC
    LIMIT 5
")->fetchAll();


$pageTitle = "Tableau de bord";
$pageIcon = "speedometer2";
$pageDescription = "Vue d'ensemble de votre plateforme Delivero";
$breadcrumbs = [
    ['title' => 'Dashboard', 'link' => '#', 'active' => true]
];
$actionButton = [
    'text' => 'Ajouter un plat',
    'icon' => 'plus-circle',
    'link' => 'add_dish.php'
];


$template = 'templates/dashboard.phtml';
include 'templates/layout-admin.phtml';