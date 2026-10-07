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


$search_query = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$sort_filter = $_GET['sort'] ?? 'newest';


$sql = "SELECT * FROM restaurants WHERE 1=1";
$params = [];

if (!empty($search_query)) {
    $sql .= " AND (name LIKE ? OR address LIKE ? OR phone LIKE ? OR email LIKE ?)";
    $search_term = "%$search_query%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

if ($status_filter === 'active') {
    $sql .= " AND is_active = 1";
} elseif ($status_filter === 'inactive') {
    $sql .= " AND is_active = 0";
}


switch ($sort_filter) {
    case 'oldest':
        $sql .= " ORDER BY created_at ASC";
        break;
    case 'rating':
        $sql .= " ORDER BY rating DESC";
        break;
    case 'name':
        $sql .= " ORDER BY name ASC";
        break;
    default:
        $sql .= " ORDER BY created_at DESC";
        break;
}


$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$restaurants = $stmt->fetchAll();


$pageTitle = "Gestion des restaurants";
$pageIcon = "shop";
$pageDescription = "Gérez les restaurants partenaires de Delivero";
$breadcrumbs = [
    ['title' => 'Restaurants', 'link' => '#', 'active' => true]
];
$actionButton = [
    'text' => 'Nouveau restaurant',
    'icon' => 'plus-circle',
    'link' => 'add_restaurant.php'
];


$template = 'templates/restaurants_list.phtml';
include 'templates/layout-admin.phtml';
?>