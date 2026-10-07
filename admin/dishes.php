<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';


if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . 'login.php');
    exit();
}


if (!isAdmin()) {
    addFlash('danger', 'Accès non autorisé.');
    header('Location: ' . BASE_URL);
    exit();
}


$category_filter = $_GET['category'] ?? '';
$restaurant_filter = $_GET['restaurant'] ?? '';
$availability_filter = $_GET['availability'] ?? '';
$search_query = $_GET['search'] ?? '';


$sql = "SELECT d.* FROM dishes d WHERE 1=1";
$params = [];


if (!empty($search_query)) {
    $sql .= " AND (d.name LIKE ? OR d.description LIKE ?)";
    $search_term = "%" . trim($search_query) . "%";
    $params[] = $search_term;
    $params[] = $search_term;
}


if (!empty($category_filter) && is_numeric($category_filter)) {
    $sql .= " AND d.category_id = ?";
    $params[] = (int)$category_filter;
}


if (!empty($restaurant_filter) && is_numeric($restaurant_filter)) {
    $sql .= " AND d.restaurant_id = ?";
    $params[] = (int)$restaurant_filter;
}


if ($availability_filter === 'available') {
    $sql .= " AND d.is_available = 1";
} elseif ($availability_filter === 'unavailable') {
    $sql .= " AND d.is_available = 0";
}

$sql .= " ORDER BY d.created_at DESC";


try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $dishes = $stmt->fetchAll();
    
} catch (Exception $e) {
    addFlash('danger', 'Erreur lors de la récupération des plats: ' . $e->getMessage());
    $dishes = [];
}


if (!empty($dishes)) {
    
    $category_ids = [];
    $restaurant_ids = [];
    
    foreach ($dishes as $dish) {
        if ($dish['category_id']) {
            $category_ids[] = $dish['category_id'];
        }
        if ($dish['restaurant_id']) {
            $restaurant_ids[] = $dish['restaurant_id'];
        }
    }
    
    
    $categories_map = [];
    if (!empty($category_ids)) {
        $cat_ids_str = implode(',', array_unique($category_ids));
        $cat_sql = "SELECT id, name FROM categories WHERE id IN ($cat_ids_str)";
        $cat_stmt = $pdo->query($cat_sql);
        $categories_data = $cat_stmt->fetchAll();
        
        foreach ($categories_data as $cat) {
            $categories_map[$cat['id']] = $cat['name'];
        }
    }
    
    
    $restaurants_map = [];
    if (!empty($restaurant_ids)) {
        $rest_ids_str = implode(',', array_unique($restaurant_ids));
        $rest_sql = "SELECT id, name FROM restaurants WHERE id IN ($rest_ids_str)";
        $rest_stmt = $pdo->query($rest_sql);
        $restaurants_data = $rest_stmt->fetchAll();
        
        foreach ($restaurants_data as $rest) {
            $restaurants_map[$rest['id']] = $rest['name'];
        }
    }
    
    
    foreach ($dishes as &$dish) {
        $dish['category_name'] = $categories_map[$dish['category_id']] ?? 'N/A';
        $dish['restaurant_name'] = $restaurants_map[$dish['restaurant_id']] ?? 'N/A';
    }
    unset($dish); 
}


$categories = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name")->fetchAll();
$restaurants = $pdo->query("SELECT * FROM restaurants WHERE is_active = 1 ORDER BY name")->fetchAll();


$total_dishes = $pdo->query("SELECT COUNT(*) as total FROM dishes")->fetchColumn();


$pageTitle = "Gestion des plats";
$pageIcon = "egg-fried";
$pageDescription = "Gérez tous les plats de votre catalogue";
$breadcrumbs = [
    ['title' => 'Plats', 'link' => '#', 'active' => true]
];
$actionButton = [
    'text' => 'Nouveau plat',
    'icon' => 'plus-circle',
    'link' => 'add_dish.php'
];


$template = 'templates/dishes_list.phtml';
include 'templates/layout-admin.phtml';
?>