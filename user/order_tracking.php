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
    SELECT o.*, 
           TIMESTAMPDIFF(MINUTE, o.created_at, NOW()) as minutes_elapsed
    FROM orders o
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: orders.php');
    exit();
}

// Statut
$status_steps = [
    'pending' => ['step' => 1, 'icon' => 'clock', 'label' => 'Commande reçue'],
    'preparing' => ['step' => 2, 'icon' => 'egg-fried', 'label' => 'En préparation'],
    'delivering' => ['step' => 3, 'icon' => 'truck', 'label' => 'En livraison'],
    'completed' => ['step' => 4, 'icon' => 'check-circle', 'label' => 'Livrée']
];
$current_step = $status_steps[$order['status']] ?? $status_steps['pending'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suivi commande #<?= $order['order_number'] ?> - Delivero</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
    .tracking-container {
        max-width: 800px;
        margin: 0 auto;
    }
    .timeline {
        position: relative;
        padding-left: 50px;
        margin: 2rem 0;
    }
    .timeline-step {
        position: relative;
        margin-bottom: 2rem;
    }
    .timeline-step.completed .step-icon {
        background-color: #198754;
        color: white;
    }
    .timeline-step.active .step-icon {
        background-color: #0d6efd;
        color: white;
        animation: pulse 2s infinite;
    }
    .timeline-step.pending .step-icon {
        background-color: #e9ecef;
        color: #6c757d;
    }
    .step-icon {
        position: absolute;
        left: -50px;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 3px solid white;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .timeline::before {
        content: '';
        position: absolute;
        left: 30px;
        top: 0;
        bottom: 0;
        width: 2px;
        background-color: #dee2e6;
    }
    .driver-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        overflow: hidden;
    }
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.7); }
        70% { box-shadow: 0 0 0 10px rgba(13, 110, 253, 0); }
        100% { box-shadow: 0 0 0 0 rgba(13, 110, 253, 0); }
    }
    #map {
        height: 300px;
        border-radius: 15px;
        overflow: hidden;
    }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container mt-4">
        <div class="tracking-container">
            
            <div class="text-center mb-5">
                <h1 class="h3 mb-3">
                    <i class="bi bi-truck"></i> Suivi en direct
                </h1>
                <p class="text-muted">
                    Commande #<?= $order['order_number'] ?> • 
                    <?= date('d/m/Y à H:i', strtotime($order['created_at'])) ?>
                </p>
            </div>

            
            <div class="card shadow-sm mb-4">
                <div class="card-body p-0">
                    <div id="map"></div>
                </div>
            </div>

            
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <i class="bi bi-clock-history"></i> Progression de votre commande
                    </h5>
                    
                    <div class="timeline">
                        <?php foreach ($status_steps as $status => $step): ?>
                            <div class="timeline-step 
                                <?= $step['step'] < $current_step['step'] ? 'completed' : 
                                   ($step['step'] == $current_step['step'] ? 'active' : 'pending') ?>">
                                <div class="step-icon">
                                    <i class="bi bi-<?= $step['icon'] ?>"></i>
                                </div>
                                <div class="step-content">
                                    <h6 class="mb-1"><?= $step['label'] ?></h6>
                                    <p class="text-muted mb-2 small">
                                        <?php if ($step['step'] < $current_step['step']): ?>
                                            <i class="bi bi-check-circle text-success"></i> Terminé
                                        <?php elseif ($step['step'] == $current_step['step']): ?>
                                            <i class="bi bi-clock text-primary"></i> En cours
                                        <?php else: ?>
                                            <i class="bi bi-clock text-muted"></i> À venir
                                        <?php endif; ?>
                                    </p>
                                    <p class="small text-muted">
                                        <?php 
                                        $times = [
                                            1 => 'Votre commande a été reçue',
                                            2 => 'Le restaurant prépare vos plats',
                                            3 => 'Votre livreur est en route',
                                            4 => 'Commande livrée'
                                        ];
                                        echo $times[$step['step']];
                                        ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            
            <div class="card shadow-sm mb-4 driver-card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="text-white mb-3">
                                <i class="bi bi-person-badge"></i> Votre livreur
                            </h5>
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-white rounded-circle d-flex align-items-center justify-content-center me-3"
                                     style="width: 60px; height: 60px;">
                                    <i class="bi bi-person fs-3 text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="text-white mb-1" id="driver-name">Mohamed</h6>
                                    <p class="text-white-50 mb-0 small">
                                        <i class="bi bi-star-fill"></i> 4.8 • 150+ livraisons
                                    </p>
                                </div>
                            </div>
                            <div class="text-white-50 small">
                                <p class="mb-1">
                                    <i class="bi bi-phone"></i> 
                                    <span id="driver-phone">+216 23 456 789</span>
                                </p>
                                <p class="mb-0">
                                    <i class="bi bi-clock"></i> 
                                    Temps estimé: <span id="eta">20-30 minutes</span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <button class="btn btn-light mb-2" id="call-driver">
                                <i class="bi bi-telephone"></i> Appeler
                            </button>
                            <button class="btn btn-outline-light" id="message-driver">
                                <i class="bi bi-chat"></i> Message
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="row">
                <div class="col-md-6">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="bi bi-info-circle"></i> Informations
                            </h6>
                            <ul class="list-unstyled small">
                                <li class="mb-2">
                                    <strong>Commande:</strong> #<?= $order['order_number'] ?>
                                </li>
                                <li class="mb-2">
                                    <strong>Total:</strong> <?= number_format($order['total'], 2, ',', ' ') ?> €
                                </li>
                                <li class="mb-2">
                                    <strong>Paiement:</strong> 
                                    <?= $order['payment_method'] == 'card' ? 'Carte bancaire' : 'Espèces' ?>
                                </li>
                                <li>
                                    <strong>Statut:</strong> 
                                    <span class="badge bg-<?= 
                                        $order['status'] == 'pending' ? 'warning' :
                                        ($order['status'] == 'preparing' ? 'info' :
                                        ($order['status'] == 'delivering' ? 'primary' : 'success'))
                                    ?>">
                                        <?= $order['status'] ?>
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="bi bi-geo-alt"></i> Adresse de livraison
                            </h6>
                            <p class="mb-0 small">
                                <?= nl2br(htmlspecialchars($order['delivery_address'])) ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="text-center mb-5">
                <a href="order_details.php?id=<?= $order_id ?>" class="btn btn-outline-primary me-2">
                    <i class="bi bi-arrow-left"></i> Détails commande
                </a>
                <a href="orders.php" class="btn btn-primary">
                    <i class="bi bi-list-check"></i> Mes commandes
                </a>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

   
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/tracking.js"></script>
    
    <script>
    
    let map = L.map('map').setView([36.8065, 10.1815], 13);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(map);
    
    
    const restaurantPos = [36.8065, 10.1815];
    const deliveryPos = [36.8065 + (Math.random() * 0.01 - 0.005), 10.1815 + (Math.random() * 0.01 - 0.005)];
    
    
    L.marker(restaurantPos)
        .addTo(map)
        .bindPopup('Restaurant')
        .openPopup();
    
    L.marker(deliveryPos)
        .addTo(map)
        .bindPopup('Votre adresse');
    
  
    L.polyline([restaurantPos, deliveryPos], {color: 'blue'}).addTo(map);
    
  
    function updateDriverPosition() {
        const driverMarker = L.marker([
            36.8065 + (Math.random() * 0.005 - 0.0025),
            10.1815 + (Math.random() * 0.005 - 0.0025)
        ]).addTo(map);
        
        driverMarker.bindPopup('Votre livreur').openPopup();
    }
    
   
    function updateTracking() {
        fetch('ajax/track_order.php?id=<?= $order_id ?>')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    
                    document.getElementById('driver-name').textContent = data.driver.name;
                    document.getElementById('driver-phone').textContent = data.driver.phone;
                    document.getElementById('eta').textContent = data.order.estimated_time;
                    
                    
                    if (data.driver.latitude && data.driver.longitude) {
                        L.marker([data.driver.latitude, data.driver.longitude])
                            .addTo(map)
                            .bindPopup('Livreur: ' + data.driver.name);
                    }
                }
            });
    }
    
    
    setInterval(updateTracking, 30000);
    updateTracking();
    
   
    document.getElementById('call-driver')?.addEventListener('click', function() {
        const phone = document.getElementById('driver-phone').textContent;
        window.location.href = 'tel:' + phone.replace(/\s+/g, '');
    });
    
    document.getElementById('message-driver')?.addEventListener('click', function() {
        const phone = document.getElementById('driver-phone').textContent;
        window.location.href = 'sms:' + phone.replace(/\s+/g, '');
    });
    </script>
</body>
</html>