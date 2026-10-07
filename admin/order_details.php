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
    addFlash('danger', 'ID de commande manquant.');
    header('Location: orders.php');
    exit();
}

$order_id = $_GET['id'];


$stmt = $pdo->prepare("
    SELECT o.*, 
           u.username, 
           u.email, 
           u.full_name, 
           u.phone
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    WHERE o.id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    addFlash('danger', 'Commande non trouvée.');
    header('Location: orders.php');
    exit();
}


$items_stmt = $pdo->prepare("
    SELECT oi.*, 
           d.name as dish_name,
           d.image as dish_image,
           d.description as dish_description
    FROM order_items oi
    LEFT JOIN dishes d ON oi.dish_id = d.id
    WHERE oi.order_id = ?
");
$items_stmt->execute([$order_id]);
$items = $items_stmt->fetchAll();


$pageTitle = "Détails de la commande #" . str_pad($order['id'], 6, '0', STR_PAD_LEFT);
$pageIcon = "cart";
$pageDescription = "Détails et suivi de la commande";
$breadcrumbs = [
    ['title' => 'Commandes', 'link' => 'orders.php', 'active' => false],
    ['title' => 'Détails', 'link' => '#', 'active' => true]
];


$template = 'templates/order_details.phtml';
include 'templates/layout-admin.phtml';
?>