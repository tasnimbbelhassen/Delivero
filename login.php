<?php
require_once 'admin/includes/config.php';
require_once 'admin/includes/auth.php';


$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Tous les champs sont obligatoires";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                if ($user['is_active'] == 1) {
                    
                    loginUser($user);
                    
                    
                    $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                    $stmt->execute([$user['id']]);
                    
                    
                    if ($user['role'] === 'admin') {
                        header('Location: admin/index.php');
                    } else {
                        header('Location: user/index.php');
                    }
                    exit();
                } else {
                    $error = "Votre compte est désactivé. Contactez l'administrateur.";
                }
            } else {
                $error = "Nom d'utilisateur ou mot de passe incorrect !";
            }
        } catch (PDOException $e) {
            $error = "Erreur de connexion : " . $e->getMessage();
        }
    }
}


include 'admin/templates/login.phtml';
?>