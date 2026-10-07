<?php
session_start();
require_once '../admin/includes/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non connecté', 'valid' => false]);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$address = $input['address'] ?? '';
$city = $input['city'] ?? '';
$postal_code = $input['postal_code'] ?? '';

// Validation basique
$errors = [];
$is_valid = true;

// Vérifier adresse
if (empty($address)) {
    $errors[] = 'Adresse requise';
    $is_valid = false;
} elseif (strlen($address) < 10) {
    $errors[] = 'Adresse trop courte (minimum 10 caractères)';
    $is_valid = false;
}

// Vérifier ville
if (empty($city)) {
    $errors[] = 'Ville requise';
    $is_valid = false;
} elseif (strlen($city) < 2) {
    $errors[] = 'Nom de ville invalide';
    $is_valid = false;
}

// Vérifier code postal (Tunisien)
if (!empty($postal_code)) {
    if (!preg_match('/^\d{4}$/', $postal_code)) {
        $errors[] = 'Code postal invalide (4 chiffres requis)';
        $is_valid = false;
    }
}

// Si tout est valide, vérifier l'existence de la ville
if ($is_valid && !empty($city)) {
    $cities_tunisia = [
        'Tunis', 'Ariana', 'Ben Arous', 'Manouba', 'Nabeul', 'Zaghouan',
        'Bizerte', 'Béja', 'Jendouba', 'Kef', 'Siliana', 'Kairouan',
        'Kasserine', 'Sidi Bouzid', 'Sousse', 'Monastir', 'Mahdia',
        'Sfax', 'Gabès', 'Médenine', 'Tataouine', 'Tozeur', 'Kébili'
    ];
    
    $city_found = false;
    foreach ($cities_tunisia as $valid_city) {
        if (strcasecmp(trim($city), $valid_city) === 0) {
            $city_found = true;
            break;
        }
    }
    
    if (!$city_found) {
        // Ville non trouvée mais on continue quand même (peut être une nouvelle ville)
        $errors[] = "Ville '$city' non reconnue, vérifiez l'orthographe";
        // On ne met pas $is_valid = false car l'utilisateur peut ajouter une nouvelle ville
    }
}

// Retourner résultat
echo json_encode([
    'success' => true,
    'valid' => $is_valid,
    'message' => $is_valid ? 'Adresse valide' : 'Veuillez corriger les erreurs',
    'errors' => $errors
]);
exit();