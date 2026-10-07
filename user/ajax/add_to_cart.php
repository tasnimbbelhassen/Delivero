<?php
// Turn on ALL error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session FIRST
session_start();

// Set JSON header IMMEDIATELY
header('Content-Type: application/json');

// DEBUG: Check if session is working
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'NOT LOGGED IN',
        'session_id' => session_id(),
        'session_data' => $_SESSION
    ]);
    exit();
}

// FIXED PATH: From user/ajax/ to admin/includes/config.php
$config_path = __DIR__ . '/../../admin/includes/config.php';

// Debug: Check if file exists
if (!file_exists($config_path)) {
    echo json_encode([
        'success' => false,
        'message' => 'CONFIG FILE NOT FOUND',
        'path_tried' => $config_path,
        'current_dir' => __DIR__
    ]);
    exit();
}

// Include config
require_once $config_path;

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Check if JSON is valid
if (!$data) {
    echo json_encode([
        'success' => false,
        'message' => 'INVALID JSON DATA',
        'raw_input' => $input
    ]);
    exit();
}

// Get dish_id and quantity
$dish_id = intval($data['dish_id'] ?? 0);
$quantity = intval($data['quantity'] ?? 1);

// Validate
if ($dish_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'INVALID DISH ID']);
    exit();
}

// Get dish info from database
try {
    $stmt = $pdo->prepare("
        SELECT d.*, r.id as restaurant_id, r.name as restaurant_name 
        FROM dishes d 
        LEFT JOIN restaurants r ON d.restaurant_id = r.id 
        WHERE d.id = ?
    ");
    $stmt->execute([$dish_id]);
    $dish = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$dish) {
        echo json_encode(['success' => false, 'message' => 'DISH NOT FOUND']);
        exit();
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'DATABASE ERROR: ' . $e->getMessage()
    ]);
    exit();
}

// Initialize cart if needed
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Check restaurant conflict if cart is not empty
if (!empty($_SESSION['cart'])) {
    $existing_dish_ids = array_keys($_SESSION['cart']);
    if (!empty($existing_dish_ids)) {
        $placeholders = str_repeat('?,', count($existing_dish_ids) - 1) . '?';
        
        try {
            $stmt = $pdo->prepare("
                SELECT DISTINCT restaurant_id 
                FROM dishes 
                WHERE id IN ($placeholders)
            ");
            $stmt->execute($existing_dish_ids);
            $existing_restaurants = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Check if new dish is from different restaurant
            if (!in_array($dish['restaurant_id'], $existing_restaurants)) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'RESTAURANT CONFLICT',
                    'action_required' => true,
                    'current_restaurant_id' => reset($existing_restaurants),
                    'new_restaurant_id' => $dish['restaurant_id'],
                    'new_restaurant_name' => $dish['restaurant_name']
                ]);
                exit();
            }
        } catch (Exception $e) {
            // Continue even if there's an error checking
        }
    }
}

// Add to cart
if (isset($_SESSION['cart'][$dish_id])) {
    $_SESSION['cart'][$dish_id] += $quantity;
} else {
    $_SESSION['cart'][$dish_id] = $quantity;
}

// Calculate total items
$total_items = array_sum($_SESSION['cart']);

// SUCCESS response
echo json_encode([
    'success' => true,
    'message' => 'ADDED TO CART: ' . $dish['name'],
    'count' => $total_items,
    'dish' => [
        'id' => $dish['id'],
        'name' => $dish['name'],
        'price' => $dish['price'],
        'restaurant_name' => $dish['restaurant_name']
    ],
    'cart' => $_SESSION['cart']
]);
?>