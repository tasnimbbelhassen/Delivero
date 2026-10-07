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

try {
    
    $pdo->beginTransaction();
    
    
    $stmt = $pdo->prepare("SELECT order_number FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
    
    if ($order) {
        
        $delete_items = $pdo->prepare("DELETE FROM order_items WHERE order_id = ?");
        $delete_items->execute([$order_id]);
        
        
        $delete_order = $pdo->prepare("DELETE FROM orders WHERE id = ?");
        $delete_order->execute([$order_id]);
        
        $pdo->commit();
        
        $order_display = $order['order_number'] ? "commande " . $order['order_number'] : "cette commande";
        addFlash('success', "La $order_display a été supprimée avec succès.");
    } else {
        addFlash('warning', 'Commande non trouvée.');
    }
    
} catch (Exception $e) {
    $pdo->rollBack();
    addFlash('danger', 'Erreur lors de la suppression : ' . $e->getMessage());
}

header('Location: orders.php');
exit();
?>