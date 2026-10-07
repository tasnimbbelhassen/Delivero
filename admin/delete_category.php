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
    addFlash('danger', 'ID de la catégorie manquant.');
    header('Location: categories.php');
    exit();
}

$category_id = $_GET['id'];

try {
    
    $stmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
    $stmt->execute([$category_id]);
    $category = $stmt->fetch();
    
    if ($category) {
        
        $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM dishes WHERE category_id = ?");
        $count_stmt->execute([$category_id]);
        $dish_count = $count_stmt->fetchColumn();
        
        if ($dish_count > 0) {
            

            addFlash('warning', 'Cette catégorie contient ' . $dish_count . ' plat(s). Veuillez d\'abord déplacer ces plats.');
            header('Location: categories.php');
            exit();
        } else {
            
            $delete_stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $delete_stmt->execute([$category_id]);
            
            addFlash('success', 'Catégorie "' . htmlspecialchars($category['name']) . '" supprimée avec succès.');
        }
    } else {
        addFlash('warning', 'Catégorie non trouvée.');
    }
} catch (Exception $e) {
    addFlash('danger', 'Erreur lors de la suppression : ' . $e->getMessage());
}

header('Location: categories.php');
exit();
?>