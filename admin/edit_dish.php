<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
redirectIfNotAdmin();


if (!isset($_GET['id']) || empty($_GET['id'])) {
    addFlash('danger', 'ID du plat manquant.');
    header('Location: dishes.php');
    exit();
}

$dish_id = $_GET['id'];


$stmt = $pdo->prepare("
    SELECT d.*, c.name as category_name, r.name as restaurant_name
    FROM dishes d
    LEFT JOIN categories c ON d.category_id = c.id
    LEFT JOIN restaurants r ON d.restaurant_id = r.id
    WHERE d.id = ?
");

$stmt->execute([$dish_id]);
$dish = $stmt->fetch();

if (!$dish) {
    addFlash('danger', 'Plat non trouvé.');
    header('Location: dishes.php');
    exit();
}


$categories = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name")->fetchAll();
$restaurants = $pdo->query("SELECT * FROM restaurants WHERE is_active = 1 ORDER BY name")->fetchAll();

$errors = [];

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
            
            
            $image_path = $dish['image'];
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                require_once 'includes/functions.php';
                $uploadResult = uploadImage($_FILES['image'], '../uploads/dishes/');
                
                if ($uploadResult['success']) {
                    
                    if (!empty($dish['image']) && file_exists('../' . $dish['image'])) {
                        unlink('../' . $dish['image']);
                    }
                    $image_path = 'uploads/dishes/' . $uploadResult['filename'];
                } else {
                    $errors = array_merge($errors, $uploadResult['errors']);
                }
            }
            
            if (empty($errors)) {
                
                $stmt = $pdo->prepare("
                    UPDATE dishes 
                    SET name = ?, description = ?, price = ?, category_id = ?, restaurant_id = ?, 
                        image = ?, preparation_time = ?, calories = ?, ingredients = ?, 
                        is_available = ?, is_special = ?, is_vegetarian = ?
                    WHERE id = ?
                ");
                
                $stmt->execute([
                    $name, $description, $price, $category_id, $restaurant_id, $image_path,
                    $preparation_time, $calories, $ingredients, 
                    $is_available, $is_special, $is_vegetarian, $dish_id
                ]);
                
                $pdo->commit();
                
                addFlash('success', 'Plat mis à jour avec succès !');
                header('Location: dish_details.php?id=' . $dish_id);
                exit();
            } else {
                $pdo->rollBack();
            }
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Erreur lors de la mise à jour : " . $e->getMessage();
        }
    }
}

// Configuration pour le layout
$pageTitle = "Modifier le plat";
$pageIcon = "pencil";
$pageDescription = "Modifiez les informations de " . htmlspecialchars($dish['name']);
$breadcrumbs = [
    ['title' => 'Plats', 'link' => 'dishes.php', 'active' => false],
    ['title' => htmlspecialchars($dish['name']), 'link' => 'dish_details.php?id=' . $dish_id, 'active' => false],
    ['title' => 'Modifier', 'link' => '#', 'active' => true]
];

// Rendu
$template = 'templates/dish_form.phtml';
include 'templates/layout-admin.phtml';
?>