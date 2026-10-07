<?php
session_start();
require_once '../admin/includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];


$tableExists = $pdo->query("SHOW TABLES LIKE 'favorites'")->fetch();

if (!$tableExists) {
    $favorites = [];
} else {
    
    $stmt = $pdo->prepare("
        SELECT d.*, c.name as category_name, r.name as restaurant_name,
               (SELECT COUNT(*) FROM favorites f2 WHERE f2.dish_id = d.id) as total_favorites
        FROM favorites f
        JOIN dishes d ON f.dish_id = d.id
        LEFT JOIN categories c ON d.category_id = c.id
        LEFT JOIN restaurants r ON d.restaurant_id = r.id
        WHERE f.user_id = ? AND d.is_available = 1
        ORDER BY f.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $favorites = $stmt->fetchAll();
}


if ($tableExists) {
    $stmt = $pdo->prepare("
        SELECT r.*, 
               COUNT(DISTINCT f.id) as total_favorites,
               COUNT(DISTINCT d.id) as dish_count
        FROM restaurants r
        LEFT JOIN dishes d ON r.id = d.restaurant_id
        LEFT JOIN favorites f ON d.id = f.dish_id AND f.user_id = ?
        WHERE r.is_active = 1
        GROUP BY r.id
        HAVING COUNT(DISTINCT f.id) > 0
        ORDER BY total_favorites DESC
    ");
    $stmt->execute([$user_id]);
    $favorite_restaurants = $stmt->fetchAll();
} else {
    $favorite_restaurants = [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes favoris - Delivero</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">

    <style>
    .favorite-card {
        position: relative;
        transition: transform 0.3s;
    }
    .favorite-card:hover {
        transform: translateY(-5px);
    }
    .favorite-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 2;
    }
    .empty-favorites {
        max-width: 400px;
        margin: 0 auto;
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
                            <i class="bi bi-heart text-danger"></i> Mes favoris
                        </h1>
                        <p class="text-muted mb-0">
                            Vos plats et restaurants préférés
                        </p>
                    </div>
                    <div class="btn-group">
                        <a href="#plats" class="btn btn-outline-primary active" data-target="plats">
                            <i class="bi bi-egg-fried"></i> Plats
                        </a>
                        <a href="#restaurants" class="btn btn-outline-primary" data-target="restaurants">
                            <i class="bi bi-shop"></i> Restaurants
                        </a>
                    </div>
                </div>
            </div>
        </div>

       
        <div id="plats-section" class="section-content">
            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="h5 mb-3">
                        <i class="bi bi-egg-fried"></i> Plats favoris
                        <span class="badge bg-primary ms-2"><?= count($favorites) ?></span>
                    </h2>
                </div>
            </div>

            <?php if (empty($favorites)): ?>
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-heart display-1 text-muted"></i>
                        <h3 class="mt-3">Aucun plat favori</h3>
                        <p class="text-muted mb-4">
                            Ajoutez des plats à vos favoris pour les retrouver facilement
                        </p>
                        <a href="home.php" class="btn btn-primary">
                            <i class="bi bi-shop"></i> Découvrir les restaurants
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($favorites as $dish): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 favorite-card shadow-sm">
                                <div class="favorite-badge">
                                    <button class="btn btn-danger btn-sm remove-favorite" 
                                            data-dish-id="<?= $dish['id'] ?>"
                                            title="Retirer des favoris">
                                        <i class="bi bi-heart-fill"></i>
                                    </button>
                                </div>
                                
                                <div class="position-relative">
                                    <?php if ($dish['image']): ?>
                                        <img src="<?= htmlspecialchars($dish['image']) ?>" 
                                             class="card-img-top" 
                                             alt="<?= htmlspecialchars($dish['name']) ?>"
                                             style="height: 200px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                             style="height: 200px;">
                                            <i class="bi bi-egg-fried display-4 text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($dish['name']) ?></h5>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted">
                                            <i class="bi bi-shop"></i> <?= htmlspecialchars($dish['restaurant_name']) ?>
                                        </small>
                                        <br>
                                        <small class="text-muted">
                                            <i class="bi bi-tag"></i> <?= htmlspecialchars($dish['category_name']) ?>
                                        </small>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="h5 text-primary mb-0">
                                            <?= number_format($dish['price'], 2, ',', ' ') ?> €
                                        </span>
                                        <span class="text-warning small">
                                            <i class="bi bi-star-fill"></i> <?= $dish['rating'] ?>
                                            <span class="text-muted ms-1">
                                                (<?= $dish['total_favorites'] ?> <i class="bi bi-heart"></i>)
                                            </span>
                                        </span>
                                    </div>
                                    
                                    <div class="d-grid gap-2">
                                        <a href="menu.php?restaurant_id=<?= $dish['restaurant_id'] ?>" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-shop"></i> Voir le restaurant
                                        </a>
                                        <button class="btn btn-primary btn-sm add-to-cart-from-fav"
                                                data-dish-id="<?= $dish['id'] ?>"
                                                data-dish-name="<?= htmlspecialchars($dish['name']) ?>"
                                                data-dish-price="<?= $dish['price'] ?>">
                                            <i class="bi bi-cart-plus"></i> Ajouter au panier
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    
        <div id="restaurants-section" class="section-content d-none">
            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="h5 mb-3">
                        <i class="bi bi-shop"></i> Restaurants favoris
                        <span class="badge bg-primary ms-2"><?= count($favorite_restaurants) ?></span>
                    </h2>
                </div>
            </div>

            <?php if (empty($favorite_restaurants)): ?>
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-shop display-1 text-muted"></i>
                        <h3 class="mt-3">Aucun restaurant favori</h3>
                        <p class="text-muted mb-4">
                            Ajoutez des plats de restaurants pour les voir apparaître ici
                        </p>
                        <a href="home.php" class="btn btn-primary">
                            <i class="bi bi-shop"></i> Explorer les restaurants
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($favorite_restaurants as $restaurant): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 favorite-card shadow-sm">
                                <div class="position-relative">
                                    <?php if ($restaurant['image']): ?>
                                        <img src="<?= htmlspecialchars($restaurant['image']) ?>" 
                                             class="card-img-top" 
                                             alt="<?= htmlspecialchars($restaurant['name']) ?>"
                                             style="height: 200px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                             style="height: 200px;">
                                            <i class="bi bi-shop display-4 text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="position-absolute top-0 end-0 m-2">
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-star-fill"></i> <?= $restaurant['rating'] ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($restaurant['name']) ?></h5>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted">
                                            <i class="bi bi-geo-alt"></i> 
                                            <?= htmlspecialchars(substr($restaurant['address'], 0, 50)) ?>
                                            <?php if (strlen($restaurant['address']) > 50): ?>...<?php endif; ?>
                                        </small>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="badge bg-light text-dark border">
                                            <i class="bi bi-clock"></i> <?= $restaurant['delivery_time'] ?>
                                        </span>
                                        <span class="badge bg-info">
                                            <?= $restaurant['dish_count'] ?> plats
                                        </span>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <small class="text-success">
                                            <i class="bi bi-heart-fill"></i> 
                                            <?= $restaurant['total_favorites'] ?> plat(s) favori(s)
                                        </small>
                                    </div>
                                    
                                    <div class="d-grid">
                                        <a href="menu.php?restaurant_id=<?= $restaurant['id'] ?>" 
                                           class="btn btn-primary">
                                            <i class="bi bi-eye"></i> Voir le menu
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/user.js"></script>
    
    <script>
   
    document.querySelectorAll('[data-target]').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            
            document.querySelectorAll('[data-target]').forEach(btn => {
                btn.classList.remove('active');
            });
            this.classList.add('active');
            
            
            const target = this.dataset.target;
            document.querySelectorAll('.section-content').forEach(section => {
                section.classList.add('d-none');
            });
            document.getElementById(`${target}-section`).classList.remove('d-none');
        });
    });
    
    
    document.querySelectorAll('.remove-favorite').forEach(button => {
        button.addEventListener('click', function() {
            const dishId = this.dataset.dishId;
            
            if (confirm('Retirer ce plat des favoris ?')) {
                fetch('ajax/toggle_favorite.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        dish_id: dishId,
                        action: 'remove'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        
                        this.closest('.col-md-6').remove();
                        
                        
                        const countBadge = document.querySelector('[data-target="plats"] + .badge');
                        if (countBadge) {
                            const currentCount = parseInt(countBadge.textContent);
                            countBadge.textContent = currentCount - 1;
                        }
                        
                      
                        showNotification('success', data.message);
                    }
                });
            }
        });
    });
    
   
    document.querySelectorAll('.add-to-cart-from-fav').forEach(button => {
        button.addEventListener('click', function() {
            const dishId = this.dataset.dishId;
            const dishName = this.dataset.dishName;
            const dishPrice = this.dataset.dishPrice;
            
            fetch('ajax/add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    dish_id: dishId,
                    dish_name: dishName,
                    price: dishPrice,
                    quantity: 1
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('success', `${dishName} ajouté au panier !`);
                }
            });
        });
    });
    </script>
</body>
</html>