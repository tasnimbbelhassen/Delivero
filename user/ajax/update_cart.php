<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non connecté']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$dish_id = $input['dish_id'] ?? null;
$quantity = $input['quantity'] ?? 1;

if (!$dish_id || $quantity < 0) {
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit();
}

// Vérifier si panier existe
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Mettre à jour ou supprimer
if ($quantity > 0) {
    $_SESSION['cart'][$dish_id] = $quantity;
} else {
    unset($_SESSION['cart'][$dish_id]);
}

// Calculer total
$total = 0;
$total_items = array_sum($_SESSION['cart']);

echo json_encode([
    'success' => true,
    'count' => $total_items,
    'cart' => $_SESSION['cart'],
    'message' => 'Panier mis à jour'
]);