<?php
session_start();
require_once '../admin/includes/config.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$user_lat = $input['lat'] ?? null;
$user_lng = $input['lng'] ?? null;

// Pour la démo, utiliser une position par défaut (Tunis)
if (!$user_lat || !$user_lng) {
    $user_lat = 36.8065;
    $user_lng = 10.1815;
}

// Récupérer restaurants avec distance calculée (simulée)
$stmt = $pdo->prepare("
    SELECT r.*,
           (6371 * acos(
               cos(radians(?)) * cos(radians(36.8065)) *
               cos(radians(10.1815) - radians(?)) +
               sin(radians(?)) * sin(radians(36.8065))
           )) as distance_km
    FROM restaurants r
    WHERE r.is_active = 1
    HAVING distance_km < 10
    ORDER BY distance_km ASC
    LIMIT 20
");

$stmt->execute([$user_lat, $user_lng, $user_lat]);
$restaurants = $stmt->fetchAll();

// Formater les données
$formatted_restaurants = [];
foreach ($restaurants as $restaurant) {
    $formatted_restaurants[] = [
        'id' => $restaurant['id'],
        'name' => $restaurant['name'],
        'address' => $restaurant['address'],
        'phone' => $restaurant['phone'],
        'rating' => $restaurant['rating'],
        'delivery_time' => $restaurant['delivery_time'],
        'image' => $restaurant['image'],
        'distance' => round($restaurant['distance_km'], 1),
        'is_open' => true // Simulé
    ];
}

echo json_encode([
    'success' => true,
    'restaurants' => $formatted_restaurants,
    'count' => count($formatted_restaurants)
]);