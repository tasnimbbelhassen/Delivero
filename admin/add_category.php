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


$prefilled_name = $_GET['name'] ?? '';
$prefilled_icon = $_GET['icon'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $icon = trim($_POST['icon'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
   
    if (empty($name)) {
        $errors[] = "Le nom de la catégorie est obligatoire.";
    }
    
    if (empty($errors)) {
        try {
           
            $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
            $stmt->execute([$name]);
            
            if ($stmt->fetch()) {
                $errors[] = "Une catégorie avec ce nom existe déjà.";
            } else {
               
                $stmt = $pdo->prepare("
                    INSERT INTO categories (name, description, icon, is_active) 
                    VALUES (?, ?, ?, ?)
                ");
                
                $stmt->execute([$name, $description, $icon, $is_active]);
                
                $category_id = $pdo->lastInsertId();
                
                addFlash('success', 'Catégorie ajoutée avec succès !');
                header('Location: category_details.php?id=' . $category_id);
                exit();
            }
            
        } catch (Exception $e) {
            $errors[] = "Erreur lors de l'ajout de la catégorie : " . $e->getMessage();
        }
    }
}


$pageTitle = "Ajouter une catégorie";
$pageIcon = "plus-circle";
$pageDescription = "Créez une nouvelle catégorie pour organiser vos plats";
$breadcrumbs = [
    ['title' => 'Catégories', 'link' => 'categories.php', 'active' => false],
    ['title' => 'Ajouter', 'link' => '#', 'active' => true]
];


$template = 'templates/category_form.phtml';
include 'templates/layout-admin.phtml';
?>