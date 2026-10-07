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
        COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_orders,
        MAX(created_at) as last_order
    FROM orders 
    WHERE user_id = ?
");
$stmt->execute([$user_id]);
$stats = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon profil - Delivero</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        
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
    background-color: var(--bg-light);
    color: var(--text-dark);
    font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
}


.card {
    background-color: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    box-shadow: var(--shadow-light);
}

.card-header {
    background-color: var(--card-bg);
    border-bottom: 1px solid var(--border-color);
    font-weight: 600;
    color: var(--text-dark);
}

.card-body {
    padding: 1.5rem;
}


.avatar-placeholder {
    background-color: var(--primary-green);
    color: white;
    font-size: 2rem;
}


dt {
    font-weight: 600;
    color: var(--text-dark);
}

dd {
    color: var(--text-gray);
}


.btn-primary {
    background-color: var(--primary-green);
    border-color: var(--primary-green);
}

.btn-primary:hover {
    background-color: var(--dark-green);
    border-color: var(--dark-green);
}

.btn-outline-primary {
    color: var(--primary-green);
    border-color: var(--primary-green);
}

.btn-outline-primary:hover {
    background-color: var(--primary-green);
    color: white;
}

.btn-outline-success {
    color: var(--success-color);
    border-color: var(--success-color);
}

.btn-outline-success:hover {
    background-color: var(--success-color);
    color: white;
}

.btn-outline-warning {
    color: var(--warning-color);
    border-color: var(--warning-color);
}

.btn-outline-warning:hover {
    background-color: var(--warning-color);
    color: white;
}


.progress {
    background-color: rgba(57, 193, 193, 0.1);
    border-radius: 10px;
    overflow: hidden;
}

.progress-bar {
    background-color: var(--success-color);
}


.card-header i {
    color: var(--primary-green);
    margin-right: 0.5rem;
}


.d-grid.gap-2 .btn {
    font-weight: 600;
}


@media (max-width: 992px) {
    .card {
        margin-bottom: 1.5rem;
    }
}

@media (max-width: 576px) {
    .avatar-placeholder {
        width: 80px !important;
        height: 80px !important;
    }
}

    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container mt-4">
        <div class="row">
            <div class="col-lg-8">
                
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="bi bi-person"></i> Informations personnelles
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 text-center mb-4">
                                <div class="avatar-placeholder rounded-circle bg-primary d-inline-flex align-items-center justify-content-center mb-3"
                                     style="width: 100px; height: 100px;">
                                    <i class="bi bi-person text-white fs-1"></i>
                                </div>
                                <a href="edit_profile.php" class="btn btn-primary btn-sm">
                                    <i class="bi bi-pencil"></i> Modifier
                                </a>
                            </div>
                            
                            <div class="col-md-9">
                                <dl class="row">
                                    <dt class="col-sm-3">Nom complet</dt>
                                    <dd class="col-sm-9"><?= htmlspecialchars($user['full_name']) ?></dd>
                                    
                                    <dt class="col-sm-3">Email</dt>
                                    <dd class="col-sm-9"><?= htmlspecialchars($user['email']) ?></dd>
                                    
                                    <dt class="col-sm-3">Téléphone</dt>
                                    <dd class="col-sm-9"><?= htmlspecialchars($user['phone'] ?? 'Non renseigné') ?></dd>
                                    
                                    <dt class="col-sm-3">Adresse</dt>
                                    <dd class="col-sm-9"><?= nl2br(htmlspecialchars($user['address'] ?? 'Non renseignée')) ?></dd>
                                    
                                    <dt class="col-sm-3">Membre depuis</dt>
                                    <dd class="col-sm-9"><?= date('d/m/Y', strtotime($user['created_at'])) ?></dd>
                                    
                                    <dt class="col-sm-3">Dernière connexion</dt>
                                    <dd class="col-sm-9"><?= $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Jamais' ?></dd>
                                </dl>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <a href="edit_profile.php" class="btn btn-primary me-2">
                                <i class="bi bi-pencil"></i> Modifier profil
                            </a>
                            <a href="change_password.php" class="btn btn-outline-primary me-2">
                                <i class="bi bi-lock"></i> Changer mot de passe
                            </a>
                            <a href="addresses.php" class="btn btn-outline-primary">
                                <i class="bi bi-geo-alt"></i> Mes adresses
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            
            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="bi bi-bar-chart"></i> Mes statistiques
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Commandes totales</span>
                                <strong><?= $stats['total_orders'] ?? 0 ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Commandes terminées</span>
                                <strong><?= $stats['completed_orders'] ?? 0 ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Total dépensé</span>
                                <strong><?= number_format($stats['total_spent'] ?? 0, 2, ',', ' ') ?> €</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Dernière commande</span>
                                <strong>
                                    <?= $stats['last_order'] ? date('d/m/Y', strtotime($stats['last_order'])) : '--/--/----' ?>
                                </strong>
                            </div>
                        </div>
                        
                        <hr>
                        
                        
                        <div class="text-center">
                            <h6 class="text-primary mb-3">
                                <i class="bi bi-gift"></i> Programme fidélité
                            </h6>
                            <div class="progress mb-2" style="height: 20px;">
                                <div class="progress-bar bg-success" 
                                     style="width: <?= min(($stats['completed_orders'] ?? 0) * 10, 100) ?>%">
                                </div>
                            </div>
                            <p class="small text-muted">
                                <?= $stats['completed_orders'] ?? 0 ?> commandes sur 10
                                <br>Prochaine récompense: 10% de réduction
                            </p>
                        </div>
                    </div>
                </div>
                
                
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title mb-3">
                            <i class="bi bi-lightning"></i> Actions rapides
                        </h6>
                        <div class="d-grid gap-2">
                            <a href="orders.php" class="btn btn-outline-primary">
                                <i class="bi bi-list-check"></i> Mes commandes
                            </a>
                            <a href="home.php" class="btn btn-outline-success">
                                <i class="bi bi-shop"></i> Commander
                            </a>
                            <a href="cart.php" class="btn btn-outline-warning">
                                <i class="bi bi-cart"></i> Mon panier
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>