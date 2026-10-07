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
    addFlash('danger', 'ID du restaurant manquant.');
    header('Location: restaurants.php');
    exit();
}

$restaurant_id = $_GET['id'];


$stmt = $pdo->prepare("SELECT * FROM restaurants WHERE id = ?");
$stmt->execute([$restaurant_id]);
$restaurant = $stmt->fetch();

if (!$restaurant) {
    addFlash('danger', 'Restaurant non trouvé.');
    header('Location: restaurants.php');
    exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $name = trim($_POST['name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $delivery_time = trim($_POST['delivery_time'] ?? '30-40 min');
    $rating = $_POST['rating'] ?? 4.0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    
    if (empty($name)) {
        $errors[] = "Le nom du restaurant est obligatoire.";
    }
    
    if (empty($address)) {
        $errors[] = "L'adresse est obligatoire.";
    }
    
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse email n'est pas valide.";
    }
    
    if ($rating < 0 || $rating > 5) {
        $errors[] = "La note doit être entre 0 et 5.";
    }
    
    if (empty($errors)) {
        try {
            
            $image_path = $restaurant['image'];
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                require_once 'includes/functions.php';
                $uploadResult = uploadImage($_FILES['image'], '../uploads/restaurants/');
                
                if ($uploadResult['success']) {
                    
                    if (!empty($restaurant['image']) && file_exists('../' . $restaurant['image'])) {
                        unlink('../' . $restaurant['image']);
                    }
                    $image_path = 'uploads/restaurants/' . $uploadResult['filename'];
                } else {
                    $errors = array_merge($errors, $uploadResult['errors']);
                }
            }
            
            if (empty($errors)) {
                
                $stmt = $pdo->prepare("
                    UPDATE restaurants 
                    SET name = ?, address = ?, phone = ?, email = ?, image = ?, 
                        delivery_time = ?, rating = ?, is_active = ?
                    WHERE id = ?
                ");
                
                $stmt->execute([
                    $name, $address, $phone, $email, $image_path, 
                    $delivery_time, $rating, $is_active, $restaurant_id
                ]);
                
                addFlash('success', 'Restaurant mis à jour avec succès !');
                header('Location: restaurant_details.php?id=' . $restaurant_id);
                exit();
            }
            
        } catch (Exception $e) {
            $errors[] = "Erreur lors de la mise à jour : " . $e->getMessage();
        }
    }
}


$pageTitle = "Modifier le restaurant";
$pageIcon = "pencil";
$pageDescription = "Modifiez les informations de " . htmlspecialchars($restaurant['name']);
$breadcrumbs = [
    ['title' => 'Restaurants', 'link' => 'restaurants.php', 'active' => false],
    ['title' => htmlspecialchars($restaurant['name']), 'link' => 'restaurant_details.php?id=' . $restaurant_id, 'active' => false],
    ['title' => 'Modifier', 'link' => '#', 'active' => true]
];


$template = 'templates/restaurant_form.phtml';
include 'templates/layout-admin.phtml';
?>