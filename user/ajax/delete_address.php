<?php
session_start();
require_once __DIR__ . '/../../admin/includes/config.php';

header('Content-Type: application/json');

// Activer les erreurs pour debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non connecté', 'debug' => 'Session user_id non défini']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Récupérer l'ID de l'adresse
$input = json_decode(file_get_contents('php://input'), true);

// Debug: Afficher l'input reçu
$debug_info = [
    'input_received' => $input,
    'php_input' => file_get_contents('php://input'),
    'user_id' => $user_id
];

if (!$input || !isset($input['address_id'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'Données invalides', 
        'debug' => $debug_info
    ]);
    exit();
}

$address_id = (int)$input['address_id'];

if ($address_id <= 0) {
    echo json_encode([
        'success' => false, 
        'message' => 'ID d\'adresse invalide: ' . $address_id,
        'debug' => $debug_info
    ]);
    exit();
}

try {
    // Vérifier d'abord si l'adresse appartient à l'utilisateur
    $stmt = $pdo->prepare("SELECT * FROM user_addresses WHERE id = ? AND user_id = ?");
    $stmt->execute([$address_id, $user_id]);
    $address = $stmt->fetch();
    
    if (!$address) {
        echo json_encode([
            'success' => false, 
            'message' => 'Adresse non trouvée ou vous n\'y avez pas accès',
            'debug' => [
                'address_id' => $address_id,
                'user_id' => $user_id,
                'address_found' => $address ? 'oui' : 'non'
            ]
        ]);
        exit();
    }
    
    // Vérifier si c'est l'adresse par défaut
    if ($address['is_default']) {
        // Si c'est l'adresse par défaut, on vérifie s'il y a d'autres adresses
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM user_addresses WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch();
        
        if ($result['total'] > 1) {
            // Supprimer l'adresse
            $stmt = $pdo->prepare("DELETE FROM user_addresses WHERE id = ? AND user_id = ?");
            $deleted = $stmt->execute([$address_id, $user_id]);
            
            // Définir une nouvelle adresse par défaut (la plus récente)
            $stmt = $pdo->prepare("
                UPDATE user_addresses 
                SET is_default = 1 
                WHERE user_id = ? 
                AND id != ?
                ORDER BY created_at DESC 
                LIMIT 1
            ");
            $stmt->execute([$user_id, $address_id]);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Adresse supprimée. Une nouvelle adresse a été définie par défaut.',
                'debug' => [
                    'deleted' => $deleted,
                    'was_default' => true,
                    'total_addresses' => $result['total']
                ]
            ]);
        } else {
            // C'est la seule adresse
            echo json_encode([
                'success' => false, 
                'message' => 'Vous ne pouvez pas supprimer votre seule adresse',
                'debug' => [
                    'total_addresses' => $result['total']
                ]
            ]);
        }
    } else {
        // Ce n'est pas l'adresse par défaut
        $stmt = $pdo->prepare("DELETE FROM user_addresses WHERE id = ? AND user_id = ?");
        $deleted = $stmt->execute([$address_id, $user_id]);
        
        if ($deleted) {
            echo json_encode([
                'success' => true, 
                'message' => 'Adresse supprimée avec succès',
                'debug' => ['rows_deleted' => $stmt->rowCount()]
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Erreur lors de la suppression',
                'debug' => ['error_info' => $stmt->errorInfo()]
            ]);
        }
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Erreur: ' . $e->getMessage(),
        'debug' => ['exception' => $e->getMessage()]
    ]);
}
?>