<?php
session_start();
require_once '../admin/includes/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non connecté']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$dish_id = $input['dish_id'] ?? null;
$action = $input['action'] ?? 'toggle';
$user_id = $_SESSION['user_id'];

if (!$dish_id) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
    exit();
}

// Vérifier si la table favorites existe
$tableExists = $pdo->query("SHOW TABLES LIKE 'favorites'")->fetch();

if (!$tableExists) {
    // Créer table si elle n'existe pas
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS favorites (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            dish_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (dish_id) REFERENCES dishes(id) ON DELETE CASCADE,
            UNIQUE KEY unique_favorite (user_id, dish_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
}

if ($action === 'add' || $action === 'toggle') {
    // Vérifier si déjà favori
    $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND dish_id = ?");
    $stmt->execute([$user_id, $dish_id]);
    $exists = $stmt->fetch();
    
    if ($exists && $action === 'toggle') {
        // Supprimer
        $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND dish_id = ?");
        $stmt->execute([$user_id, $dish_id]);
        
        echo json_encode([
            'success' => true,
            'is_favorite' => false,
            'message' => 'Retiré des favoris'
        ]);
    } elseif (!$exists) {
        // Ajouter
        $stmt = $pdo->prepare("INSERT INTO favorites (user_id, dish_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $dish_id]);
        
        echo json_encode([
            'success' => true,
            'is_favorite' => true,
            'message' => 'Ajouté aux favoris'
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'is_favorite' => true,
            'message' => 'Déjà dans les favoris'
        ]);
    }
} elseif ($action === 'remove') {
    $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND dish_id = ?");
    $stmt->execute([$user_id, $dish_id]);
    
    echo json_encode([
        'success' => true,
        'is_favorite' => false,
        'message' => 'Retiré des favoris'
    ]);
}