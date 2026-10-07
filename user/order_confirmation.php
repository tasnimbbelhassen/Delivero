<?php
session_start();
require_once '../admin/includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$order_id = $_GET['id'] ?? 0;

// Récupérer commande
$stmt = $pdo->prepare("
    SELECT o.*, u.full_name, u.phone, u.email
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: orders.php');
    exit();
}


$stmt = $pdo->prepare("
    SELECT oi.*, d.name as dish_name, d.image
    FROM order_items oi
    LEFT JOIN dishes d ON oi.dish_id = d.id
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation - Delivero</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <?php include 'includes/header.php'; ?>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <div class="card border-0 shadow-lg">
                    <div class="card-body p-5 text-center">
                        <div class="mb-4">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success"
                                 style="width: 80px; height: 80px;">
                                <i class="bi bi-check-lg text-white fs-1"></i>
                            </div>
                        </div>
                        
                        <h1 class="display-6 fw-bold text-success mb-3">Commande confirmée !</h1>
                        <p class="lead text-muted mb-4">
                            Votre commande #<?= $order['order_number'] ?> a été passée avec succès
                        </p>
                        
                        <div class="row mb-5">
                            <div class="col-md-6 mb-3">
                                <div class="card h-100 border">
                                    <div class="card-body">
                                        <h5 class="card-title text-primary">
                                            <i class="bi bi-receipt"></i> Commande
                                        </h5>
                                        <ul class="list-unstyled">
                                            <li class="mb-2">
                                                <strong>N°:</strong> 
                                                <span class="badge bg-primary">#<?= $order['order_number'] ?></span>
                                            </li>
                                            <li class="mb-2">
                                                <strong>Date:</strong> 
                                                <?= date('d/m/Y à H:i', strtotime($order['created_at'])) ?>
                                            </li>
                                            <li class="mb-2">
                                                <strong>Statut:</strong> 
                                                <span class="badge bg-warning">En attente</span>
                                            </li>
                                            <li class="mb-2">
                                                <strong>Total:</strong> 
                                                <span class="fw-bold"><?= number_format($order['total'], 2, ',', ' ') ?> €</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="card h-100 border">
                                    <div class="card-body">
                                        <h5 class="card-title text-primary">
                                            <i class="bi bi-truck"></i> Livraison
                                        </h5>
                                        <ul class="list-unstyled">
                                            <li class="mb-2">
                                                <strong>Adresse:</strong> 
                                                <div><?= nl2br(htmlspecialchars($order['delivery_address'])) ?></div>
                                            </li>
                                            <li class="mb-2">
                                                <strong>Heure:</strong> 
                                                <?= $order['delivery_time'] == 'asap' ? 'Livraison rapide' : $order['delivery_time'] ?>
                                            </li>
                                            <li class="mb-2">
                                                <strong>Paiement:</strong> 
                                                <?= $order['payment_method'] == 'card' ? 'Carte bancaire' : 'Espèces' ?>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        
                        <div class="card border mb-5">
                            <div class="card-header bg-light">
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
                                                <th class="text-end">Prix</th>
                                                <th class="text-end">Qté</th>
                                                <th class="text-end">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($items as $item): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($item['dish_name']) ?></td>
                                                    <td class="text-end"><?= number_format($item['price'], 2, ',', ' ') ?> €</td>
                                                    <td class="text-end"><?= $item['quantity'] ?></td>
                                                    <td class="text-end fw-bold">
                                                        <?= number_format($item['subtotal'], 2, ',', ' ') ?> €
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="3" class="text-end fw-bold">Total:</td>
                                                <td class="text-end fw-bold text-primary">
                                                    <?= number_format($order['total'], 2, ',', ' ') ?> €
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        
                        <div class="d-grid gap-3 d-md-flex justify-content-md-center">
                            <a href="orders.php" class="btn btn-primary btn-lg px-4">
                                <i class="bi bi-list-check"></i> Mes commandes
                            </a>
                            <a href="home.php" class="btn btn-outline-primary btn-lg px-4">
                                <i class="bi bi-shop"></i> Nouvelle commande
                            </a>
                            <button class="btn btn-outline-secondary btn-lg px-4" onclick="window.print()">
                                <i class="bi bi-printer"></i> Imprimer
                            </button>
                        </div>
                    </div>
                </div>
                
                
                <div class="text-center mt-4">
                    <p class="text-muted">
                        Vous recevrez un email de confirmation. Suivez votre commande dans 
                        <a href="order_tracking.php?id=<?= $order_id ?>">suivi en temps réel</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    setTimeout(() => {
        const colors = ['#0d6efd', '#198754', '#ffc107', '#dc3545'];
        for (let i = 0; i < 50; i++) {
            const confetti = document.createElement('div');
            confetti.style.cssText = `
                position: fixed;
                width: 10px;
                height: 10px;
                background-color: ${colors[Math.floor(Math.random() * colors.length)]};
                border-radius: 50%;
                top: -20px;
                left: ${Math.random() * 100}vw;
                animation: fall ${Math.random() * 2 + 1}s linear forwards;
                z-index: 9999;
            `;
            document.body.appendChild(confetti);
            setTimeout(() => confetti.remove(), 3000);
        }
        
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fall {
                to { transform: translateY(100vh) rotate(${Math.random() * 360}deg); }
            }
        `;
        document.head.appendChild(style);
    }, 500);
    </script>
</body>
</html>