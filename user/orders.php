<?php
session_start();
require_once '../admin/includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    $order_id = $_POST['order_id'] ?? 0;
    
   
    $stmt = $pdo->prepare("SELECT id FROM orders WHERE id = ? AND user_id = ? AND status = 'pending'");
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch();
    
    if ($order) {
        $update_stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
        if ($update_stmt->execute([$order_id])) {
            $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Commande annulée avec succès.'];
        } else {
            $_SESSION['flash_message'] = ['type' => 'danger', 'text' => 'Erreur lors de l\'annulation.'];
        }
    } else {
        $_SESSION['flash_message'] = ['type' => 'danger', 'text' => 'Commande non trouvée ou ne peut pas être annulée.'];
    }
    
    header('Location: orders.php');
    exit();
}


$status = $_GET['status'] ?? '';
$sql = "SELECT o.*, 
               COUNT(oi.id) as items_count,
               GROUP_CONCAT(d.name SEPARATOR ', ') as dish_names
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        LEFT JOIN dishes d ON oi.dish_id = d.id
        WHERE o.user_id = ?";
$params = [$user_id];

if ($status) {
    $sql .= " AND o.status = ?";
    $params[] = $status;
}

$sql .= " GROUP BY o.id ORDER BY o.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();


$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(total) as spent,
        COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
        COUNT(CASE WHEN status = 'delivering' THEN 1 END) as delivering
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
    <title>Mes commandes - Delivero</title>
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
    --transition-speed: 0.3s;
}


* {
    box-sizing: border-box;
}

body {
    background-color: var(--bg-light);
    color: var(--text-dark);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    line-height: 1.6;
    margin: 0;
    padding: 0;
}

main.container {
    padding-top: 2rem;
    padding-bottom: 4rem;
    max-width: 1200px;
}


h1, h2, h3, h4, h5, h6 {
    color: var(--dark-green);
    font-weight: 700;
    margin-bottom: 1rem;
}

.h3 {
    color: var(--dark-green);
    font-weight: 700;
    font-size: 1.75rem;
}

.h3 i {
    color: var(--primary-green);
    margin-right: 0.5rem;
}

p {
    margin-bottom: 1rem;
}

.text-muted {
    color: var(--text-gray) !important;
}

.text-primary {
    color: var(--primary-green) !important;
}

.text-success {
    color: var(--success-color) !important;
}

.text-warning {
    color: var(--warning-color) !important;
}

.text-info {
    color: var(--info-color) !important;
}

.text-danger {
    color: var(--danger-color) !important;
}


.d-flex.justify-content-between.align-items-center {
    margin-bottom: 1rem;
}

.d-flex.justify-content-between.align-items-center .btn {
    white-space: nowrap;
}


.card {
    border: 1px solid var(--border-color);
    border-radius: 12px;
    transition: all var(--transition-speed) ease;
    background-color: var(--card-bg);
    overflow: hidden;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-medium);
}

.card-body {
    padding: 1.5rem;
}

.card.border-0 {
    border: none;
}


.card.h-100 {
    height: 100%;
}

.card-body.text-center {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.card-body.text-center .bi {
    transition: transform var(--transition-speed) ease;
}

.card:hover .card-body.text-center .bi {
    transform: scale(1.15) rotate(5deg);
}

.card-title {
    font-weight: 700;
    font-size: 2rem;
    margin-bottom: 0.5rem;
    color: var(--dark-green);
    line-height: 1.2;
}

.card-text {
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0;
    font-weight: 500;
}

.card-text.text-muted.small {
    text-transform: uppercase;
}


.card .text-primary .bi {
    color: var(--primary-green);
}

.card .text-success .bi {
    color: var(--success-color);
}

.card .text-warning .bi {
    color: var(--warning-color);
}

.card .text-info .bi {
    color: var(--info-color);
}


.btn {
    border-radius: 8px;
    font-weight: 500;
    transition: all var(--transition-speed) ease;
    border: 2px solid transparent;
    padding: 0.5rem 1rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
    cursor: pointer;
}

.btn i {
    font-size: 1rem;
}


.btn-primary {
    background-color: var(--primary-green);
    border-color: var(--primary-green);
    color: white;
}

.btn-primary:hover,
.btn-primary:focus {
    background-color: var(--dark-green);
    border-color: var(--dark-green);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(57, 193, 193, 0.3);
}


.btn-outline-primary {
    color: var(--primary-green);
    border-color: var(--primary-green);
    background-color: transparent;
}

.btn-outline-primary:hover,
.btn-outline-primary.active {
    background-color: var(--primary-green);
    border-color: var(--primary-green);
    color: white;
}

.btn-outline-warning {
    color: var(--warning-color);
    border-color: var(--warning-color);
    background-color: transparent;
}

.btn-outline-warning:hover,
.btn-outline-warning.active {
    background-color: var(--warning-color);
    border-color: var(--warning-color);
    color: #000;
}

.btn-outline-info {
    color: var(--info-color);
    border-color: var(--info-color);
    background-color: transparent;
}

.btn-outline-info:hover,
.btn-outline-info.active {
    background-color: var(--info-color);
    border-color: var(--info-color);
    color: white;
}

.btn-outline-success {
    color: var(--success-color);
    border-color: var(--success-color);
    background-color: transparent;
}

.btn-outline-success:hover,
.btn-outline-success.active {
    background-color: var(--success-color);
    border-color: var(--success-color);
    color: white;
}

.btn-outline-danger {
    color: var(--danger-color);
    border-color: var(--danger-color);
    background-color: transparent;
}

.btn-outline-danger:hover {
    background-color: var(--danger-color);
    border-color: var(--danger-color);
    color: white;
}


.btn-secondary {
    background-color: #6c757d;
    border-color: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background-color: #5a6268;
    border-color: #545b62;
}

.btn-danger {
    background-color: var(--danger-color);
    border-color: var(--danger-color);
    color: white;
}

.btn-danger:hover {
    background-color: #c82333;
    border-color: #bd2130;
}


.btn-group {
    display: inline-flex;
    gap: 0;
}

.btn-group .btn {
    border-radius: 0;
    margin-left: -1px;
}

.btn-group .btn:first-child {
    border-radius: 8px 0 0 8px;
}

.btn-group .btn:last-child {
    border-radius: 0 8px 8px 0;
}

.btn-group-sm .btn {
    padding: 0.375rem 0.625rem;
    font-size: 0.875rem;
}

.btn:focus {
    outline: 2px solid var(--primary-green);
    outline-offset: 2px;
    box-shadow: none;
}


.badge {
    font-weight: 500;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    text-transform: capitalize;
    font-size: 0.85rem;
    display: inline-block;
}

.badge.bg-warning {
    background-color: var(--warning-color) !important;
    color: #000;
}

.badge.bg-info {
    background-color: var(--info-color) !important;
    color: white;
}

.badge.bg-primary {
    background-color: var(--primary-green) !important;
    color: white;
}

.badge.bg-success {
    background-color: var(--success-color) !important;
    color: white;
}

.badge.bg-danger {
    background-color: var(--danger-color) !important;
    color: white;
}

.badge.bg-secondary {
    background-color: #6c757d !important;
    color: white;
}


.d-flex.flex-wrap.gap-2 {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem !important;
}

.d-flex.flex-wrap.gap-2 .btn {
    flex-shrink: 0;
}


.table-responsive {
    border-radius: 8px;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.table {
    width: 100%;
    margin-bottom: 0;
    border-collapse: collapse;
}

.table thead th {
    background-color: var(--bg-light);
    color: var(--dark-green);
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.875rem;
    letter-spacing: 0.5px;
    border-bottom: 2px solid var(--primary-green);
    padding: 1rem 0.75rem;
    text-align: left;
    white-space: nowrap;
}

.table tbody tr {
    transition: all 0.2s ease;
    border-bottom: 1px solid var(--border-color);
}

.table tbody tr:last-child {
    border-bottom: none;
}

.table tbody tr:hover {
    background-color: rgba(57, 193, 193, 0.05);
    transform: scale(1.005);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.table td {
    vertical-align: middle;
    padding: 1rem 0.75rem;
    color: var(--text-dark);
}

.table td strong {
    color: var(--dark-green);
    font-weight: 600;
}

.table td small {
    color: var(--text-gray);
    display: block;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.table-hover tbody tr:hover {
    background-color: rgba(57, 193, 193, 0.05);
}


.text-center.py-5 {
    padding: 4rem 2rem !important;
}

.text-center.py-5 .bi {
    color: var(--border-color);
    font-size: 6rem;
}

.text-center.py-5 h3 {
    color: var(--dark-green);
    font-weight: 600;
    margin-top: 1.5rem;
    margin-bottom: 1rem;
}

.text-center.py-5 p {
    color: var(--text-gray);
    margin-bottom: 2rem;
}


.modal-content {
    border-radius: 12px;
    border: none;
    box-shadow: var(--shadow-medium);
}

.modal-header {
    background-color: var(--bg-light);
    border-bottom: 2px solid var(--primary-green);
    border-radius: 12px 12px 0 0;
    padding: 1.5rem;
}

.modal-title {
    color: var(--dark-green);
    font-weight: 600;
    font-size: 1.25rem;
}

.modal-body {
    padding: 2rem;
}

.modal-body p {
    color: var(--text-dark);
    margin-bottom: 1rem;
    line-height: 1.6;
}

.modal-body .text-danger {
    color: var(--danger-color) !important;
}

.modal-footer {
    border-top: 1px solid var(--border-color);
    padding: 1rem 2rem;
}

.btn-close {
    opacity: 0.5;
}

.btn-close:hover {
    opacity: 1;
}

.btn-close:focus {
    box-shadow: none;
}


.alert {
    border-radius: 8px;
    border: none;
    box-shadow: var(--shadow-medium);
    font-weight: 500;
    padding: 1rem 1.5rem;
    position: relative;
}

.alert-dismissible {
    padding-right: 3rem;
}

.alert-dismissible .btn-close {
    position: absolute;
    top: 0.75rem;
    right: 1rem;
}

.alert-success {
    background-color: rgba(40, 167, 69, 0.1);
    color: var(--success-color);
    border-left: 4px solid var(--success-color);
}

.alert-danger {
    background-color: rgba(220, 53, 69, 0.1);
    color: var(--danger-color);
    border-left: 4px solid var(--danger-color);
}

.alert-warning {
    background-color: rgba(255, 193, 7, 0.1);
    color: #856404;
    border-left: 4px solid var(--warning-color);
}

.alert-info {
    background-color: rgba(23, 162, 184, 0.1);
    color: var(--info-color);
    border-left: 4px solid var(--info-color);
}

.alert.position-fixed {
    z-index: 1060;
    max-width: 500px;
    margin-top: 1rem;
}


.shadow-sm {
    box-shadow: var(--shadow-light) !important;
}

.shadow {
    box-shadow: var(--shadow-medium) !important;
}

.border-0 {
    border: 0 !important;
}


.mb-1 { margin-bottom: 0.25rem; }
.mb-2 { margin-bottom: 0.5rem; }
.mb-3 { margin-bottom: 1rem; }
.mb-4 { margin-bottom: 1.5rem; }
.mb-5 { margin-bottom: 3rem; }

.mt-3 { margin-top: 1rem; }
.mt-4 { margin-top: 1.5rem; }

.gap-2 {
    gap: 0.75rem !important;
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

.card {
    animation: fadeIn 0.5s ease;
}

.table tbody tr {
    animation: fadeIn 0.5s ease;
}

.alert {
    animation: slideDown 0.3s ease;
}


.table-responsive::-webkit-scrollbar {
    height: 8px;
}

.table-responsive::-webkit-scrollbar-track {
    background: var(--bg-light);
    border-radius: 4px;
}

.table-responsive::-webkit-scrollbar-thumb {
    background: var(--primary-green);
    border-radius: 4px;
}

.table-responsive::-webkit-scrollbar-thumb:hover {
    background: var(--dark-green);
}


@media (max-width: 992px) {
    .card-body {
        padding: 1.25rem;
    }
    
    main.container {
        padding-left: 1rem;
        padding-right: 1rem;
    }
}


@media (max-width: 768px) {
    .card-body {
        padding: 1rem;
    }
    
    .card-title {
        font-size: 1.5rem;
    }
    
    .h3 {
        font-size: 1.5rem;
    }
    
   
    .col-6 {
        margin-bottom: 1rem;
    }
    
    
    .d-flex.justify-content-between.align-items-center {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 1rem;
    }
    
    .d-flex.justify-content-between.align-items-center .btn {
        width: 100%;
        justify-content: center;
    }
    

    .d-flex.flex-wrap.gap-2 {
        flex-wrap: nowrap !important;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        padding-bottom: 0.5rem;
    }
    
    
    .table thead {
        display: none;
    }
    
    .table tbody tr {
        display: block;
        margin-bottom: 1rem;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 1rem;
        background-color: var(--card-bg);
        box-shadow: var(--shadow-light);
    }
    
    .table tbody tr:hover {
        transform: none;
    }
    
    .table td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0;
        border: none;
        text-align: right;
    }
    
    .table td::before {
        content: attr(data-label);
        font-weight: 600;
        color: var(--dark-green);
        text-transform: uppercase;
        font-size: 0.75rem;
        text-align: left;
        flex: 1;
    }
    
    .table td:first-child {
        padding-top: 0;
    }
    
    .table td:last-child {
        padding-bottom: 0;
    }
    
    
    .btn-group {
        width: 100%;
        justify-content: flex-end;
    }
    
    
    .modal-body {
        padding: 1.5rem;
    }
    
    .modal-footer {
        padding: 1rem 1.5rem;
    }
}


@media (max-width: 576px) {
    main.container {
        padding-top: 1rem;
        padding-bottom: 2rem;
    }
    
    .card-body {
        padding: 0.875rem;
    }
    
    .h3 {
        font-size: 1.25rem;
    }
    
    .card-title {
        font-size: 1.25rem;
    }
    
    .text-center.py-5 {
        padding: 2rem 1rem !important;
    }
    
    .text-center.py-5 .bi {
        font-size: 4rem;
    }
    
    .btn {
        font-size: 0.875rem;
        padding: 0.5rem 0.875rem;
    }
    
    .badge {
        padding: 0.375rem 0.75rem;
        font-size: 0.75rem;
    }
    
    .alert.position-fixed {
        left: 1rem;
        right: 1rem;
        transform: none !important;
    }
}


@media print {
    body {
        background-color: white;
    }
    
    .btn,
    .modal,
    .alert {
        display: none !important;
    }
    
    .card {
        box-shadow: none;
        border: 1px solid #ddd;
    }
    
    .table {
        page-break-inside: auto;
    }
    
    .table tr {
        page-break-inside: avoid;
        page-break-after: auto;
    }
}


.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}


a:focus-visible,
button:focus-visible,
.btn:focus-visible {
    outline: 2px solid var(--primary-green);
    outline-offset: 2px;
}


a {
    color: var(--primary-green);
    text-decoration: none;
    transition: color var(--transition-speed) ease;
}

a:hover {
    color: var(--dark-green);
    text-decoration: underline;
}


* {
    transition-duration: var(--transition-speed);
    transition-timing-function: ease;
}

button,
input,
select,
textarea {
    font-family: inherit;
}
        </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container mt-4">
       
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 mb-1">
                            <i class="bi bi-list-check"></i> Mes commandes
                        </h1>
                        <p class="text-muted mb-0">Historique de toutes vos commandes</p>
                    </div>
                    <a href="home.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Nouvelle commande
                    </a>
                </div>
            </div>
        </div>
        
        
        <div class="row mb-4">
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="text-primary mb-2">
                            <i class="bi bi-cart-check fs-1"></i>
                        </div>
                        <h3 class="card-title"><?= $stats['total'] ?? 0 ?></h3>
                        <p class="card-text text-muted small">Total</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="text-success mb-2">
                            <i class="bi bi-currency-euro fs-1"></i>
                        </div>
                        <h3 class="card-title"><?= number_format($stats['spent'] ?? 0, 0, ',', ' ') ?>€</h3>
                        <p class="card-text text-muted small">Dépensé</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="text-warning mb-2">
                            <i class="bi bi-clock-history fs-1"></i>
                        </div>
                        <h3 class="card-title"><?= $stats['pending'] ?? 0 ?></h3>
                        <p class="card-text text-muted small">En attente</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="text-info mb-2">
                            <i class="bi bi-truck fs-1"></i>
                        </div>
                        <h3 class="card-title"><?= $stats['delivering'] ?? 0 ?></h3>
                        <p class="card-text text-muted small">En livraison</p>
                    </div>
                </div>
            </div>
        </div>
        
       
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    <a href="orders.php" class="btn btn-outline-primary <?= !$status ? 'active' : '' ?>">
                        Toutes
                    </a>
                    <a href="orders.php?status=pending" 
                       class="btn btn-outline-warning <?= $status == 'pending' ? 'active' : '' ?>">
                        <i class="bi bi-clock"></i> En attente
                    </a>
                    <a href="orders.php?status=preparing" 
                       class="btn btn-outline-info <?= $status == 'preparing' ? 'active' : '' ?>">
                        <i class="bi bi-egg-fried"></i> Préparation
                    </a>
                    <a href="orders.php?status=delivering" 
                       class="btn btn-outline-primary <?= $status == 'delivering' ? 'active' : '' ?>">
                        <i class="bi bi-truck"></i> Livraison
                    </a>
                    <a href="orders.php?status=completed" 
                       class="btn btn-outline-success <?= $status == 'completed' ? 'active' : '' ?>">
                        <i class="bi bi-check-circle"></i> Terminées
                    </a>
                </div>
            </div>
        </div>
        
        
        <div class="card shadow-sm">
            <div class="card-body">
                <?php if ($orders): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Commande</th>
                                    <th>Date</th>
                                    <th>Montant</th>
                                    <th>Statut</th>
                                    <th>Articles</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>
                                            <strong>#<?= htmlspecialchars($order['order_number'] ?? '') ?></strong>
                                        </td>
                                        <td>
                                            <?= date('d/m/Y', strtotime($order['created_at'] ?? date('Y-m-d'))) ?>
                                            <small class="text-muted d-block"><?= date('H:i', strtotime($order['created_at'] ?? date('H:i:s'))) ?></small>
                                        </td>
                                        <td>
                                            <strong><?= number_format($order['total'] ?? 0, 2, ',', ' ') ?> €</strong>
                                        </td>
                                        <td>
                                            <?php
                                            $statusColors = [
                                                'pending' => 'warning',
                                                'preparing' => 'info',
                                                'delivering' => 'primary',
                                                'completed' => 'success',
                                                'cancelled' => 'danger'
                                            ];
                                            ?>
                                            <span class="badge bg-<?= $statusColors[$order['status']] ?? 'secondary' ?>">
                                                <?= $order['status'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div><?= $order['items_count'] ?? 0 ?> article(s)</div>
                                            <small class="text-muted">
                                                <?= htmlspecialchars(substr($order['dish_names'] ?? '', 0, 50)) ?>
                                                <?php if (strlen($order['dish_names'] ?? '') > 50): ?>...<?php endif; ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="order_details.php?id=<?= $order['id'] ?>" 
                                                   class="btn btn-outline-primary" title="Détails">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="order_tracking.php?id=<?= $order['id'] ?>" 
                                                   class="btn btn-outline-info" title="Suivre">
                                                    <i class="bi bi-truck"></i>
                                                </a>
                                                <?php if ($order['status'] == 'pending'): ?>
                                                    <button type="button" 
                                                            class="btn btn-outline-danger cancel-order-btn"
                                                            data-order-id="<?= $order['id'] ?>"
                                                            data-order-number="<?= htmlspecialchars($order['order_number'] ?? '') ?>"
                                                            title="Annuler">
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-cart-x display-1 text-muted"></i>
                        <h3 class="mt-3">Aucune commande</h3>
                        <p class="text-muted mb-4">Vous n'avez pas encore passé de commande</p>
                        <a href="home.php" class="btn btn-primary">Commander maintenant</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

   
    <div class="modal fade" id="cancelModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="orders.php" id="cancelForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Annuler la commande</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p id="modalMessage">Êtes-vous sûr de vouloir annuler cette commande ?</p>
                        <p class="text-danger"><small>Cette action est irréversible.</small></p>
                        <input type="hidden" name="order_id" id="modalOrderId">
                        <input type="hidden" name="cancel_order" value="1">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Non, garder</button>
                        <button type="submit" class="btn btn-danger">Oui, annuler</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/user.js"></script>
    
    <script>
   
    document.querySelectorAll('.cancel-order-btn').forEach(button => {
        button.addEventListener('click', function() {
            const orderId = this.getAttribute('data-order-id');
            const orderNumber = this.getAttribute('data-order-number');
            
            
            document.getElementById('modalMessage').textContent = 
                'Êtes-vous sûr de vouloir annuler la commande #' + orderNumber + ' ?';
            document.getElementById('modalOrderId').value = orderId;
            
            
            const modal = new bootstrap.Modal(document.getElementById('cancelModal'));
            modal.show();
        });
    });
    
    
    <?php if (isset($_SESSION['flash_message'])): ?>
        const flashType = '<?= $_SESSION['flash_message']['type'] ?>';
        const flashText = '<?= $_SESSION['flash_message']['text'] ?>';
        
        if (flashType && flashText) {
           
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${flashType} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
            alertDiv.style.zIndex = '1060';
            alertDiv.innerHTML = `
                ${flashText}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alertDiv);
            
            
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alertDiv);
                bsAlert.close();
            }, 5000);
            
            
            <?php unset($_SESSION['flash_message']); ?>
        }
    <?php endif; ?>
    </script>
</body>
</html>