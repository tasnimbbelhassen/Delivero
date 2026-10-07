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
    addFlash('danger', 'ID du plat manquant.');
    header('Location: dishes.php');
    exit();
}

$dish_id = $_GET['id'];

try {
    
    $stmt = $pdo->prepare("SELECT image FROM dishes WHERE id = ?");
    $stmt->execute([$dish_id]);
    $dish = $stmt->fetch();
    
    if ($dish) {
        
        if (!empty($dish['image']) && file_exists('../' . $dish['image'])) {
            unlink('../' . $dish['image']);
        }
        
        
        $stmt = $pdo->prepare("DELETE FROM dishes WHERE id = ?");
        $stmt->execute([$dish_id]);
        
        addFlash('success', 'Plat supprimé avec succès.');
    } else {
        addFlash('warning', 'Plat déjà supprimé ou non trouvé.');
    }
    
} catch (Exception $e) {
    addFlash('danger', 'Erreur lors de la suppression : ' . $e->getMessage());
}

header('Location: dishes.php');
exit();
?>