<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'NOT LOGGED IN']);
    exit();
}

// FIXED PATH
$config_path = __DIR__ . '/../../admin/includes/config.php';

if (!file_exists($config_path)) {
    echo json_encode([
        'success' => false,
        'message' => 'CONFIG NOT FOUND',
        'path' => $config_path
    ]);
    exit();
}

require_once $config_path;

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$dish_id = intval($data['dish_id'] ?? 0);
$quantity = intval($data['quantity'] ?? 1);

if ($dish_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'INVALID DISH ID']);
    exit();
}

// Get dish info
try {
    $stmt = $pdo->prepare("
        SELECT d.*, r.name as restaurant_name
        FROM dishes d
        LEFT JOIN restaurants r ON d.restaurant_id = r.id
        WHERE d.id = ?
    ");
    $stmt->execute([$dish_id]);
    $dish = $stmt->fetch();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'DB ERROR']);
    exit();
}

if (!$dish) {
    echo json_encode(['success' => false, 'message' => 'DISH NOT FOUND']);
    exit();
}

// Clear cart and add new item
$_SESSION['cart'] = [$dish_id => $quantity];
$total_items = array_sum($_SESSION['cart']);

echo json_encode([
    'success' => true,
    'message' => 'SWITCHED TO RESTAURANT: ' . $dish['restaurant_name'],
    'count' => $total_items,
    'restaurant_name' => $dish['restaurant_name']
]);
?>