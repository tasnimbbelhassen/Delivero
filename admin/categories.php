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


$status_filter = $_GET['status'] ?? '';
$search_query = $_GET['search'] ?? '';
$sort_by = $_GET['sort'] ?? 'name';


$sql = "SELECT c.*, 
               COUNT(d.id) as dish_count,
               SUM(d.order_count) as total_orders,
               AVG(d.rating) as avg_rating
        FROM categories c
        LEFT JOIN dishes d ON c.id = d.category_id
        WHERE 1=1";

$params = [];

if (!empty($search_query)) {
    $sql .= " AND (c.name LIKE ? OR c.description LIKE ?)";
    $search_term = "%" . trim($search_query) . "%";
    $params[] = $search_term;
    $params[] = $search_term;
}

if ($status_filter === 'active') {
    $sql .= " AND c.is_active = 1";
} elseif ($status_filter === 'inactive') {
    $sql .= " AND c.is_active = 0";
}

$sql .= " GROUP BY c.id";


switch ($sort_by) {
    case 'dishes':
        $sql .= " ORDER BY dish_count DESC";
        break;
    case 'orders':
        $sql .= " ORDER BY total_orders DESC";
        break;
    case 'rating':
        $sql .= " ORDER BY avg_rating DESC";
        break;
    case 'created':
        $sql .= " ORDER BY c.created_at DESC";
        break;
    default: 
        $sql .= " ORDER BY c.name ASC";
        break;
}


try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $categories = $stmt->fetchAll();
    
} catch (Exception $e) {
    addFlash('danger', 'Erreur lors de la récupération des catégories.');
    $categories = [];
}


$total_categories = count($categories);
$active_categories = 0;
$total_dishes_in_categories = 0;
$categories_without_dishes = 0;

foreach ($categories as $category) {
    if ($category['is_active']) {
        $active_categories++;
    }
    $total_dishes_in_categories += $category['dish_count'];
    if ($category['dish_count'] == 0) {
        $categories_without_dishes++;
    }
}


$pageTitle = "Gestion des catégories";
$pageIcon = "tags";
$pageDescription = "Organisez vos plats par catégories";
$breadcrumbs = [
    ['title' => 'Catégories', 'link' => '#', 'active' => true]
];
$actionButton = [
    'text' => 'Nouvelle catégorie',
    'icon' => 'plus-circle',
    'link' => 'add_category.php'
];


$template = 'templates/categories_list.phtml';
include 'templates/layout-admin.phtml';
?>