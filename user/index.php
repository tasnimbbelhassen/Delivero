<?php
session_start();
require_once '../admin/includes/config.php';


if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];


$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();


$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_orders,
        SUM(total) as total_spent,
        COUNT(CASE WHEN status = 'delivering' THEN 1 END) as delivering,
        COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed
    FROM orders 
    WHERE user_id = ?
");
$stmt->execute([$user_id]);
$stats = $stmt->fetch();


$stmt = $pdo->prepare("
    SELECT 
        o.*,
        (
            SELECT r.name 
            FROM order_items oi
            JOIN dishes d ON oi.dish_id = d.id
            JOIN restaurants r ON d.restaurant_id = r.id
            WHERE oi.order_id = o.id
            LIMIT 1
        ) as restaurant_name
    FROM orders o
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
    LIMIT 5
");
$stmt->execute([$user_id]);
$recent_orders = $stmt->fetchAll();


$stmt = $pdo->prepare("
    SELECT r.*, COUNT(DISTINCT o.id) as order_count
    FROM restaurants r
    LEFT JOIN dishes d ON r.id = d.restaurant_id
    LEFT JOIN order_items oi ON d.id = oi.dish_id
    LEFT JOIN orders o ON oi.order_id = o.id
    WHERE r.is_active = 1
    GROUP BY r.id
    ORDER BY order_count DESC
    LIMIT 6
");
$stmt->execute();
$popular_restaurants = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Delivero</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Dubai:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
         @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montez&display=swap");
         
        :root {
    --primary-green: #39c1c1;
    --dark-green: #216767;
    --bg-light: #fbfcf8;
    --card-bg: #ffffff;
    --text-dark: #216767;
    --text-gray: #888888;
    --border-color: #e0e0e0;
    --shadow-light: 0 4px 20px rgba(0, 0, 0, 0.05);
    --shadow-medium: 0 8px 30px rgba(0, 0, 0, 0.08);
    --success-color: #28a745;
    --warning-color: #ffc107;
    --danger-color: #dc3545;
    --info-color: #17a2b8;
}


body {
    background-color: var(--bg-light) !important;
}


.steps {
    display: flex;
    justify-content: space-between;
    position: relative;
    margin-bottom: 3rem;
    padding: 0 20px;
}

.steps::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 10%;
    right: 10%;
    height: 3px;
    background-color: var(--border-color);
    z-index: 1;
}

.step {
    position: relative;
    z-index: 2;
    text-align: center;
    flex: 1;
    background-color: var(--bg-light);
}

.step-number {
    width: 45px;
    height: 45px;
    line-height: 45px;
    border-radius: 50%;
    background-color: var(--border-color);
    color: var(--text-gray);
    margin: 0 auto 8px;
    font-weight: bold;
    position: relative;
    z-index: 2;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.step.active .step-number {
    background-color: var(--primary-green);
    color: white;
    box-shadow: 0 4px 12px rgba(57, 193, 193, 0.4);
    transform: scale(1.1);
}

.step.completed .step-number {
    background-color: var(--success-color);
    color: white;
    box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
}

.step.completed .step-number::after {
    content: '✓';
    font-size: 18px;
}

.step-label {
    font-size: 0.875rem;
    color: var(--text-gray);
    font-weight: 500;
    transition: all 0.3s ease;
}

.step.active .step-label {
    color: var(--primary-green);
    font-weight: 600;
}

.step.completed .step-label {
    color: var(--success-color);
}


.card {
    border: 1px solid var(--border-color) !important;
    box-shadow: var(--shadow-light) !important;
    border-radius: 15px !important;
    background-color: var(--card-bg) !important;
    overflow: hidden;
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: var(--shadow-medium) !important;
}

.card-header {
    background-color: var(--bg-light) !important;
    border-bottom: 2px solid var(--border-color) !important;
    padding: 1.25rem !important;
}

.card-header h5 {
    color: var(--text-dark) !important;
    font-weight: 600;
    margin: 0;
}

.card-body {
    padding: 1.5rem !important;
}


.form-label {
    color: var(--text-dark);
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.form-control,
.form-select {
    border: 2px solid var(--border-color) !important;
    border-radius: 10px !important;
    padding: 0.75rem 1rem !important;
    transition: all 0.3s ease;
}

.form-control:focus,
.form-select:focus {
    border-color: var(--primary-green) !important;
    box-shadow: 0 0 0 0.25rem rgba(57, 193, 193, 0.15) !important;
}

textarea.form-control {
    resize: vertical;
    min-height: 100px;
}

.form-control::placeholder {
    color: var(--text-gray);
    opacity: 0.6;
}


.invalid-feedback {
    color: var(--danger-color);
    font-size: 0.875rem;
    margin-top: 0.5rem;
}

.was-validated .form-control:invalid,
.was-validated .form-select:invalid {
    border-color: var(--danger-color) !important;
}

.was-validated .form-control:valid,
.was-validated .form-select:valid {
    border-color: var(--success-color) !important;
}


.form-check {
    border: 2px solid var(--border-color);
    border-radius: 12px;
    padding: 1.25rem;
    transition: all 0.3s ease;
    cursor: pointer;
    background-color: var(--card-bg);
}

.form-check:hover {
    border-color: var(--primary-green);
    background-color: rgba(57, 193, 193, 0.05);
    transform: translateY(-2px);
    box-shadow: var(--shadow-light);
}

.form-check-input {
    width: 20px;
    height: 20px;
    margin-top: 0.5rem;
    cursor: pointer;
}

.form-check-input:checked {
    background-color: var(--primary-green);
    border-color: var(--primary-green);
}

.form-check-input:checked ~ .form-check-label {
    color: var(--primary-green);
}

.form-check-input:checked ~ .form-check-label .fw-bold {
    color: var(--text-dark);
}

.form-check .bi {
    color: var(--primary-green);
}


#card-details {
    background-color: var(--bg-light);
    border-radius: 10px;
    padding: 1.5rem;
    border: 1px dashed var(--border-color);
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}


.btn-primary {
    background-color: var(--primary-green) !important;
    border-color: var(--primary-green) !important;
    color: white !important;
    font-weight: 600;
    border-radius: 12px !important;
    transition: all 0.3s ease;
}

.btn-primary:hover,
.btn-primary:focus {
    background-color: var(--dark-green) !important;
    border-color: var(--dark-green) !important;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(57, 193, 193, 0.3) !important;
}

.btn-lg {
    padding: 1rem 2rem !important;
    font-size: 1.1rem !important;
}


.alert {
    border-radius: 12px !important;
    border: none;
    padding: 1rem 1.5rem;
}

.alert-danger {
    background-color: rgba(220, 53, 69, 0.1) !important;
    color: var(--danger-color) !important;
    border-left: 4px solid var(--danger-color) !important;
}

.alert .bi {
    font-size: 1.2rem;
}


.sticky-top {
    position: sticky !important;
    top: 80px !important;
}

.sticky-top .card {
    border: 2px solid var(--border-color) !important;
}

.sticky-top hr {
    border-color: var(--border-color);
    opacity: 1;
    margin: 1rem 0;
}

.text-primary {
    color: var(--primary-green) !important;
}

.text-success {
    color: var(--success-color) !important;
}

.text-muted {
    color: var(--text-gray) !important;
}


.order-item {
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--border-color);
}

.order-item:last-child {
    border-bottom: none;
}


.small.text-muted p {
    display: flex;
    align-items: center;
    margin-bottom: 0.75rem;
}

.small.text-muted .bi {
    color: var(--primary-green);
    margin-right: 0.5rem;
    font-size: 1.1rem;
}


a {
    color: var(--primary-green);
    text-decoration: none;
    transition: color 0.2s ease;
}

a:hover {
    color: var(--dark-green);
    text-decoration: underline;
}


@media (max-width: 768px) {
    .steps {
        padding: 0 10px;
    }
    
    .steps::before {
        left: 5%;
        right: 5%;
    }
    
    .step-number {
        width: 40px;
        height: 40px;
        line-height: 40px;
    }
    
    .step-label {
        font-size: 0.75rem;
    }
    
    .card-body {
        padding: 1rem !important;
    }
    
    .form-check {
        margin-bottom: 1rem;
    }
    
    .sticky-top {
        position: static !important;
        margin-top: 2rem;
    }
}

@media (max-width: 576px) {
    .step-label {
        display: none;
    }
    
    .btn-lg {
        font-size: 1rem !important;
        padding: 0.875rem 1.5rem !important;
    }
}


@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.card {
    animation: fadeIn 0.5s ease;
}

.card:nth-child(2) {
    animation-delay: 0.1s;
}

.card:nth-child(3) {
    animation-delay: 0.2s;
}


.btn-primary:disabled {
    background-color: var(--text-gray) !important;
    border-color: var(--text-gray) !important;
    opacity: 0.6;
}


.form-check-input:focus {
    box-shadow: 0 0 0 0.25rem rgba(57, 193, 193, 0.25) !important;
}


.fs-5 {
    font-size: 1.35rem !important;
}

.fw-bold {
    font-weight: 600 !important;
}


.border-bottom {
    border-color: var(--border-color) !important;
}


.bi-shield-check,
.bi-truck,
.bi-clock {
    color: var(--primary-green);
}
.hero-section {
    border-radius: 0;
    padding: 0;
    overflow: visible;
    min-height: 450px;
    position: relative;
    box-shadow: none;
    margin-left: -15px;
    margin-right: -15px;
    margin-top: -15px;
}

.hero-content {
    display: flex;
    align-items: center;
    min-height: 450px;
    max-width: 1320px;
    margin: 0 auto;
    padding: 0 15px;
}

.hero-image {
    flex: 0 0 40%;
    position: relative;
    height: 450px;
    display: flex;
    align-items: flex-end;
    justify-content: center;
    padding-bottom: 2rem;
    margin-top: -15px;
}

.hero-image::before {
    content: '';
    position: absolute;
    width: 420px;
    height: 420px;
    background: #a3a1a11c;
    border-radius: 50%;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 0;
    backdrop-filter: blur(20px);
    border: 2px solid rgba(255, 255, 255, 0.2);
}

.hero-image img {
    width: 98%;
    height: 98%;
    object-fit: contain;
    object-position: bottom center;
    filter: drop-shadow(0 10px 30px rgba(0, 0, 0, 0.2));
    animation: float 3s ease-in-out infinite;
    position: relative;
    z-index: 1;
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-15px); }
}

.hero-text {
    flex: 1;
    padding: 3rem 4rem 3rem 2rem;
    color: white;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.hero-title {
    font-size: 80px;
    font-weight: 700;
    margin-bottom: 1rem;
    line-height: 1.2;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    color: var(--text-dark);
    text-align: center;
    font-family: 'Dubai', sans-serif;
}

.hero-plus{
    font-family: 'Montez', cursive; 
    color: var(--primary-green); 
    font-size: 80px;
}

.hero-subtitle {
    font-size: 18px;
    margin-bottom: 2.5rem;
    opacity: 0.95;
    font-weight: 350;
    line-height: 1.6;
    color: #2167678d;
    text-align: center;
}

.hero-meta {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    align-items: center; 
    text-align: center;  
}

.member-info {
    font-size: 1rem;
    opacity: 0.9;
    display: flex;
    gap: 0.6rem;
    background: rgba(255, 255, 255, 0.15);
    padding: 0.75rem 1.25rem;
    border-radius: 50px;
    backdrop-filter: blur(10px);
    width: fit-content;
    color: #2167678d;
    margin-top: -5%;
}

.hero-btn {
    background: white;
    color: var(--primary-green);
    border: none;
    padding: 1rem 2.5rem;
    border-radius: 50px;
    font-weight: 600;
    font-size: 1.05rem;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.7rem;
    text-decoration: none;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    margin-bottom: 10%;
    margin-top: 5%;
}

.hero-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.25);
    color: var(--dark-green);
    background: #f8f9fa;
}

.hero-btn i {
    font-size: 1.2rem;
    transition: transform 0.3s ease;
}

.hero-btn:hover i {
    transform: scale(1.1);
}


@media (max-width: 992px) {
    .hero-section {
        margin-left: 0;
        margin-right: 0;
    }
    
    .hero-content {
        flex-direction: column-reverse;
        min-height: auto;
        padding: 0;
    }
    
    .hero-image {
        flex: 0 0 280px;
        width: 100%;
        height: 280px;
        padding-bottom: 1rem;
    }
    
    .hero-image img {
        width: 70%;
        height: 70%;
    }
    
    .hero-text {
        padding: 2.5rem 2rem;
        text-align: center;
        align-items: center;
    }
    
    .hero-title {
        font-size: 2.2rem;
    }
    
    .hero-subtitle {
        font-size: 1.1rem;
        margin-bottom: 2rem;
    }
    
    .member-info {
        justify-content: center;
    }
}

@media (max-width: 576px) {
    .hero-section {
        min-height: auto;
    }
    
    .hero-title {
        font-size: 1.85rem;
    }
    
    .hero-subtitle {
        font-size: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .hero-text {
        padding: 2rem 1.5rem;
    }
    
    .hero-btn {
        padding: 0.85rem 2rem;
        font-size: 0.95rem;
    }
    
    .hero-member-info {
        font-size: 0.9rem;
        padding: 0.6rem 1rem;
    }
}


        </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container mt-4">
       
        <div class="row mb-4">
    <div class="col-12">
       <div class="hero-section">
    <div class="hero-content">
        <div class="hero-image">
            <img src="assets/Acceuil.boy2-removebg-preview.png" alt="Livreur Delivero">
        </div>
        
        
        <div class="hero-text">
            <p class="hero-title">
                Bon retour<span class="hero-plus">,</span>
                <br><?= htmlspecialchars($user['full_name'] ?? 'Utilisateur') ?> !
</p>
            <p class="hero-subtitle">
                Que souhaitez-vous commander aujourd'hui ?
            </p>
            
            <div class="hero-meta">
                <p class="member-info">
                    Membre depuis <?= date('d/m/Y', strtotime($user['created_at'] ?? date('Y-m-d'))) ?>
                </p>
                
                <a href="home.php" class="hero-btn">
                    <i class="bi bi-basket"></i>
                    Commander maintenant
                </a>
            </div>
        </div>
    </div>
</div>
</div>

        <div class="row">
            <div class="col-lg-8">
                <div class="row mb-4">
                    <div class="col-md-3 col-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <div class="text-primary mb-2">
                                    <i class="bi bi-cart-check fs-1"></i>
                                </div>
                                <h3 class="card-title"><?= $stats['total_orders'] ?? 0 ?></h3>
                                <p class="card-text text-muted small">Commandes totales</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 col-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <div class="text-success mb-2">
                                    <i class="bi bi-currency-euro fs-1"></i>
                                </div>
                                <h3 class="card-title"><?= number_format($stats['total_spent'] ?? 0, 0, ',', ' ') ?>€</h3>
                                <p class="card-text text-muted small">Total dépensé</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 col-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <div class="text-warning mb-2">
                                    <i class="bi bi-truck fs-1"></i>
                                </div>
                                <h3 class="card-title"><?= $stats['delivering'] ?? 0 ?></h3>
                                <p class="card-text text-muted small">En livraison</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 col-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <div class="text-info mb-2">
                                    <i class="bi bi-star fs-1"></i>
                                </div>
                                <h3 class="card-title"><?= $stats['completed'] ?? 0 ?></h3>
                                <p class="card-text text-muted small">Commandées terminées</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dernières commandes -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="bi bi-clock-history"></i> Commandes récentes
                            <a href="orders.php" class="float-end btn btn-sm btn-outline-primary">Tout voir</a>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if ($recent_orders): ?>
                            <div class="list-group">
                                <?php foreach ($recent_orders as $order): ?>
                                    <a href="order_details.php?id=<?= $order['id'] ?>" 
                                       class="list-group-item list-group-item-action border-0">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1">Commande #<?= htmlspecialchars($order['order_number'] ?? '') ?></h6>
                                                <small class="text-muted">
                                                    <i class="bi bi-shop"></i>
                                                    <?php if (!empty($order['restaurant_name'])): ?>
                                                        <?= htmlspecialchars($order['restaurant_name']) ?>
                                                    <?php else: ?>
                                                        <span class="text-warning">
                                                            <i class="bi bi-question-circle"></i> À confirmer
                                                        </span>
                                                    <?php endif; ?>
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-<?= 
                                                    $order['status'] == 'pending' ? 'warning' :
                                                    ($order['status'] == 'preparing' ? 'info' :
                                                    ($order['status'] == 'delivering' ? 'primary' :
                                                    ($order['status'] == 'completed' ? 'success' : 'danger')))
                                                ?>">
                                                    <?= htmlspecialchars($order['status'] ?? '') ?>
                                                </span>
                                                <div class="mt-1">
                                                    <strong><?= number_format($order['total'] ?? 0, 2, ',', ' ') ?>€</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="bi bi-cart-x display-1 text-muted"></i>
                                <h5 class="mt-3">Aucune commande</h5>
                                <p class="text-muted">Vous n'avez pas encore passé de commande</p>
                                <a href="home.php" class="btn btn-primary">Commander maintenant</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

           
            <div class="col-lg-4">
                
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="bi bi-lightning"></i> Actions rapides
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="home.php" class="btn btn-primary">
                                <i class="bi bi-shop"></i> Commander
                            </a>
                            <a href="cart.php" class="btn btn-outline-primary">
                                <i class="bi bi-cart"></i> Voir mon panier
                            </a>
                            <a href="orders.php" class="btn btn-outline-primary">
                                <i class="bi bi-list-check"></i> Mes commandes
                            </a>
                            <a href="profile.php" class="btn btn-outline-primary">
                                <i class="bi bi-person"></i> Mon profil
                            </a>
                        </div>
                    </div>
                </div>

              
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="bi bi-fire"></i> Restaurants populaires
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <?php foreach ($popular_restaurants as $restaurant): ?>
                                <a href="restaurant_details.php?id=<?= $restaurant['id'] ?>" 
                                   class="list-group-item list-group-item-action border-0">
                                    <div class="d-flex align-items-center">
                                        <?php if (!empty($restaurant['image'])): ?>
                                            <img src="<?= htmlspecialchars($restaurant['image'] ?? '') ?>" 
                                                 alt="<?= htmlspecialchars($restaurant['name'] ?? '') ?>"
                                                 class="rounded me-3" width="50" height="50">
                                        <?php else: ?>
                                            <div class="rounded bg-light d-flex align-items-center justify-content-center me-3" 
                                                 style="width: 50px; height: 50px;">
                                                <i class="bi bi-shop text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1"><?= htmlspecialchars($restaurant['name'] ?? '') ?></h6>
                                            <small class="text-muted">
                                                <i class="bi bi-clock"></i> <?= htmlspecialchars($restaurant['delivery_time'] ?? 'Non spécifié') ?>
                                                <span class="ms-2">
                                                    <i class="bi bi-star-fill text-warning"></i> <?= htmlspecialchars($restaurant['rating'] ?? '0') ?>
                                                </span>
                                            </small>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/user.js"></script>
</body>
</html>