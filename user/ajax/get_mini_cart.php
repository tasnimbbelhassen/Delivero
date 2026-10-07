<?php
session_start();
require_once '../../admin/includes/config.php';

header('Content-Type: application/json');

// Initialiser les variables
$cart_html = '';
$total = 0;
$has_items = false;

// Vérifier si le panier existe et n'est pas vide
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    try {
        $dish_ids = array_keys($_SESSION['cart']);
        $placeholders = str_repeat('?,', count($dish_ids) - 1) . '?';
        
        $stmt = $pdo->prepare("
            SELECT d.*, r.name as restaurant_name
            FROM dishes d
            LEFT JOIN restaurants r ON d.restaurant_id = r.id
            WHERE d.id IN ($placeholders)
        ");
        $stmt->execute($dish_ids);
        $dishes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($dishes) {
            $has_items = true;
            
            foreach ($dishes as $dish) {
                $quantity = $_SESSION['cart'][$dish['id']];
                $subtotal = $dish['price'] * $quantity;
                $total += $subtotal;
                
                $cart_html .= '
                <div class="mini-cart-item border-bottom pb-2 mb-2">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-2">
                            ' . ($dish['image'] ? 
                                '<img src="' . htmlspecialchars($dish['image']) . '" 
                                     alt="' . htmlspecialchars($dish['name']) . '" 
                                     class="rounded" 
                                     style="width: 50px; height: 50px; object-fit: cover;">' 
                                : 
                                '<div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                     style="width: 50px; height: 50px;">
                                    <i class="bi bi-egg-fried text-muted"></i>
                                 </div>'
                            ) . '
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0 small">' . htmlspecialchars($dish['name']) . '</h6>
                            <small class="text-muted">' . htmlspecialchars($dish['restaurant_name']) . '</small>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <span class="badge bg-light text-dark">' . $quantity . 'x</span>
                                <span class="fw-bold">' . number_format($subtotal, 2, ',', ' ') . ' DT</span>
                            </div>
                        </div>
                        <button type="button" 
                                class="btn btn-link btn-sm text-danger p-0 ms-2 remove-mini-cart-item" 
                                data-dish-id="' . $dish['id'] . '"
                                title="Supprimer">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>';
            }
        }
    } catch (PDOException $e) {
        // En cas d'erreur, retourner un panier vide
        $has_items = false;
        $cart_html = '';
    }
}

echo json_encode([
    'success' => true,
    'has_items' => $has_items,
    'html' => $cart_html,
    'total' => $total,
    'count' => $has_items ? array_sum($_SESSION['cart']) : 0
]);