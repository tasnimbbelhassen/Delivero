<?php
session_start();
require_once '../admin/includes/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non connecté']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$address_id = $input['address_id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$address_id) {
    echo json_encode(['success' => false, 'message' => 'Adresse non spécifiée']);
    exit();
}

// Vérifier que l'adresse appartient à l'utilisateur
$stmt = $pdo->prepare("SELECT user_id FROM user_addresses WHERE id = ?");
$stmt->execute([$address_id]);
$address = $stmt->fetch();

if (!$address || $address['user_id'] != $user_id) {
    echo json_encode(['success' => false, 'message' => 'Adresse non trouvée']);
    exit();
}

// Sauvegarder comme dernière adresse utilisée
$_SESSION['last_address_id'] = $address_id;

// Récupérer les détails de l'adresse
$stmt = $pdo->prepare("SELECT * FROM user_addresses WHERE id = ?");
$stmt->execute([$address_id]);
$address_details = $stmt->fetch();

echo json_encode([
    'success' => true,
    'address' => $address_details,
    'message' => 'Adresse sauvegardée'
]);