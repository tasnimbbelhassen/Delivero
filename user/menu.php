<?php
session_start();
require_once '../admin/includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$restaurant_id = $_GET['restaurant_id'] ?? 0;


$stmt = $pdo->prepare("SELECT * FROM restaurants WHERE id = ?");
$stmt->execute([$restaurant_id]);
$restaurant = $stmt->fetch();

if (!$restaurant) {
    header('Location: home.php');
    exit();
}


$stmt = $pdo->prepare("
    SELECT c.*, 
           COUNT(d.id) as dish_count
    FROM categories c
    LEFT JOIN dishes d ON c.id = d.category_id 
                      AND d.restaurant_id = ? 
                      AND d.is_available = 1
    WHERE c.is_active = 1
    GROUP BY c.id
    ORDER BY c.name
");
$stmt->execute([$restaurant_id]);
$categories = $stmt->fetchAll();


$stmt = $pdo->prepare("
    SELECT d.*, c.name as category_name
    FROM dishes d
    LEFT JOIN categories c ON d.category_id = c.id
    WHERE d.restaurant_id = ? 
    AND d.is_available = 1
    ORDER BY d.category_id, d.name
");
$stmt->execute([$restaurant_id]);
$all_dishes = $stmt->fetchAll();


$dishes_by_category = [];
foreach ($all_dishes as $dish) {
    $category_name = $dish['category_name'] ?: 'Autres';
    if (!isset($dishes_by_category[$category_name])) {
        $dishes_by_category[$category_name] = [];
    }
    $dishes_by_category[$category_name][] = $dish;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - <?= htmlspecialchars($restaurant['name']) ?> - Delivero</title>
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
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}


.card {
    border: 1px solid var(--border-color);
    border-radius: 12px;
    transition: all 0.3s ease;
    background-color: var(--card-bg);
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-medium);
}


.card-body {
    padding: 1.5rem;
}

.card .rounded {
    object-fit: cover;
    border: 3px solid var(--primary-green);
}


.badge {
    font-weight: 500;
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
}

.badge.bg-warning {
    background-color: var(--warning-color) !important;
}

.badge.bg-light {
    background-color: #f8f9fa !important;
    color: var(--text-dark) !important;
}

.badge.bg-success {
    background-color: var(--success-color) !important;
}

.badge.bg-danger {
    background-color: var(--danger-color) !important;
}

.badge.bg-info {
    background-color: var(--info-color) !important;
}

.badge.bg-primary {
    background-color: var(--primary-green) !important;
}


.btn {
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
    border: 2px solid transparent;
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
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(57, 193, 193, 0.3);
}

.btn-outline-primary {
    color: var(--primary-green);
    border-color: var(--primary-green);
}

.btn-outline-primary:hover,
.btn-outline-primary.active {
    background-color: var(--primary-green);
    border-color: var(--primary-green);
    color: white;
}

.btn-outline-primary:focus {
    box-shadow: 0 0 0 0.2rem rgba(57, 193, 193, 0.25);
}


#category-nav {
    padding: 0.5rem 0;
}

#category-nav .btn {
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

#category-nav .badge {
    font-size: 0.75rem;
}


.dish-card {
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.dish-card:hover {
    border-color: var(--primary-green);
    box-shadow: 0 8px 24px rgba(57, 193, 193, 0.15);
}

.dish-card .card-title {
    color: var(--text-dark);
    font-weight: 600;
    font-size: 1.1rem;
}

.dish-card .card-text {
    color: var(--text-gray);
    line-height: 1.5;
}

.dish-card img {
    object-fit: cover;
    border-radius: 8px;
    border: 2px solid var(--border-color);
    transition: all 0.3s ease;
}

.dish-card:hover img {
    border-color: var(--primary-green);
    transform: scale(1.05);
}


.h5.text-primary {
    color: var(--primary-green) !important;
    font-weight: 700;
}


.text-warning {
    color: var(--warning-color) !important;
}


.category-section h2 {
    color: var(--dark-green);
    font-weight: 700;
    border-bottom: 3px solid var(--primary-green) !important;
    padding-bottom: 0.75rem !important;
}

.category-section h2 small {
    color: var(--text-gray);
    font-weight: 400;
}


#floating-cart .btn {
    width: 60px;
    height: 60px;
    border-radius: 50% !important;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    box-shadow: 0 4px 16px rgba(57, 193, 193, 0.4);
}

#floating-cart .btn:hover {
    transform: scale(1.1);
}

#floating-cart .badge {
    position: absolute;
    top: -5px;
    right: -5px;
    min-width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
}


.dropdown-menu {
    border-radius: 8px;
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-medium);
}

.dropdown-header {
    color: var(--dark-green);
    font-weight: 600;
}

.dropdown-item {
    color: var(--text-dark);
    transition: all 0.2s ease;
}

.dropdown-item:hover {
    background-color: rgba(57, 193, 193, 0.1);
    color: var(--dark-green);
}


.shadow-sm {
    box-shadow: var(--shadow-light) !important;
}

.text-muted {
    color: var(--text-gray) !important;
}

.border-bottom {
    border-bottom-color: var(--border-color) !important;
}


@media (max-width: 768px) {
    .card-body {
        padding: 1rem;
    }
    
    #category-nav {
        overflow-x: auto;
        flex-wrap: nowrap !important;
        -webkit-overflow-scrolling: touch;
    }
    
    #category-nav .btn {
        flex-shrink: 0;
    }
    
    .dish-card .col-8,
    .dish-card .col-4 {
        flex: 0 0 100%;
        max-width: 100%;
    }
    
    .dish-card .col-4 {
        margin-top: 1rem;
        text-align: center !important;
    }
    
    .dish-card img {
        max-height: 150px !important;
        width: 100%;
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

.category-section {
    animation: fadeIn 0.5s ease;
}


#category-nav::-webkit-scrollbar {
    height: 6px;
}

#category-nav::-webkit-scrollbar-track {
    background: var(--bg-light);
    border-radius: 3px;
}

#category-nav::-webkit-scrollbar-thumb {
    background: var(--primary-green);
    border-radius: 3px;
}

#category-nav::-webkit-scrollbar-thumb:hover {
    background: var(--dark-green);
}
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container mt-4">
        
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <?php if ($restaurant['image']): ?>
                                <img src="<?= htmlspecialchars($restaurant['image']) ?>" 
                                     alt="<?= htmlspecialchars($restaurant['name']) ?>"
                                     class="rounded me-4" width="100" height="100">
                            <?php endif; ?>
                            <div class="flex-grow-1">
                                <h1 class="h3 mb-2"><?= htmlspecialchars($restaurant['name']) ?></h1>
                                <div class="d-flex flex-wrap gap-3 mb-2">
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-star-fill"></i> <?= $restaurant['rating'] ?>
                                    </span>
                                    <span class="badge bg-light text-dark border">
                                        <i class="bi bi-clock"></i> <?= $restaurant['delivery_time'] ?>
                                    </span>
                                    <span class="badge bg-light text-dark border">
                                        <i class="bi bi-geo-alt"></i> <?= htmlspecialchars(substr($restaurant['address'], 0, 30)) ?>
                                    </span>
                                </div>
                                <p class="text-muted mb-0">
                                    <i class="bi bi-telephone"></i> <?= htmlspecialchars($restaurant['phone']) ?>
                                </p>
                            </div>
                            <div class="text-end">
                                <a href="home.php" class="btn btn-outline-primary">
                                    <i class="bi bi-arrow-left"></i> Changer de restaurant
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

       
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div id="category-nav" class="d-flex flex-wrap gap-2">
                            <button class="btn btn-outline-primary active" data-category="all">
                                Tous les plats
                            </button>
                            <?php foreach ($categories as $category): ?>
                                <?php if ($category['dish_count'] > 0): ?>
                                    <button class="btn btn-outline-primary" 
                                            data-category="cat-<?= $category['id'] ?>">
                                        <?= htmlspecialchars($category['name']) ?>
                                        <span class="badge bg-primary rounded-pill ms-1">
                                            <?= $category['dish_count'] ?>
                                        </span>
                                    </button>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <?php foreach ($dishes_by_category as $category_name => $dishes): ?>
            <div class="category-section mb-5" id="cat-<?= $dishes[0]['category_id'] ?? 'other' ?>">
                <h2 class="mb-4 border-bottom pb-2">
                    <?= htmlspecialchars($category_name) ?>
                    <small class="text-muted fs-6">(<?= count($dishes) ?> plats)</small>
                </h2>
                
                <div class="row">
                    <?php foreach ($dishes as $dish): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card h-100 dish-card shadow-sm">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-8">
                                            <h5 class="card-title mb-2"><?= htmlspecialchars($dish['name']) ?></h5>
                                            
                                            <?php if ($dish['description']): ?>
                                                <p class="card-text text-muted small mb-2">
                                                    <?= htmlspecialchars(substr($dish['description'], 0, 100)) ?>
                                                    <?php if (strlen($dish['description']) > 100): ?>...<?php endif; ?>
                                                </p>
                                            <?php endif; ?>
                                            
                                            <div class="mb-3">
                                                <?php 
                                                
                                                $badges = [];
                                                
                                                if (isset($dish['is_vegetarian']) && $dish['is_vegetarian']) {
                                                    $badges[] = '<span class="badge bg-success me-1">Végétarien</span>';
                                                }
                                                
                                                if (isset($dish['is_spicy']) && $dish['is_spicy']) {
                                                    $badges[] = '<span class="badge bg-danger me-1">Épicé</span>';
                                                }
                                                
                                                if (!empty($dish['calories'])) {
                                                    $badges[] = '<span class="badge bg-info me-1">' . $dish['calories'] . ' cal</span>';
                                                }
                                                
                                                
                                                echo implode(' ', $badges);
                                                ?>
                                            </div>
                                            
                                            <div class="d-flex align-items-center">
                                                <span class="h5 text-primary mb-0">
                                                    <?= number_format($dish['price'], 3, ',', ' ') ?> DT
                                                </span>
                                                <span class="ms-3 text-warning small">
                                                    <i class="bi bi-star-fill"></i> <?= $dish['rating'] ?>
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="col-4 text-end">
                                            <?php if ($dish['image']): ?>
                                                <img src="<?= htmlspecialchars($dish['image']) ?>" 
                                                     alt="<?= htmlspecialchars($dish['name']) ?>"
                                                     class="img-fluid rounded mb-3" 
                                                     style="max-height: 100px;">
                                            <?php endif; ?>
                                            
                                            
                                            <div class="d-grid gap-2">
                                                
                                                <button class="btn btn-primary add-to-cart"
                                                        data-dish-id="<?= $dish['id'] ?>"
                                                        data-dish-name="<?= htmlspecialchars($dish['name']) ?>"
                                                        data-restaurant-id="<?= $dish['restaurant_id'] ?>"
                                                        data-restaurant-name="<?= htmlspecialchars($restaurant['name']) ?>">
                                                    <i class="bi bi-cart-plus"></i> Ajouter au panier
                                                </button>

                                                
                                                
                                                <div class="btn-group w-100">
                                                    <button type="button" 
                                                            class="btn btn-outline-primary dropdown-toggle" 
                                                            data-bs-toggle="dropdown">
                                                        <i class="bi bi-plus-slash-minus"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li><h6 class="dropdown-header">Quantité rapide</h6></li>
                                                        <?php for ($q = 1; $q <= 5; $q++): ?>
                                                            <li>
                                                                <a class="dropdown-item add-to-cart-quick" 
                                                                   href="#"
                                                                   data-dish-id="<?= $dish['id'] ?>"
                                                                   data-dish-name="<?= htmlspecialchars($dish['name']) ?>"
                                                                   data-price="<?= $dish['price'] ?>"
                                                                   data-restaurant-id="<?= $restaurant_id ?>"
                                                                   data-quantity="<?= $q ?>">
                                                                    <?= $q ?> portion<?= $q > 1 ? 's' : '' ?>
                                                                </a>
                                                            </li>
                                                        <?php endfor; ?>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
        
       
        <div id="floating-cart" class="position-fixed bottom-0 end-0 m-3 d-lg-none">
            <a href="cart.php" class="btn btn-primary btn-lg shadow-lg rounded-pill">
                <i class="bi bi-cart"></i>
                <span id="floating-cart-count" class="badge bg-danger rounded-pill">0</span>
            </a>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/menu.js"></script>
    <script src="assets/js/cart.js"></script>
    
    <script>
   
    document.addEventListener('DOMContentLoaded', function() {
        
        const categoryButtons = document.querySelectorAll('#category-nav button');
        const categorySections = document.querySelectorAll('.category-section');
        
        categoryButtons.forEach(button => {
            button.addEventListener('click', function() {
             
                categoryButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                const category = this.dataset.category;
                
                
                categorySections.forEach(section => {
                    if (category === 'all' || section.id === category) {
                        section.style.display = 'block';
                    } else {
                        section.style.display = 'none';
                    }
                });
                
               
                if (category !== 'all') {
                    document.getElementById(category).scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
        
      
        document.querySelectorAll('.add-to-cart-quick').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                const item = {
                    id: this.dataset.dishId,
                    name: this.dataset.dishName,
                    price: parseFloat(this.dataset.price),
                    restaurantId: this.dataset.restaurantId,
                    quantity: parseInt(this.dataset.quantity),
                    image: this.dataset.image || null
                };
                
            
                if (window.DeliveroCart) {
                    const result = window.DeliveroCart.addItem(item);
                    if (result.success) {
                       
                        updateCartCounters();
                        showNotification('success', result.message);
                    }
                }
            });
        });
        
        
        function updateCartCounters() {
            if (window.DeliveroCart) {
                const totalItems = window.DeliveroCart.getTotalItems();
                
               
                const floatingCounter = document.getElementById('floating-cart-count');
                if (floatingCounter) {
                    floatingCounter.textContent = totalItems;
                    floatingCounter.style.display = totalItems > 0 ? 'inline' : 'none';
                }
                
               
            }
        }
        
     
        updateCartCounters();
        

        if (window.DeliveroCart) {
            window.DeliveroCart.setCurrentRestaurant(<?= $restaurant_id ?>);
        }
        
        
        if (window.DeliveroCart) {
            window.DeliveroCart.addObserver(function(cart) {
                updateCartCounters();
            });
        }
    });
    </script>
</body>
</html>