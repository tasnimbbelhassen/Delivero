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
           d.price as dish_price
    FROM order_items oi
    LEFT JOIN dishes d ON oi.dish_id = d.id
    WHERE oi.order_id = ?
");
$items_stmt->execute([$order_id]);
$items = $items_stmt->fetchAll();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $delivery_address = trim($_POST['delivery_address'] ?? '');
    $delivery_time = trim($_POST['delivery_time'] ?? '');
    $payment_method = trim($_POST['payment_method'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $status = $_POST['status'] ?? $order['status'];
    $total = $_POST['total'] ?? $order['total'];
    
    
    if (empty($delivery_address)) {
        $errors[] = "L'adresse de livraison est obligatoire.";
    }
    
    if ($total <= 0) {
        $errors[] = "Le total doit être supérieur à 0.";
    }
    
    if (empty($errors)) {
        try {
            
            $update_stmt = $pdo->prepare("
                UPDATE orders 
                SET delivery_address = ?, 
                    delivery_time = ?, 
                    payment_method = ?, 
                    notes = ?, 
                    status = ?, 
                    total = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            
            $update_stmt->execute([
                $delivery_address, 
                $delivery_time, 
                $payment_method, 
                $notes, 
                $status, 
                $total,
                $order_id
            ]);
            
            addFlash('success', 'Commande mise à jour avec succès !');
            header('Location: order_details.php?id=' . $order_id);
            exit();
            
        } catch (Exception $e) {
            $errors[] = "Erreur lors de la mise à jour : " . $e->getMessage();
        }
    }
}


$pageTitle = "Modifier la commande";
$pageIcon = "pencil";
$pageDescription = "Modifiez les informations de la commande #" . str_pad($order['id'], 6, '0', STR_PAD_LEFT);
$breadcrumbs = [
    ['title' => 'Commandes', 'link' => 'orders.php', 'active' => false],
    ['title' => 'Commande #' . str_pad($order['id'], 6, '0', STR_PAD_LEFT), 'link' => 'order_details.php?id=' . $order_id, 'active' => false],
    ['title' => 'Modifier', 'link' => '#', 'active' => true]
];


$template = 'templates/order_form.phtml';
include 'templates/layout-admin.phtml';
?>