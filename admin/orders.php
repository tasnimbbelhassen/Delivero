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
$start_date_filter = $_GET['start_date'] ?? '';
$end_date_filter = $_GET['end_date'] ?? '';
$search_query = $_GET['search'] ?? '';


$sql = "SELECT o.* FROM orders o WHERE 1=1";
$params = [];

if (!empty($search_query)) {
    $sql .= " AND (o.order_number LIKE ? OR o.delivery_address LIKE ?)";
    $search_term = "%" . trim($search_query) . "%";
    $params[] = $search_term;
    $params[] = $search_term;
}

if (!empty($status_filter)) {
    $sql .= " AND o.status = ?";
    $params[] = $status_filter;
}

if (!empty($start_date_filter)) {
    $sql .= " AND DATE(o.created_at) >= ?";
    $params[] = $start_date_filter;
}

if (!empty($end_date_filter)) {
    $sql .= " AND DATE(o.created_at) <= ?";
    $params[] = $end_date_filter;
}

$sql .= " ORDER BY o.created_at DESC";


try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();
    
} catch (Exception $e) {
    addFlash('danger', 'Erreur lors de la récupération des commandes.');
    $orders = [];
}


if (!empty($orders)) {
    $order_ids = array_column($orders, 'id');
    $order_ids_str = implode(',', $order_ids);
    
    
    $users_data = [];
    $user_stmt = $pdo->query("
        SELECT o.id as order_id, u.username, u.full_name, u.email 
        FROM users u 
        INNER JOIN orders o ON u.id = o.user_id 
        WHERE o.id IN ($order_ids_str)
    ");
    $users_result = $user_stmt->fetchAll();
    
    foreach ($users_result as $user_row) {
        $users_data[$user_row['order_id']] = [
            'username' => $user_row['username'],
            'full_name' => $user_row['full_name'],
            'email' => $user_row['email']
        ];
    }
    
   
    $items_data = [];
    $items_stmt = $pdo->query("
        SELECT oi.order_id, 
               COUNT(oi.id) as items_count,
               GROUP_CONCAT(d.name SEPARATOR ', ') as dish_names
        FROM order_items oi
        LEFT JOIN dishes d ON oi.dish_id = d.id
        WHERE oi.order_id IN ($order_ids_str)
        GROUP BY oi.order_id
    ");
    $items_result = $items_stmt->fetchAll();
    
    foreach ($items_result as $item_row) {
        $items_data[$item_row['order_id']] = [
            'items_count' => $item_row['items_count'],
            'dish_names' => $item_row['dish_names']
        ];
    }
    
    
    foreach ($orders as &$order) {
        $order_id = $order['id'];
        
        
        if (isset($users_data[$order_id])) {
            $order['username'] = $users_data[$order_id]['username'];
            $order['full_name'] = $users_data[$order_id]['full_name'];
            $order['email'] = $users_data[$order_id]['email'];
        } else {
            $order['username'] = 'N/A';
            $order['full_name'] = 'N/A';
            $order['email'] = 'N/A';
        }
        
        
        if (isset($items_data[$order_id])) {
            $order['items_count'] = $items_data[$order_id]['items_count'];
            $order['dish_names'] = $items_data[$order_id]['dish_names'];
        } else {
            $order['items_count'] = 0;
            $order['dish_names'] = 'N/A';
        }
    }
    unset($order); 
}


$pendingCount = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
$todayCount = $pdo->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()")->fetchColumn();
$todayRevenue = $pdo->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE DATE(created_at) = CURDATE()")->fetchColumn();
$totalCount = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalRevenue = $pdo->query("SELECT COALESCE(SUM(total), 0) FROM orders")->fetchColumn();


$pageTitle = "Gestion des commandes";
$pageIcon = "cart";
$pageDescription = "Suivez et gérez les commandes des clients";
$breadcrumbs = [
    ['title' => 'Commandes', 'link' => '#', 'active' => true]
];


$template = 'templates/orders_list.phtml';
include 'templates/layout-admin.phtml';
?>