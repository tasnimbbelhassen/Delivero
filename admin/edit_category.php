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
    addFlash('danger', 'ID de la catégorie manquant.');
    header('Location: categories.php');
    exit();
}

$category_id = $_GET['id'];


$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$category_id]);
$category = $stmt->fetch();

if (!$category) {
    addFlash('danger', 'Catégorie non trouvée.');
    header('Location: categories.php');
    exit();
}

$errors = [];

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
            
            $check_stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ? AND id != ?");
            $check_stmt->execute([$name, $category_id]);
            
            if ($check_stmt->fetch()) {
                $errors[] = "Une autre catégorie avec ce nom existe déjà.";
            } else {
                
                $update_stmt = $pdo->prepare("
                    UPDATE categories 
                    SET name = ?, description = ?, icon = ?, is_active = ?
                    WHERE id = ?
                ");
                
                $update_stmt->execute([$name, $description, $icon, $is_active, $category_id]);
                
                addFlash('success', 'Catégorie mise à jour avec succès !');
                header('Location: category_details.php?id=' . $category_id);
                exit();
            }
        } catch (Exception $e) {
            $errors[] = "Erreur lors de la mise à jour : " . $e->getMessage();
        }
    }
}


$pageTitle = "Modifier la catégorie";
$pageIcon = "pencil";
$pageDescription = "Modifiez les informations de " . htmlspecialchars($category['name']);
$breadcrumbs = [
    ['title' => 'Catégories', 'link' => 'categories.php', 'active' => false],
    ['title' => htmlspecialchars($category['name']), 'link' => 'category_details.php?id=' . $category_id, 'active' => false],
    ['title' => 'Modifier', 'link' => '#', 'active' => true]
];


$template = 'templates/category_form.phtml';
include 'templates/layout-admin.phtml';
?>