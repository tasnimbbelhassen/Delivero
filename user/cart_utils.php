
<?php

function clearRestaurantCart($pdo, $restaurant_id) {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return false;
    }
    
 
    $stmt = $pdo->prepare("SELECT id FROM dishes WHERE restaurant_id = ?");
    $stmt->execute([$restaurant_id]);
    $restaurant_dishes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    

    foreach ($restaurant_dishes as $dish_id) {
        if (isset($_SESSION['cart'][$dish_id])) {
            unset($_SESSION['cart'][$dish_id]);
        }
    }
    
    return true;
}


function switchRestaurantCart($pdo, $new_restaurant_id, $new_dish_id, $quantity) {
   
    $_SESSION['cart'] = [];
    $_SESSION['cart'][$new_dish_id] = $quantity;
    
    return true;
}


function getCurrentCartRestaurant($pdo) {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return null;
    }
    
    $dish_ids = array_keys($_SESSION['cart']);
    $placeholders = str_repeat('?,', count($dish_ids) - 1) . '?';
    
    $stmt = $pdo->prepare("
        SELECT DISTINCT r.id, r.name 
        FROM dishes d
        LEFT JOIN restaurants r ON d.restaurant_id = r.id
        WHERE d.id IN ($placeholders)
        LIMIT 1
    ");
    $stmt->execute($dish_ids);
    
    return $stmt->fetch();
}