<?php
session_start();
require_once __DIR__ . '/../../admin/includes/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non connecté']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Récupérer l'ID de l'adresse
$address_id = isset($_POST['address_id']) ? (int)$_POST['address_id'] : 0;

if ($address_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID d\'adresse invalide']);
    exit();
}

try {
    // Vérifier d'abord que l'adresse appartient à l'utilisateur
    $stmt = $pdo->prepare("SELECT * FROM user_addresses WHERE id = ? AND user_id = ?");
    $stmt->execute([$address_id, $user_id]);
    $address = $stmt->fetch();
    
    if (!$address) {
        echo json_encode([
            'success' => false, 
            'message' => 'Adresse non trouvée ou vous n\'y avez pas accès'
        ]);
        exit();
    }
    
    // Démarrer une transaction
    $pdo->beginTransaction();
    
    // 1. Réinitialiser toutes les adresses de l'utilisateur
    $stmt = $pdo->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = ?");
    $stmt->execute([$user_id]);
    
    // 2. Définir l'adresse spécifique comme par défaut
    $stmt = $pdo->prepare("UPDATE user_addresses SET is_default = 1 WHERE id = ? AND user_id = ?");
    $stmt->execute([$address_id, $user_id]);
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Adresse définie comme par défaut avec succès'
    ]);
    
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false, 
        'message' => 'Erreur: ' . $e->getMessage()
    ]);
}
?>