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


if (!isset($_GET['id']) || empty($_GET['id'])) {
    addFlash('danger', 'ID du restaurant manquant.');
    header('Location: restaurants.php');
    exit();
}

$restaurant_id = $_GET['id'];

try {
    $pdo->beginTransaction();
    
    
    $stmt = $pdo->prepare("SELECT * FROM restaurants WHERE id = ?");
    $stmt->execute([$restaurant_id]);
    $restaurant = $stmt->fetch();
    
    if ($restaurant) {
        
        $dishes_stmt = $pdo->prepare("SELECT id, image FROM dishes WHERE restaurant_id = ?");
        $dishes_stmt->execute([$restaurant_id]);
        $dishes = $dishes_stmt->fetchAll();
        
        
        foreach ($dishes as $dish) {
            if (!empty($dish['image']) && file_exists('../' . $dish['image'])) {
                unlink('../' . $dish['image']);
            }
        }
        
        
        if (!empty($restaurant['image']) && file_exists('../' . $restaurant['image'])) {
            unlink('../' . $restaurant['image']);
        }
        
        
        $delete_dishes = $pdo->prepare("DELETE FROM dishes WHERE restaurant_id = ?");
        $delete_dishes->execute([$restaurant_id]);
        
       
        $delete_restaurant = $pdo->prepare("DELETE FROM restaurants WHERE id = ?");
        $delete_restaurant->execute([$restaurant_id]);
        
        $pdo->commit();
        
        addFlash('success', 'Restaurant et tous ses plats ont été supprimés avec succès.');
    } else {
        addFlash('warning', 'Restaurant non trouvé.');
    }
    
} catch (Exception $e) {
    $pdo->rollBack();
    addFlash('danger', 'Erreur lors de la suppression : ' . $e->getMessage());
}

header('Location: restaurants.php');
exit();
?>