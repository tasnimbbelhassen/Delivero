<?php
function redirect($url) {
    header("Location: $url");
    exit();
}

function flash($type, $message) {
    $_SESSION['flash'][$type] = $message;
}

function getFlash($type) {
    if (isset($_SESSION['flash'][$type])) {
        $message = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);
        return $message;
    }
    return null;
}


function formatPrice($price) {
    return number_format($price, 2, ',', ' ') . ' €';
}


function formatDate($date, $format = 'd/m/Y H:i') {
    return date($format, strtotime($date));
}


function truncate($string, $length = 100) {
    if (strlen($string) > $length) {
        return substr($string, 0, $length) . '...';
    }
    return $string;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}


function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}


function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}


function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}


function logError($error) {
    $logFile = __DIR__ . '/../logs/errors.log';
    $message = date('Y-m-d H:i:s') . " - $error\n";
    file_put_contents($logFile, $message, FILE_APPEND);
}


function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}


function isValidPhone($phone) {
    return preg_match('/^[0-9+\-\s]{8,20}$/', $phone);
}


function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371; // Rayon Terre en km
    
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    
    $a = sin($dLat/2) * sin($dLat/2) + 
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * 
         sin($dLon/2) * sin($dLon/2);
    
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    
    return $earthRadius * $c;
}


function generatePromoCode($length = 8) {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $code = '';
    
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    
    return $code;
}


function isDishAvailable($dish_id, $pdo) {
    $stmt = $pdo->prepare("SELECT is_available FROM dishes WHERE id = ?");
    $stmt->execute([$dish_id]);
    $dish = $stmt->fetch();
    
    return $dish && $dish['is_available'] == 1;
}


function getCart($pdo) {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return [];
    }
    
    $dish_ids = array_keys($_SESSION['cart']);
    $placeholders = str_repeat('?,', count($dish_ids) - 1) . '?';
    
    $stmt = $pdo->prepare("
        SELECT d.*, r.name as restaurant_name
        FROM dishes d
        LEFT JOIN restaurants r ON d.restaurant_id = r.id
        WHERE d.id IN ($placeholders)
    ");
    $stmt->execute($dish_ids);
    
    $dishes = $stmt->fetchAll();
    $cart_items = [];
    
    foreach ($dishes as $dish) {
        $quantity = $_SESSION['cart'][$dish['id']];
        $cart_items[] = [
            'dish' => $dish,
            'quantity' => $quantity,
            'subtotal' => $dish['price'] * $quantity
        ];
    }
    
    return $cart_items;
}


function getCartTotal($pdo) {
    $cart_items = getCart($pdo);
    $total = 0;
    
    foreach ($cart_items as $item) {
        $total += $item['subtotal'];
    }
    
    return $total;
}

function checkCartRestaurantConsistency($pdo) {
    $cart_items = getCart($pdo);
    
    if (empty($cart_items)) {
        return true;
    }
    
    $restaurant_ids = array_unique(array_column(array_column($cart_items, 'dish'), 'restaurant_id'));
    
    return count($restaurant_ids) === 1;
}


function sendNotificationEmail($to, $subject, $template, $data) {
  
    $templatePath = __DIR__ . "/../email_templates/$template.html";
    
    if (!file_exists($templatePath)) {
        logError("Template email non trouvé: $template");
        return false;
    }
    
    $content = file_get_contents($templatePath);
    
   
    foreach ($data as $key => $value) {
        $content = str_replace("{{$key}}", $value, $content);
    }
    
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=utf-8',
        'From: Delivero <noreply@delivero.tn>',
        'Reply-To: support@delivero.tn'
    ];
    
    return mail($to, $subject, $content, implode("\r\n", $headers));
}


function getCartWithRestaurants($pdo) {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return [
            'items' => [],
            'restaurants' => [],
            'total' => 0
        ];
    }
    
    $dish_ids = array_keys($_SESSION['cart']);
    $placeholders = str_repeat('?,', count($dish_ids) - 1) . '?';
    
    $stmt = $pdo->prepare("
        SELECT d.*, r.id as restaurant_id, r.name as restaurant_name, 
               r.image as restaurant_image, r.delivery_time
        FROM dishes d
        LEFT JOIN restaurants r ON d.restaurant_id = r.id
        WHERE d.id IN ($placeholders)
        ORDER BY r.name, d.name
    ");
    $stmt->execute($dish_ids);
    
    $dishes = $stmt->fetchAll();
    $cart_items = [];
    $restaurants = [];
    $total = 0;
    
    foreach ($dishes as $dish) {
        $quantity = $_SESSION['cart'][$dish['id']];
        $subtotal = $dish['price'] * $quantity;
        $total += $subtotal;
        
        $cart_items[] = [
            'dish' => $dish,
            'quantity' => $quantity,
            'subtotal' => $subtotal
        ];
        
        $restaurant_id = $dish['restaurant_id'];
        if (!isset($restaurants[$restaurant_id])) {
            $restaurants[$restaurant_id] = [
                'id' => $dish['restaurant_id'],
                'name' => $dish['restaurant_name'],
                'image' => $dish['restaurant_image'],
                'delivery_time' => $dish['delivery_time'],
                'items_count' => 0,
                'subtotal' => 0
            ];
        }
        
        $restaurants[$restaurant_id]['items_count'] += $quantity;
        $restaurants[$restaurant_id]['subtotal'] += $subtotal;
    }
    
    return [
        'items' => $cart_items,
        'restaurants' => array_values($restaurants),
        'total' => $total
    ];
}


function checkRestaurantCompatibility($pdo, $new_restaurant_id) {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return true; 
    }
    
    $dish_ids = array_keys($_SESSION['cart']);
    $placeholders = str_repeat('?,', count($dish_ids) - 1) . '?';
    
    $stmt = $pdo->prepare("
        SELECT DISTINCT restaurant_id 
        FROM dishes 
        WHERE id IN ($placeholders)
    ");
    $stmt->execute($dish_ids);
    
    $existing_restaurants = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    
    if (count($existing_restaurants) > 0 && !in_array($new_restaurant_id, $existing_restaurants)) {
        return false;
    }
    
    return true;
}
?>