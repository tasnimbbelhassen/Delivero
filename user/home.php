<?php
session_start();
require_once '../admin/includes/config.php';


if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}


$stmt = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name");
$categories = $stmt->fetchAll();


$search = $_GET['search'] ?? '';
$category_id = $_GET['category'] ?? '';

$sql = "SELECT r.*, 
               COUNT(DISTINCT d.id) as dish_count,
               AVG(d.rating) as avg_rating
        FROM restaurants r
        LEFT JOIN dishes d ON r.id = d.restaurant_id
        WHERE r.is_active = 1";

$params = [];

if ($search) {
    $sql .= " AND (r.name LIKE ? OR r.address LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category_id) {
    $sql .= " AND d.category_id = ?";
    $params[] = $category_id;
}

$sql .= " GROUP BY r.id ORDER BY r.rating DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$restaurants = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choisir un restaurant - Delivero</title>
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
    background-color: #fbfcf8 !important;
}



.filters-card {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
    margin-bottom: 3rem;
}

.filter-form {
    display: grid;
    grid-template-columns: 2fr 1.5fr auto;
    gap: 1rem;
    align-items: end;
}

.filter-group {
    display: flex;
    flex-direction: column;
}

.filter-label {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 0.5rem;
}

.search-input-wrapper {
    position: relative;
}

.search-input {
    width: 100%;
    padding: 0.9rem 1rem 0.9rem 3rem;
    border: 2px solid var(--border-color);
    border-radius: 12px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.search-input:focus {
    outline: none;
    border-color: var(--primary-green);
    box-shadow: 0 0 0 3px rgba(57, 193, 193, 0.1);
}

.search-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-gray);
    font-size: 1.2rem;
}

.filter-select {
    width: 100%;
    padding: 0.9rem 1rem;
    border: 2px solid var(--border-color);
    border-radius: 12px;
    font-size: 0.95rem;
    background: white;
    cursor: pointer;
    transition: all 0.3s ease;
}

.filter-select:focus {
    outline: none;
    border-color: var(--primary-green);
    box-shadow: 0 0 0 3px rgba(57, 193, 193, 0.1);
}

.filter-select:hover {
    border-color: var(--primary-green);
    background: rgba(57, 193, 193, 0.02);
    
}

.filter-select option {
    padding: 0.75rem 1rem;
    background: white;
    color: var(--text-dark);
    transition: all 0.2s ease;
}

.filter-select option:hover {
    color: white;
}

.filter-select option:checked {
    background-color: var(--primary-green);
    
}

.filter-btn {
    padding: 0.9rem 2rem;
    background: var(--primary-green);
    color: white;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.filter-btn:hover {
    background: var(--dark-green);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(57, 193, 193, 0.3);
}


.icons-section {
    padding: 60px 0;
    background: var(--bg-light);
    overflow: hidden;
    margin-top: -80px;
}

.icons-scroll-container {
    margin-top: 30px;
    margin-bottom: 15px;
    width: 100%;
    overflow: hidden;
}

.scroll-icons {
    white-space: nowrap;
    display: inline-block;
    animation: scroll-left 40s linear infinite;
}

.food-icon {
    display: inline-block;
    margin: 0 30px;
}

.food-icon img {
    width: 70px;
    height:70px;
    object-fit: contain;
    opacity: 1;
    transition: all 0.3s ease;
}

.food-icon img:hover {
    opacity: 1;
    transform: scale(1.2);
}

@keyframes scroll-left {
    0% { transform: translateX(0%); }
    100% { transform: translateX(-50%); }
}


.restaurants-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
    box-shadow: none !important;
    border-radius: none !important;
}

.restaurant-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
    transition: all 0.3s ease;
    cursor: pointer;
}

.restaurant-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
}

.restaurant-image-wrapper {
    position: relative;
    width: 100%;
    height: 220px;
    overflow: hidden;
}

.restaurant-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.restaurant-card:hover .restaurant-image {
    transform: scale(1.1);
}

.restaurant-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    display: flex;
    align-items: center;
    justify-content: center;
}

.restaurant-placeholder i {
    font-size: 4rem;
    color: var(--text-gray);
    opacity: 0.5;
}

.restaurant-rating {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: white;
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.3rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.restaurant-rating i {
    color: #ffc107;
}

.restaurant-content {
    padding: 1.5rem;
}

.restaurant-name {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 0.75rem;
}

.restaurant-address {
    color: var(--text-gray);
    font-size: 0.9rem;
    display: flex;
    align-items: start;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.restaurant-address i {
    color: var(--primary-green);
    margin-top: 0.2rem;
}

.restaurant-meta {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.25rem;
}

.meta-badge {
    padding: 0.4rem 0.8rem;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.meta-badge.time {
    background: rgba(57, 193, 193, 0.1);
    color: var(--primary-green);
}

.meta-badge.dishes {
    background: rgba(57, 193, 193, 0.1);
    color: var(--primary-green);
}

.restaurant-action {
    width: 100%;
    padding: 0.9rem;
    background: var(--primary-green);
    color: white;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.restaurant-action:hover {
    background: var(--dark-green);
    color: white;
    transform: translateY(-2px);
}


.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 16px;
}

.empty-icon {
    font-size: 5rem;
    color: var(--text-gray);
    opacity: 0.5;
    margin-bottom: 1.5rem;
}

.empty-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 0.75rem;
}

.empty-text {
    color: var(--text-gray);
    margin-bottom: 2rem;
}

.empty-action {
    padding: 0.9rem 2rem;
    background: var(--primary-green);
    color: white;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s ease;
}

.empty-action:hover {
    background: var(--dark-green);
    color: white;
    transform: translateY(-2px);
}


.page-container {
    max-width: 1320px;
    margin: 0 auto;
    padding: 0 15px;
    background-color: #fbfcf8;
}


@media (max-width: 992px) {
    .filter-form {
        grid-template-columns: 1fr;
    }
    
    .restaurants-grid {
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.5rem;
    }
}

@media (max-width: 768px) {
    .page-title {
        font-size: 2rem;
    }
    
    .page-subtitle {
        font-size: 1rem;
    }
    
    .filters-card {
        padding: 1.5rem;
    }
    
    .restaurant-image-wrapper {
        height: 180px;
    }
    
    .icons-section {
        padding: 40px 0;
    }
    
    .food-icon {
        margin: 0 20px;
    }
    
    .food-icon img {
        width: 50px;
        height: 50px;
    }
}

@media (max-width: 576px) {
    .page-header {
        padding: 2rem 0;
    }
    
    .restaurants-grid {
        grid-template-columns: 1fr;
    }
}
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main>
        
        <div class="icons-section">
                <div class="icons-scroll-container">
                    <span class="scroll-icons">
                        <?php 
                        $icons = ['bread', 'cake', 'chicken-bucket', 'french-fries', 'hot-dog', 
                                  'noodle-bowl', 'orange-juice', 'pie', 'pizza', 'pudding'];
                        $icon_names = ['Pain', 'Gâteau', 'Poulet', 'Frites', 'Hot Dog', 
                                       'Nouilles', 'Jus', 'Tarte', 'Pizza', 'Pudding'];
                        
                        for ($repeat = 0; $repeat < 5; $repeat++):
                            foreach ($icons as $index => $icon): 
                        ?>
                            <span class="food-icon">
                                <img src="assets/icons/<?= $icon ?>.png" alt="<?= $icon_names[$index] ?>">
                            </span>
                        <?php 
                            endforeach;
                        endfor; 
                        ?>
                    </span>
                </div>

        <div class="page-container">
            
            <div class="filters-card">
                <form method="GET" class="filter-form">
                    <div class="filter-group">
                        <label class="filter-label">Rechercher</label>
                        <div class="search-input-wrapper">
                            <i class="bi bi-search search-icon"></i>
                            <input type="text" 
                                   class="search-input" 
                                   name="search" 
                                   placeholder="Nom du restaurant, adresse..." 
                                   value="<?= htmlspecialchars($search) ?>">
                        </div>
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label">Catégorie</label>
                        <select class="filter-select" name="category">
                            <option value="">Toutes les catégories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>" 
                                        <?= $category_id == $category['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="filter-btn">
                        <i class="bi bi-funnel"></i>
                        Filtrer
                    </button>
                </form>
            </div>

    
            <?php if ($restaurants): ?>
                <div class="restaurants-grid">
                    <?php foreach ($restaurants as $restaurant): ?>
                        <article class="restaurant-card" 
                                 onclick="window.location='menu.php?restaurant_id=<?= $restaurant['id'] ?>'">
                            
                            <div class="restaurant-image-wrapper">
                                <?php if ($restaurant['image']): ?>
                                    <img src="<?= htmlspecialchars($restaurant['image']) ?>" 
                                         class="restaurant-image" 
                                         alt="<?= htmlspecialchars($restaurant['name']) ?>">
                                <?php else: ?>
                                    <div class="restaurant-placeholder">
                                        <i class="bi bi-shop"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="restaurant-rating">
                                    <i class="bi bi-star-fill"></i>
                                    <?= number_format($restaurant['rating'], 1) ?>
                                </div>
                            </div>
                            
                        
                            <div class="restaurant-content">
                                <h2 class="restaurant-name">
                                    <?= htmlspecialchars($restaurant['name']) ?>
                                </h2>
                                
                                <div class="restaurant-address">
                                    <i class="bi bi-geo-alt-fill"></i>
                                    <span>
                                        <?= htmlspecialchars(substr($restaurant['address'], 0, 60)) ?>
                                        <?php if (strlen($restaurant['address']) > 60): ?>...<?php endif; ?>
                                    </span>
                                </div>
                                
                                <div class="restaurant-meta">
                                    <span class="meta-badge time">
                                        <i class="bi bi-clock-fill"></i>
                                        <?= htmlspecialchars($restaurant['delivery_time']) ?>
                                    </span>
                                    <span class="meta-badge dishes">
                                        <i class="bi bi-egg-fried"></i>
                                        <?= $restaurant['dish_count'] ?> plats
                                    </span>
                                </div>
                                
                                <a href="menu.php?restaurant_id=<?= $restaurant['id'] ?>" 
                                   class="restaurant-action"
                                   onclick="event.stopPropagation()">
                                    <i class="bi bi-eye"></i>
                                    Voir le menu
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                
                <div class="empty-state">
                    <i class="bi bi-shop empty-icon"></i>
                    <h3 class="empty-title">Aucun restaurant trouvé</h3>
                    <p class="empty-text">Aucun restaurant ne correspond à votre recherche</p>
                    <a href="home.php" class="empty-action">Réinitialiser les filtres</a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/restaurants.js"></script>
</body>
</html>