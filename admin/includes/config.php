<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


define('DB_HOST', 'localhost');
define('DB_NAME', 'delivero_admin');
define('DB_USER', 'root');
define('DB_PASS', '');


define('SITE_NAME', 'Delivero Admin');
define('BASE_URL', 'http://localhost/mini-projet/');
define('ADMIN_URL', BASE_URL . 'admin/');
define('UPLOAD_PATH', '../uploads/');


try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}


function addFlash($type, $message) {
    if (!isset($_SESSION['flash'])) {
        $_SESSION['flash'] = [];
    }
    $_SESSION['flash'][$type][] = $message;
}


function displayFlash() {
    if (isset($_SESSION['flash'])) {
        foreach ($_SESSION['flash'] as $type => $messages) {
            foreach ($messages as $message) {
                echo '<div class="alert alert-' . htmlspecialchars($type) . ' alert-dismissible fade show">';
                echo htmlspecialchars($message);
                echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                echo '</div>';
            }
        }
        unset($_SESSION['flash']);
    }
}


function isLoggedIn() {
    return isset($_SESSION['user_id']);
}



function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
  
}


function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . 'login.php');
        exit();
    }
}


function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        addFlash('danger', 'Accès non autorisé.');
        header('Location: ' . BASE_URL);
        exit();
    }
}


function cleanInput($data) {
    if (is_array($data)) {
        return array_map('cleanInput', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}


function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}


function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}