<?php
session_start();
require_once '../admin/includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_cart'])) {
        foreach ($_POST['quantity'] as $dish_id => $quantity) {
            if ($quantity > 0) {
                $_SESSION['cart'][$dish_id] = $quantity;
            } else {
                unset($_SESSION['cart'][$dish_id]);
            }
        }
    } elseif (isset($_POST['clear_cart'])) {
        $_SESSION['cart'] = [];
    }
    
    header('Location: cart.php');
    exit();
}


$cart_items = [];
$total = 0;

if (!empty($_SESSION['cart'])) {
    $dish_ids = array_keys($_SESSION['cart']);
    $placeholders = str_repeat('?,', count($dish_ids) - 1) . '?';
    
    $stmt = $pdo->prepare("
        SELECT d.*, r.name as restaurant_name, r.id as restaurant_id
        FROM dishes d
        LEFT JOIN restaurants r ON d.restaurant_id = r.id
        WHERE d.id IN ($placeholders)
        ORDER BY r.name, d.name
    ");
    $stmt->execute($dish_ids);
    $dishes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($dishes as $dish) {
        $quantity = $_SESSION['cart'][$dish['id']];
        $subtotal = $dish['price'] * $quantity;
        $total += $subtotal;
        
        $cart_items[] = [
            'dish' => $dish,
            'quantity' => $quantity,
            'subtotal' => $subtotal
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon panier - Delivero</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/cart.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container mt-4">
        <div class="row">
            <div class="col-lg-8">
               
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0">
                        <i class="bi bi-cart"></i> Mon panier
                    </h1>
                    <?php if (!empty($cart_items)): ?>
                        <form method="POST" class="d-inline">
                            <button type="submit" name="clear_cart" class="btn btn-outline-danger btn-sm"
                                    onclick="return confirm('Vider tout le panier ?')">
                                <i class="bi bi-trash"></i> Vider le panier
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
                
                <?php if (empty($cart_items)): ?>

                    <div class="card shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-cart-x display-1 text-muted"></i>
                            <h3 class="mt-3">Votre panier est vide</h3>
                            <p class="text-muted mb-4">Ajoutez des plats pour commencer votre commande</p>
                            <a href="home.php" class="btn btn-primary">
                                <i class="bi bi-shop"></i> Voir les restaurants
                            </a>
                        </div>
                    </div>
                <?php else: ?>
            
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <form method="POST" id="cart-form">
                                <?php foreach ($cart_items as $item): ?>
                                    <?php $dish = $item['dish']; ?>
                                    <div class="cart-item row align-items-center mb-3 pb-3 border-bottom">
                                 
                                        <div class="col-2 col-md-1">
                                            <?php if ($dish['image']): ?>
                                                <img src="<?= htmlspecialchars($dish['image']) ?>" 
                                                     alt="<?= htmlspecialchars($dish['name']) ?>"
                                                     class="img-fluid rounded">
                                            <?php else: ?>
                                                <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                     style="width: 50px; height: 50px;">
                                                    <i class="bi bi-egg-fried text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                   
                                        <div class="col-5 col-md-6">
                                            <h6 class="mb-1"><?= htmlspecialchars($dish['name']) ?></h6>
                                            <small class="text-muted d-block">
                                                <i class="bi bi-shop"></i> <?= htmlspecialchars($dish['restaurant_name']) ?>
                                            </small>
                                            <small class="text-primary">
                                                <?= number_format($dish['price'], 2, ',', ' ') ?> €
                                            </small>
                                        </div>
                                        
                                      
                                        <div class="col-3 col-md-3">
                                            <div class="input-group input-group-sm" style="width: 120px;">
                                                <button type="button" class="btn btn-outline-secondary" 
                                                        onclick="this.nextElementSibling.stepDown()">
                                                    <i class="bi bi-dash"></i>
                                                </button>
                                                <input type="number" name="quantity[<?= $dish['id'] ?>]" 
                                                       value="<?= $item['quantity'] ?>" 
                                                       min="1" max="20" 
                                                       class="form-control text-center">
                                                <button type="button" class="btn btn-outline-secondary" 
                                                        onclick="this.previousElementSibling.stepUp()">
                                                    <i class="bi bi-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                        
                                       
                                        <div class="col-2 col-md-2 text-end">
                                            <div class="fw-bold">
                                                <?= number_format($item['subtotal'], 2, ',', ' ') ?> €
                                            </div>
                                            <button type="button" class="btn btn-link btn-sm text-danger p-0 remove-item"
                                                    data-dish-id="<?= $dish['id'] ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                
                                <div class="d-flex justify-content-between mt-3">
                                    <a href="home.php" class="btn btn-outline-primary">
                                        <i class="bi bi-arrow-left"></i> Continuer les achats
                                    </a>
                                    <button type="submit" name="update_cart" class="btn btn-primary">
                                        <i class="bi bi-arrow-clockwise"></i> Mettre à jour
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
           
            <div class="col-lg-4">
                <div class="card shadow-sm sticky-top" style="top: 80px;">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="bi bi-receipt"></i> Récapitulatif
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Sous-total</span>
                                <span><?= number_format($total, 2, ',', ' ') ?> €</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Frais de livraison</span>
                                <span class="text-success">Gratuit</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Frais de service</span>
                                <span>2,50 €</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between fw-bold fs-5">
                                <span>Total</span>
                                <span class="text-primary">
                                    <?= number_format($total + 2.5, 2, ',', ' ') ?> €
                                </span>
                            </div>
                        </div>
                        
                        <?php if (!empty($cart_items)): ?>
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-truck"></i> Livraison
                                </label>
                                <select class="form-select">
                                    <option value="asap">Livraison rapide (30-45 min)</option>
                                    <option value="19:00">19h00 - 19h30</option>
                                    <option value="19:30">19h30 - 20h00</option>
                                    <option value="20:00">20h00 - 20h30</option>
                                </select>
                            </div>
                            
                            <a href="checkout.php" class="btn btn-primary btn-lg w-100 py-3">
                                <i class="bi bi-lock"></i> Passer la commande
                            </a>
                            
                            <p class="text-center text-muted small mt-2">
                                <i class="bi bi-shield-check"></i> Paiement 100% sécurisé
                            </p>
                        <?php else: ?>
                            <a href="home.php" class="btn btn-outline-primary w-100">
                                <i class="bi bi-shop"></i> Découvrir les restaurants
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/cart.js"></script>
</body>
</html>