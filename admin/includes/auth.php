<?php
require_once __DIR__ . '/config.php';


function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        if ($_SESSION['role'] === 'admin') {
            header('Location: ' . BASE_URL . 'admin/index.php');
        } else {
            header('Location: ' . BASE_URL . 'index.php');
        }
        exit();
    }
}


function redirectIfNotAdmin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . 'login.php');
        exit();
    }
    
    
    if (!isAdmin()) {
        addFlash('danger', 'Accès non autorisé.');
        header('Location: ' . BASE_URL);
        exit();
    }
}


function loginUser($user) {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];  
    $_SESSION['login_time'] = time();
}


function logoutUser() {
    session_destroy();
    header('Location: ' . BASE_URL . 'login.php');
    exit();
}
?>