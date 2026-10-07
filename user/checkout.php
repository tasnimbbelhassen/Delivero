<?php
session_start();
require_once '../admin/includes/config.php';


if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];


$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();


$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    $_SESSION['error'] = 'Votre panier est vide.';
    header('Location: cart.php');
    exit();
}


$total = 0;
$dishes = [];
if (!empty($cart)) {
    $dish_ids = array_keys($cart);
    $placeholders = str_repeat('?,', count($dish_ids) - 1) . '?';
    
    
    $stmt = $pdo->prepare("SELECT id, name, price FROM dishes WHERE id IN ($placeholders)");
    $stmt->execute($dish_ids);
    $dishes = $stmt->fetchAll();
    
    foreach ($dishes as $dish) {
        $total += $dish['price'] * $cart[$dish['id']];
    }
}
$total += 2.5;


$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $delivery_address = trim($_POST['delivery_address'] ?? '');
    $delivery_time = $_POST['delivery_time'] ?? 'asap';
    $payment_method = $_POST['payment_method'] ?? 'cash';
    $notes = trim($_POST['notes'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    if (empty($delivery_address)) {
        $error = "L'adresse de livraison est requise";
    } elseif (empty($phone)) {
        $error = "Le téléphone est requis";
    } else {
       
        $order_number = 'CMD-' . date('YmdHis') . '-' . rand(1000, 9999);
        
        
        $pdo->beginTransaction();
        
        try {
          
            $stmt = $pdo->prepare("
                INSERT INTO orders (order_number, user_id, total, delivery_address, 
                                   delivery_time, payment_method, notes, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
            ");
            $stmt->execute([
                $order_number, 
                $user_id, 
                $total, 
                $delivery_address, 
                $delivery_time, 
                $payment_method, 
                $notes
               
            ]);
            $order_id = $pdo->lastInsertId();
            
          
            $stmt = $pdo->prepare("
                INSERT INTO order_items (order_id, dish_id, quantity, price, subtotal)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            foreach ($dishes as $dish) {
                $quantity = $cart[$dish['id']];
                $subtotal = $dish['price'] * $quantity;
                
                $stmt->execute([$order_id, $dish['id'], $quantity, $dish['price'], $subtotal]);
                
                
                $pdo->prepare("UPDATE dishes SET order_count = order_count + ? WHERE id = ?")
                    ->execute([$quantity, $dish['id']]);
            }
            
            $pdo->commit();
            
            $_SESSION['cart'] = [];
            $_SESSION['last_order_id'] = $order_id;
            
            header("Location: order_confirmation.php?id=$order_id");
            exit();
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Erreur lors de la commande: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validation - Delivero</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link href="assets/css/checkout.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container mt-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="steps">
                    <div class="step completed">
                        <div class="step-number">1</div>
                        <div class="step-label">Panier</div>
                    </div>
                    <div class="step active">
                        <div class="step-number">2</div>
                        <div class="step-label">Livraison</div>
                    </div>
                    <div class="step">
                        <div class="step-number">3</div>
                        <div class="step-label">Paiement</div>
                    </div>
                    <div class="step">
                        <div class="step-number">4</div>
                        <div class="step-label">Confirmation</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            
            <div class="col-lg-8">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="needs-validation" novalidate>
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="bi bi-geo-alt"></i> Adresse de livraison
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Adresse complète *</label>
                                <textarea class="form-control" name="delivery_address" rows="3" required
                                          placeholder="Numéro, rue, appartement, code postal, ville"><?= 
                                          htmlspecialchars($user['address'] ?? '') ?></textarea>
                                <div class="invalid-feedback">
                                    Veuillez entrer votre adresse de livraison
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Téléphone *</label>
                                        <input type="tel" class="form-control" name="phone" required
                                               value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                                        <div class="invalid-feedback">
                                            Veuillez entrer votre numéro de téléphone
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Heure de livraison</label>
                                        <select class="form-select" name="delivery_time">
                                            <option value="asap" selected>Livraison la plus rapide</option>
                                            <option value="19:00">19h00 - 19h30</option>
                                            <option value="19:30">19h30 - 20h00</option>
                                            <option value="20:00">20h00 - 20h30</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Instructions pour le livreur (optionnel)</label>
                                <textarea class="form-control" name="notes" rows="2"
                                          placeholder="Code de la porte, étage, etc."><?= 
                                          htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                    
                    
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="bi bi-credit-card"></i> Mode de paiement
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" 
                                               name="payment_method" value="card" 
                                               id="card" <?= ($_POST['payment_method'] ?? 'cash') === 'card' ? 'checked' : '' ?>>
                                        <label class="form-check-label w-100" for="card">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-credit-card-2-front fs-4 me-3"></i>
                                                <div>
                                                    <div class="fw-bold">Carte bancaire</div>
                                                    <small class="text-muted">Paiement sécurisé</small>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" 
                                               name="payment_method" value="cash" 
                                               id="cash" <?= ($_POST['payment_method'] ?? 'cash') === 'cash' ? 'checked' : '' ?>>
                                        <label class="form-check-label w-100" for="cash">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-cash fs-4 me-3"></i>
                                                <div>
                                                    <div class="fw-bold">Espèces</div>
                                                    <small class="text-muted">À la livraison</small>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            
                            <div id="card-details" class="mt-3" style="display: <?= ($_POST['payment_method'] ?? 'cash') === 'card' ? 'block' : 'none' ?>;">
                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <label class="form-label">Numéro de carte</label>
                                        <input type="text" class="form-control" 
                                               placeholder="1234 5678 9012 3456" 
                                               name="card_number"
                                               value="<?= htmlspecialchars($_POST['card_number'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-2 mb-3">
                                        <label class="form-label">MM/AA</label>
                                        <input type="text" class="form-control" placeholder="12/25"
                                               name="card_expiry"
                                               value="<?= htmlspecialchars($_POST['card_expiry'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-2 mb-3">
                                        <label class="form-label">CVV</label>
                                        <input type="text" class="form-control" placeholder="123"
                                               name="card_cvv"
                                               value="<?= htmlspecialchars($_POST['card_cvv'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg py-3">
                            <i class="bi bi-lock"></i> Confirmer la commande - 
                            <?= number_format($total, 2, ',', ' ') ?> DT
                        </button>
                        <p class="text-center text-muted small mt-2">
                            En cliquant, vous acceptez nos <a href="#">conditions générales</a>
                        </p>
                    </div>
                </form>
            </div>
            
            
            <div class="col-lg-4">
                <div class="card shadow-sm sticky-top" style="top: 80px;">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="bi bi-receipt"></i> Votre commande
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($dishes)): ?>
                            <div class="mb-3">
                                <?php foreach ($dishes as $dish): ?>
                                    <div class="d-flex justify-content-between mb-2">
                                        <div>
                                            <span class="small">
                                                <?= $cart[$dish['id']] ?> x <?= htmlspecialchars($dish['name'] ?? 'Plat') ?>
                                            </span>
                                        </div>
                                        <div class="text-end">
                                            <?= number_format(($dish['price'] ?? 0) * $cart[$dish['id']], 2, ',', ' ') ?> DT
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <hr>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Sous-total</span>
                                <span><?= number_format($total - 2.5, 2, ',', ' ') ?> DT</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Frais de service</span>
                                <span>2,50 DT</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Livraison</span>
                                <span class="text-success">Gratuit</span>
                            </div>
                            
                            <hr>
                            <div class="d-flex justify-content-between fw-bold fs-5">
                                <span>Total</span>
                                <span class="text-primary">
                                    <?= number_format($total, 2, ',', ' ') ?> DT
                                </span>
                            </div>
                            
                            <hr class="my-3">
                            <div class="small text-muted">
                                <p class="mb-1">
                                    <i class="bi bi-truck"></i> Livraison gratuite
                                </p>
                                <p class="mb-1">
                                    <i class="bi bi-clock"></i> 30-45 minutes
                                </p>
                                <p>
                                    <i class="bi bi-shield-check"></i> Paiement sécurisé
                                </p>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">Aucun article dans le panier</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
  
    document.addEventListener('DOMContentLoaded', function() {
        const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
        const cardDetails = document.getElementById('card-details');
        
        paymentRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'card') {
                    cardDetails.style.display = 'block';
                } else {
                    cardDetails.style.display = 'none';
                }
            });
        });
        
   
        const forms = document.querySelectorAll('.needs-validation');
        
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
        
   
        const phoneInput = document.querySelector('input[name="phone"]');
        if (phoneInput) {
            phoneInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 0) {
                    value = '+216 ' + value.substring(0, 2) + ' ' + value.substring(2, 5) + ' ' + value.substring(5, 8);
                }
                e.target.value = value.substring(0, 15);
            });
        }
    });
    </script>
</body>
</html>