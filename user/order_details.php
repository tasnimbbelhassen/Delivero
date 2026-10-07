<?php
session_start();
require_once '../admin/includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$order_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Récupérer commande
$stmt = $pdo->prepare("
    SELECT o.*, u.full_name, u.email, u.phone
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: orders.php');
    exit();
}

// Récupérer articles
$stmt = $pdo->prepare("
    SELECT oi.*, d.name as dish_name, d.image, r.name as restaurant_name
    FROM order_items oi
    LEFT JOIN dishes d ON oi.dish_id = d.id
    LEFT JOIN restaurants r ON d.restaurant_id = r.id
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();


$status_info = [
    'pending' => ['class' => 'warning', 'icon' => 'clock', 'text' => 'En attente', 'progress' => 25],
    'preparing' => ['class' => 'info', 'icon' => 'egg-fried', 'text' => 'En préparation', 'progress' => 50],
    'delivering' => ['class' => 'primary', 'icon' => 'truck', 'text' => 'En livraison', 'progress' => 75],
    'completed' => ['class' => 'success', 'icon' => 'check-circle', 'text' => 'Terminée', 'progress' => 100],
    'cancelled' => ['class' => 'danger', 'icon' => 'x-circle', 'text' => 'Annulée', 'progress' => 0]
];
$current_status = $status_info[$order['status']];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commande #<?= $order['order_number'] ?> - Delivero</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container mt-4">
       
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 mb-1">
                            <i class="bi bi-receipt"></i> Commande #<?= htmlspecialchars($order['order_number'] ?? '') ?>
                        </h1>
                        <p class="text-muted mb-0">
                            <?= date('d/m/Y à H:i', strtotime($order['created_at'] ?? date('Y-m-d H:i:s'))) ?>
                        </p>
                    </div>
                    <div class="text-end">
                        <a href="orders.php" class="btn btn-outline-primary">
                            <i class="bi bi-arrow-left"></i> Retour
                        </a>
                        <?php if ($order['status'] == 'pending'): ?>
                            <button class="btn btn-outline-danger ms-2" id="cancel-order">
                                <i class="bi bi-x-circle"></i> Annuler
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            
            <div class="col-lg-8">
             
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <span class="badge bg-<?= $current_status['class'] ?? 'secondary' ?> me-3 p-2">
                                <i class="bi bi-<?= $current_status['icon'] ?? 'question-circle' ?> fs-5"></i>
                            </span>
                            <div>
                                <h5 class="mb-0"><?= $current_status['text'] ?? 'Statut inconnu' ?></h5>
                                <small class="text-muted">
                                    <?= 
                                        ($order['status'] ?? '') == 'pending' ? 'En attente de confirmation' :
                                        (($order['status'] ?? '') == 'preparing' ? 'Le restaurant prépare votre commande' :
                                        (($order['status'] ?? '') == 'delivering' ? 'Votre commande est en route' :
                                        (($order['status'] ?? '') == 'completed' ? 'Commande livrée avec succès' : 
                                        (($order['status'] ?? '') == 'cancelled' ? 'Commande annulée' : 'Statut inconnu'))))
                                    ?>
                                </small>
                            </div>
                        </div>
                        
                      
                        <div class="progress mb-3" style="height: 10px;">
                            <div class="progress-bar bg-<?= $current_status['class'] ?? 'secondary' ?>" 
                                 style="width: <?= $current_status['progress'] ?? 0 ?>%"></div>
                        </div>
                        
                        
                        <div class="d-flex justify-content-between text-center">
                            <div class="step <?= ($order['status'] ?? '') == 'pending' ? 'active' : '' ?>">
                                <div class="step-icon">
                                    <i class="bi bi-clock fs-4"></i>
                                </div>
                                <small>En attente</small>
                            </div>
                            <div class="step <?= ($order['status'] ?? '') == 'preparing' ? 'active' : '' ?>">
                                <div class="step-icon">
                                    <i class="bi bi-egg-fried fs-4"></i>
                                </div>
                                <small>Préparation</small>
                            </div>
                            <div class="step <?= ($order['status'] ?? '') == 'delivering' ? 'active' : '' ?>">
                                <div class="step-icon">
                                    <i class="bi bi-truck fs-4"></i>
                                </div>
                                <small>Livraison</small>
                            </div>
                            <div class="step <?= ($order['status'] ?? '') == 'completed' ? 'active' : '' ?>">
                                <div class="step-icon">
                                    <i class="bi bi-check-circle fs-4"></i>
                                </div>
                                <small>Terminé</small>
                            </div>
                        </div>
                    </div>
                </div>

               
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="bi bi-basket"></i> Votre commande
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Article</th>
                                        <th class="text-end">Prix unitaire</th>
                                        <th class="text-end">Quantité</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if (!empty($item['image'])): ?>
                                                        <img src="<?= htmlspecialchars($item['image'] ?? '') ?>" 
                                                             alt="<?= htmlspecialchars($item['dish_name'] ?? '') ?>"
                                                             class="rounded me-3" width="50">
                                                    <?php endif; ?>
                                                    <div>
                                                        <div><?= htmlspecialchars($item['dish_name'] ?? '') ?></div>
                                                        <small class="text-muted">
                                                            <?= htmlspecialchars($item['restaurant_name'] ?? '') ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-end"><?= number_format($item['price'] ?? 0, 2, ',', ' ') ?> €</td>
                                            <td class="text-end"><?= $item['quantity'] ?? 0 ?></td>
                                            <td class="text-end fw-bold">
                                                <?= number_format($item['subtotal'] ?? 0, 2, ',', ' ') ?> €
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end">Sous-total</td>
                                        <td class="text-end"><?= number_format(($order['total'] ?? 0) - 2.5, 2, ',', ' ') ?> €</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-end">Frais de service</td>
                                        <td class="text-end">2,50 €</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-end fw-bold">Total</td>
                                        <td class="text-end fw-bold text-primary">
                                            <?= number_format($order['total'] ?? 0, 2, ',', ' ') ?> €
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                
                <?php if (!empty($order['notes'])): ?>
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="bi bi-chat-text"></i> Instructions spéciales
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-0"><?= nl2br(htmlspecialchars($order['notes'] ?? '')) ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

          
            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="bi bi-truck"></i> Livraison
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6>Adresse</h6>
                            <p class="text-muted mb-0">
                                <?= nl2br(htmlspecialchars($order['delivery_address'] ?? '')) ?>
                            </p>
                        </div>
                        
                        <div class="mb-3">
                            <h6>Heure de livraison</h6>
                            <p class="text-muted mb-0">
                                <?= ($order['delivery_time'] ?? '') == 'asap' ? 'Livraison la plus rapide' : htmlspecialchars($order['delivery_time'] ?? '') ?>
                            </p>
                        </div>
                        
                        <div class="mb-3">
                            <h6>Mode de paiement</h6>
                            <p class="text-muted mb-0">
                                <?= ($order['payment_method'] ?? '') == 'card' ? 'Carte bancaire' : 
                                    (($order['payment_method'] ?? '') == 'cash' ? 'Espèces' : 
                                    htmlspecialchars($order['payment_method'] ?? 'Non spécifié')) ?>
                            </p>
                        </div>
                        
                        <div class="mb-3">
                            <h6>Contact</h6>
                            <p class="text-muted mb-1">
                                <i class="bi bi-telephone"></i> <?= htmlspecialchars($order['phone'] ?? '') ?>
                            </p>
                            <p class="text-muted mb-0">
                                <i class="bi bi-envelope"></i> <?= htmlspecialchars($order['email'] ?? '') ?>
                            </p>
                        </div>
                    </div>
                </div>

               
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title mb-3">
                            <i class="bi bi-lightning"></i> Actions
                        </h6>
                        <div class="d-grid gap-2">
                            <a href="order_tracking.php?id=<?= $order_id ?>" class="btn btn-primary">
                                <i class="bi bi-truck"></i> Suivre la livraison
                            </a>
                            <a href="javascript:window.print()" class="btn btn-outline-secondary">
                                <i class="bi bi-printer"></i> Imprimer la facture
                            </a>
                            <a href="reviews.php?order_id=<?= $order_id ?>" class="btn btn-outline-success">
                                <i class="bi bi-star"></i> Donner un avis
                            </a>
                            <?php if (($order['status'] ?? '') == 'completed'): ?>
                                <a href="reorder.php?id=<?= $order_id ?>" class="btn btn-outline-primary">
                                    <i class="bi bi-arrow-repeat"></i> Re-commander
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>

    document.getElementById('cancel-order')?.addEventListener('click', function() {
        if (confirm('Annuler cette commande ? Cette action est irréversible.')) {
            fetch('ajax/cancel_order.php?id=<?= $order_id ?>')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                });
        }
    });
    
  
    const style = document.createElement('style');
    style.textContent = `
        .step {
            flex: 1;
            position: relative;
        }
        .step-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: #e9ecef;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 5px;
        }
        .step.active .step-icon {
            background-color: #0d6efd;
            color: white;
        }
        .step.completed .step-icon {
            background-color: #198754;
            color: white;
        }
    `;
    document.head.appendChild(style);
    </script>
</body>
</html>