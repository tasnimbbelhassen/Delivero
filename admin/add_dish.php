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


$categories = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name")->fetchAll();
$restaurants = $pdo->query("SELECT * FROM restaurants WHERE is_active = 1 ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = $_POST['price'] ?? 0;
    $category_id = $_POST['category_id'] ?? null;
    $restaurant_id = $_POST['restaurant_id'] ?? null;
    $preparation_time = $_POST['preparation_time'] ?? 20;
    $calories = $_POST['calories'] ?? null;
    $ingredients = trim($_POST['ingredients'] ?? '');
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    $is_special = isset($_POST['is_special']) ? 1 : 0;
    $is_vegetarian = isset($_POST['is_vegetarian']) ? 1 : 0;
  
    if (empty($name)) {
        $errors[] = "Le nom du plat est obligatoire.";
    }
    
    if ($price <= 0) {
        $errors[] = "Le prix doit être supérieur à 0.";
    }
    
    if (empty($category_id)) {
        $errors[] = "Veuillez sélectionner une catégorie.";
    }
    
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            
            $image_path = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                require_once 'includes/functions.php';
                $uploadResult = uploadImage($_FILES['image'], '../uploads/dishes/');
                
                if ($uploadResult['success']) {
                    $image_path = 'uploads/dishes/' . $uploadResult['filename'];
                } else {
                    $errors = array_merge($errors, $uploadResult['errors']);
                    throw new Exception("Erreur lors du téléchargement de l'image");
                }
            }
            
            
            $stmt = $pdo->prepare("
                INSERT INTO dishes 
                (name, description, price, category_id, restaurant_id, image, 
                 preparation_time, calories, ingredients, is_available, 
                 is_special, is_vegetarian, rating, order_count) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $name, $description, $price, $category_id, $restaurant_id, $image_path,
                $preparation_time, $calories, $ingredients, $is_available,
                $is_special, $is_vegetarian, 4.0, 0
            ]);
            
            $dish_id = $pdo->lastInsertId();
            $pdo->commit();
            
            addFlash('success', 'Plat ajouté avec succès !');
            header('Location: dish_details.php?id=' . $dish_id);
            exit();
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Erreur lors de l'ajout du plat : " . $e->getMessage();
        }
    }
}


$pageTitle = "Ajouter un plat";
$pageIcon = "plus-circle";
$pageDescription = "Ajoutez un nouveau plat à votre catalogue";
$breadcrumbs = [
    ['title' => 'Plats', 'link' => 'dishes.php', 'active' => false],
    ['title' => 'Ajouter', 'link' => '#', 'active' => true]
];


$template = 'templates/dish_form.phtml';
include 'templates/layout-admin.phtml';