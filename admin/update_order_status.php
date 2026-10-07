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


if (!isset($_GET['id']) || empty($_GET['id']) || !isset($_GET['status'])) {
    addFlash('danger', 'Paramètres manquants.');
    header('Location: orders.php');
    exit();
}

$order_id = $_GET['id'];
$status = $_GET['status'];


$valid_statuses = ['pending', 'preparing', 'delivering', 'completed', 'cancelled'];
if (!in_array($status, $valid_statuses)) {
    addFlash('danger', 'Statut invalide.');
    header('Location: orders.php');
    exit();
}

try {
    
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$status, $order_id]);
    
    
    $order_stmt = $pdo->prepare("SELECT order_number FROM orders WHERE id = ?");
    $order_stmt->execute([$order_id]);
    $order = $order_stmt->fetch();
    
    $order_display = $order['order_number'] ? "commande " . $order['order_number'] : "cette commande";
    
    $status_names = [
        'pending' => 'en attente',
        'preparing' => 'en préparation', 
        'delivering' => 'en livraison',
        'completed' => 'terminée',
        'cancelled' => 'annulée'
    ];
    
    addFlash('success', "La $order_display a été marquée comme " . $status_names[$status] . ".");
    
} catch (Exception $e) {
    addFlash('danger', 'Erreur lors de la mise à jour du statut: ' . $e->getMessage());
}


if (isset($_SERVER['HTTP_REFERER'])) {
    header('Location: ' . $_SERVER['HTTP_REFERER']);
} else {
    header('Location: order_details.php?id=' . $order_id);
}
exit();
?>