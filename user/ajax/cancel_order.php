<?php
// DÉSACTIVER tout affichage d'erreur qui pourrait briser le JSON
error_reporting(0);
ini_set('display_errors', 0);

session_start();
header('Content-Type: application/json');

// Initialiser la réponse
$response = ['success' => false, 'message' => 'Erreur inconnue'];

try {
    // Vérifier si l'utilisateur est connecté
    if (!isset($_SESSION['user_id'])) {
        $response['message'] = 'Non connecté';
        echo json_encode($response);
        exit();
    }

    // Récupérer l'ID de commande
    $order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($order_id <= 0) {
        $response['message'] = 'ID de commande invalide';
        echo json_encode($response);
        exit();
    }

    // Tentative de chargement de la configuration
    $config_path = __DIR__ . '/../admin/includes/config.php';
    
    // Essayer plusieurs chemins possibles
    if (!file_exists($config_path)) {
        $config_path = dirname(dirname(__DIR__)) . '/admin/includes/config.php';
    }
    
    if (!file_exists($config_path)) {
        $response['message'] = 'Fichier de configuration non trouvé';
        echo json_encode($response);
        exit();
    }
    
    require_once $config_path;

    // Vérifier si $pdo existe
    if (!isset($pdo)) {
        $response['message'] = 'Erreur de connexion à la base de données';
        echo json_encode($response);
        exit();
    }

    // Vérifier commande
    $stmt = $pdo->prepare("SELECT status FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$order_id, $_SESSION['user_id']]);
    $order = $stmt->fetch();

    if (!$order) {
        $response['message'] = 'Commande non trouvée';
        echo json_encode($response);
        exit();
    }

    if ($order['status'] != 'pending') {
        $response['message'] = 'Seules les commandes en attente peuvent être annulées';
        echo json_encode($response);
        exit();
    }

    // Annuler commande
    $stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$order_id, $_SESSION['user_id']])) {
        $response['success'] = true;
        $response['message'] = 'Commande annulée avec succès';
    } else {
        $response['message'] = 'Erreur lors de la mise à jour';
    }

} catch (PDOException $e) {
    $response['message'] = 'Erreur base de données: ' . $e->getMessage();
} catch (Exception $e) {
    $response['message'] = 'Erreur: ' . $e->getMessage();
}

// S'assurer qu'aucun output n'a été envoyé avant
ob_clean();
echo json_encode($response);
?>