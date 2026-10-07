<?php
require_once 'admin/includes/config.php';
require_once 'admin/includes/auth.php';


$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($username) || empty($email) || empty($password)) {
        $error = "Tous les champs sont obligatoires";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format d'email invalide";
    } elseif ($password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas";
    } elseif (strlen($password) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caractères";
    } else {
        try {
            
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->fetch()) {
                $error = "Cet utilisateur ou email existe déjà";
            } else {
                
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $role = 'client'; 
                
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, is_active, created_at) VALUES (?, ?, ?, ?, 1, NOW())");
                $stmt->execute([$username, $email, $hashed_password, $role]);
                
                $success = "Compte créé avec succès ! Redirection vers la page de connexion...";
                header("Refresh: 2; url=login.php");
            }
        } catch (PDOException $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}


include 'admin/templates/register.phtml';