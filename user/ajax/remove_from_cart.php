<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non connecté']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$dish_id = $input['dish_id'] ?? null;

// Vider tout le panier
if ($dish_id === 'all') {
    $_SESSION['cart'] = [];
    echo json_encode([
        'success' => true,
        'count' => 0,
        'message' => 'Panier vidé'
    ]);
    exit();
}

// Supprimer un article spécifique
if (!$dish_id) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
    exit();
}

if (isset($_SESSION['cart'][$dish_id])) {
    unset($_SESSION['cart'][$dish_id]);
    
    echo json_encode([
        'success' => true,
        'count' => array_sum($_SESSION['cart']),
        'message' => 'Article supprimé du panier'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Article non trouvé']);
}
?>