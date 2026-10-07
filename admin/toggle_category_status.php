<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';


if (!isLoggedIn() || !isAdmin()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = $_POST['id'] ?? '';
    $status = $_POST['status'] ?? 0;
    
    if (empty($category_id)) {
        echo json_encode(['success' => false, 'message' => 'ID manquant']);
        exit();
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE categories SET is_active = ? WHERE id = ?");
        $stmt->execute([$status, $category_id]);
        
        echo json_encode(['success' => true, 'message' => 'Statut mis à jour']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}
?>