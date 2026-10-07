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

$errors = [];
$success = false;

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
    
    if (empty($errors)) {
        try {
            
            $image_path = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                require_once 'includes/functions.php';
                $uploadResult = uploadImage($_FILES['image'], '../uploads/restaurants/');
                
                if ($uploadResult['success']) {
                    $image_path = 'uploads/restaurants/' . $uploadResult['filename'];
                } else {
                    $errors = array_merge($errors, $uploadResult['errors']);
                }
            }
            
            if (empty($errors)) {
                
                $stmt = $pdo->prepare("
                    INSERT INTO restaurants 
                    (name, address, phone, email, image, delivery_time, rating, is_active) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $name, $address, $phone, $email, $image_path, 
                    $delivery_time, $rating, $is_active
                ]);
                
                addFlash('success', 'Restaurant ajouté avec succès !');
                header('Location: restaurants.php');
                exit();
            }
            
        } catch (Exception $e) {
            $errors[] = "Erreur lors de l'ajout du restaurant : " . $e->getMessage();
        }
    }
}


$pageTitle = "Ajouter un restaurant";
$pageIcon = "plus-circle";
$pageDescription = "Ajoutez un nouveau restaurant partenaire";
$breadcrumbs = [
    ['title' => 'Restaurants', 'link' => 'restaurants.php', 'active' => false],
    ['title' => 'Ajouter', 'link' => '#', 'active' => true]
];


$template = 'templates/restaurant_form.phtml';
include 'templates/layout-admin.phtml';
?>