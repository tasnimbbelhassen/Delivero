<?php
session_start();
require_once '../admin/includes/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non connecté']);
    exit();
}

$order_id = $_GET['id'] ?? 0;

// Récupérer commande
$stmt = $pdo->prepare("
    SELECT o.*, 
           TIMESTAMPDIFF(MINUTE, o.created_at, NOW()) as minutes_elapsed,
           CASE 
               WHEN o.status = 'pending' THEN 'En attente'
               WHEN o.status = 'preparing' THEN 'En préparation'
               WHEN o.status = 'delivering' THEN 'En livraison'
               WHEN o.status = 'completed' THEN 'Livrée'
               ELSE 'Annulée'
           END as status_text
    FROM orders o
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    echo json_encode(['success' => false, 'message' => 'Commande non trouvée']);
    exit();
}

// Simuler progression
$progress = 0;
switch($order['status']) {
    case 'pending': $progress = 25; break;
    case 'preparing': $progress = 50; break;
    case 'delivering': $progress = 75; break;
    case 'completed': $progress = 100; break;
}

// Simuler position livreur (pour démo)
$latitude = 36.8065 + (rand(-100, 100) / 10000);
$longitude = 10.1815 + (rand(-100, 100) / 10000);

echo json_encode([
    'success' => true,
    'order' => [
        'id' => $order['id'],
        'number' => $order['order_number'],
        'status' => $order['status'],
        'status_text' => $order['status_text'],
        'progress' => $progress,
        'minutes_elapsed' => $order['minutes_elapsed'],
        'delivery_address' => $order['delivery_address'],
        'estimated_time' => $order['delivery_time'] == 'asap' ? '30-45 minutes' : $order['delivery_time']
    ],
    'driver' => [
        'name' => 'Mohamed',
        'phone' => '+216 23 456 789',
        'rating' => 4.8,
        'latitude' => $latitude,
        'longitude' => $longitude
    ]
]);