<?php

date_default_timezone_set('Africa/Tunis');


define('DB_HOST', 'localhost');
define('DB_NAME', 'delivero');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');


define('LOG_ERROR', 1);
define('LOG_WARNING', 2);
define('LOG_INFO', 3);
define('LOG_DEBUG', 4);


define('DEBUG_MODE', true);


define('BASE_PATH', dirname(dirname(__FILE__)));
define('UPLOAD_PATH', BASE_PATH . '/uploads');
define('LOG_PATH', BASE_PATH . '/logs');


define('BASE_URL', 'http://localhost/delivero');
define('ASSETS_URL', BASE_URL . '/assets');


define('SESSION_TIMEOUT', 3600); 
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 900); 


define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'no-reply@delivero.tn');
define('SMTP_PASS', '');
define('SMTP_FROM', 'no-reply@delivero.tn');
define('SMTP_FROM_NAME', 'Delivero');


define('APP_NAME', 'Delivero');
define('APP_VERSION', '1.0.0');
define('APP_ENV', 'development'); 


function app_log($message, $level = LOG_INFO, $context = []) {
    if (!DEBUG_MODE && $level === LOG_DEBUG) {
        return;
    }
    
    $log_levels = [
        LOG_ERROR => 'ERROR',
        LOG_WARNING => 'WARNING',
        LOG_INFO => 'INFO',
        LOG_DEBUG => 'DEBUG'
    ];
    
    $timestamp = date('Y-m-d H:i:s');
    $level_str = $log_levels[$level] ?? 'INFO';
    
    $log_message = "[$timestamp] [$level_str] $message";
    
    if (!empty($context)) {
        $log_message .= " " . json_encode($context, JSON_UNESCAPED_UNICODE);
    }
    
   
    $log_file = LOG_PATH . '/app_' . date('Y-m-d') . '.log';
    error_log($log_message . PHP_EOL, 3, $log_file);
    
   
    if (DEBUG_MODE && $level <= LOG_WARNING) {
        echo "<!-- Log: $log_message -->";
    }
}


function handle_error($errno, $errstr, $errfile, $errline) {
    $error_types = [
        E_ERROR => 'ERROR',
        E_WARNING => 'WARNING',
        E_PARSE => 'PARSE',
        E_NOTICE => 'NOTICE',
        E_CORE_ERROR => 'CORE_ERROR',
        E_CORE_WARNING => 'CORE_WARNING',
        E_COMPILE_ERROR => 'COMPILE_ERROR',
        E_COMPILE_WARNING => 'COMPILE_WARNING',
        E_USER_ERROR => 'USER_ERROR',
        E_USER_WARNING => 'USER_WARNING',
        E_USER_NOTICE => 'USER_NOTICE',
        E_STRICT => 'STRICT',
        E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR',
        E_DEPRECATED => 'DEPRECATED',
        E_USER_DEPRECATED => 'USER_DEPRECATED'
    ];
    
    $error_type = $error_types[$errno] ?? 'UNKNOWN';
    
    app_log("PHP $error_type: $errstr in $errfile on line $errline", LOG_ERROR);
    
    if (DEBUG_MODE) {
        return false; 
    }
    
   
    if (in_array($errno, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
        http_response_code(500);
        echo '<h1>Une erreur est survenue</h1>';
        echo '<p>Notre équipe technique a été notifiée. Veuillez réessayer plus tard.</p>';
        exit;
    }
    
    return true; 
}

set_error_handler('handle_error');


function handle_exception($exception) {
    app_log("EXCEPTION: " . $exception->getMessage() . " in " . $exception->getFile() . ":" . $exception->getLine(), LOG_ERROR, [
        'trace' => $exception->getTraceAsString()
    ]);
    
    if (DEBUG_MODE) {
        throw $exception;
    } else {
        http_response_code(500);
        echo '<h1>Une erreur inattendue est survenue</h1>';
        echo '<p>Notre équipe technique a été notifiée. Veuillez réessayer plus tard.</p>';
        exit;
    }
}

set_exception_handler('handle_exception');


try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_PERSISTENT => true, 
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
        PDO::MYSQL_ATTR_FOUND_ROWS => true
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    

    $pdo->exec("SET time_zone = '+01:00'");
    $pdo->exec("SET sql_mode = 'STRICT_ALL_TABLES'");
    
    app_log("Connexion à la base de données réussie", LOG_INFO);
    
} catch (PDOException $e) {
    app_log("Erreur de connexion à la base de données: " . $e->getMessage(), LOG_ERROR);
    
    if (DEBUG_MODE) {
        die("Erreur de connexion à la base de données: " . $e->getMessage());
    } else {
        die("<h1>Erreur de connexion à la base de données</h1>
             <p>Le service est temporairement indisponible. Veuillez réessayer plus tard.</p>");
    }
}


function db_query($sql, $params = []) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        app_log("Erreur SQL: " . $e->getMessage(), LOG_ERROR, [
            'sql' => $sql,
            'params' => $params
        ]);
        throw $e;
    }
}


function db_fetch($sql, $params = []) {
    $stmt = db_query($sql, $params);
    return $stmt->fetch();
}


function db_fetch_all($sql, $params = []) {
    $stmt = db_query($sql, $params);
    return $stmt->fetchAll();
}


function db_value($sql, $params = []) {
    $stmt = db_query($sql, $params);
    return $stmt->fetchColumn();
}


function db_insert($table, $data) {
    global $pdo;
    
    $columns = array_keys($data);
    $placeholders = array_fill(0, count($columns), '?');
    $values = array_values($data);
    
    $sql = "INSERT INTO $table (" . implode(', ', $columns) . ") 
            VALUES (" . implode(', ', $placeholders) . ")";
    
    db_query($sql, $values);
    return $pdo->lastInsertId();
}


function db_update($table, $data, $where, $where_params = []) {
    $set_parts = [];
    $values = [];
    
    foreach ($data as $column => $value) {
        $set_parts[] = "$column = ?";
        $values[] = $value;
    }
    
    $values = array_merge($values, $where_params);
    
    $sql = "UPDATE $table SET " . implode(', ', $set_parts) . " WHERE $where";
    return db_query($sql, $values)->rowCount();
}


function db_delete($table, $where, $params = []) {
    $sql = "DELETE FROM $table WHERE $where";
    return db_query($sql, $params)->rowCount();
}


function table_exists($table_name) {
    global $pdo;
    
    try {
        $result = $pdo->query("SELECT 1 FROM $table_name LIMIT 1");
        return $result !== false;
    } catch (PDOException $e) {
        return false;
    }
}


function create_table_if_not_exists($table_name, $sql) {
    if (!table_exists($table_name)) {
        global $pdo;
        $pdo->exec($sql);
        app_log("Table créée: $table_name", LOG_INFO);
    }
}


function db_begin_transaction() {
    global $pdo;
    return $pdo->beginTransaction();
}


function db_commit() {
    global $pdo;
    return $pdo->commit();
}

function db_rollback() {
    global $pdo;
    return $pdo->rollBack();
}


function db_escape($value) {
    global $pdo;
    
    if (is_null($value)) {
        return 'NULL';
    }
    
    if (is_numeric($value)) {
        return $value;
    }
    
    return $pdo->quote($value);
}


function db_date($timestamp = null) {
    if ($timestamp === null) {
        $timestamp = time();
    }
    
    return date('Y-m-d H:i:s', $timestamp);
}


function init_database() {
    app_log("Initialisation de la base de données", LOG_INFO);
    
  
    create_table_if_not_exists('users', "
        CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(255) NOT NULL,
            phone VARCHAR(20),
            address TEXT,
            profile_picture VARCHAR(500),
            birthdate DATE,
            gender ENUM('male', 'female', 'other'),
            is_active BOOLEAN DEFAULT TRUE,
            is_admin BOOLEAN DEFAULT FALSE,
            last_login DATETIME,
            reset_token VARCHAR(100),
            reset_expires DATETIME,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_is_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    
   
    create_table_if_not_exists('restaurants', "
        CREATE TABLE restaurants (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            address TEXT NOT NULL,
            phone VARCHAR(20),
            email VARCHAR(255),
            image VARCHAR(500),
            rating DECIMAL(3,2) DEFAULT 0.00,
            delivery_time VARCHAR(50) DEFAULT '30-45 min',
            min_order DECIMAL(10,2) DEFAULT 0.00,
            delivery_fee DECIMAL(10,2) DEFAULT 2.50,
            is_active BOOLEAN DEFAULT TRUE,
            opening_hours TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_is_active (is_active),
            INDEX idx_rating (rating DESC)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    
   
    create_table_if_not_exists('categories', "
        CREATE TABLE categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL UNIQUE,
            description TEXT,
            image VARCHAR(500),
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_is_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    

    create_table_if_not_exists('dishes', "
        CREATE TABLE dishes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            restaurant_id INT NOT NULL,
            category_id INT,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL,
            image VARCHAR(500),
            rating DECIMAL(3,2) DEFAULT 0.00,
            is_available BOOLEAN DEFAULT TRUE,
            is_vegetarian BOOLEAN DEFAULT FALSE,
            is_spicy BOOLEAN DEFAULT FALSE,
            calories INT,
            preparation_time INT DEFAULT 15,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
            INDEX idx_restaurant (restaurant_id),
            INDEX idx_category (category_id),
            INDEX idx_is_available (is_available),
            INDEX idx_rating (rating DESC)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    
 
    create_table_if_not_exists('orders', "
        CREATE TABLE orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_number VARCHAR(50) NOT NULL UNIQUE,
            user_id INT NOT NULL,
            restaurant_id INT NOT NULL,
            delivery_address TEXT NOT NULL,
            customer_name VARCHAR(255) NOT NULL,
            customer_phone VARCHAR(20) NOT NULL,
            customer_email VARCHAR(255),
            total_amount DECIMAL(10,2) NOT NULL,
            delivery_fee DECIMAL(10,2) DEFAULT 0.00,
            tax_amount DECIMAL(10,2) DEFAULT 0.00,
            final_amount DECIMAL(10,2) NOT NULL,
            payment_method ENUM('cash', 'card', 'online') DEFAULT 'cash',
            payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
            status ENUM('pending', 'confirmed', 'preparing', 'ready', 'on_delivery', 'delivered', 'cancelled') DEFAULT 'pending',
            notes TEXT,
            estimated_delivery DATETIME,
            delivered_at DATETIME,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
            INDEX idx_user (user_id),
            INDEX idx_restaurant (restaurant_id),
            INDEX idx_status (status),
            INDEX idx_order_number (order_number),
            INDEX idx_created_at (created_at DESC)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    
    create_table_if_not_exists('order_items', "
        CREATE TABLE order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            dish_id INT NOT NULL,
            dish_name VARCHAR(255) NOT NULL,
            quantity INT NOT NULL DEFAULT 1,
            unit_price DECIMAL(10,2) NOT NULL,
            total_price DECIMAL(10,2) NOT NULL,
            special_instructions TEXT,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (dish_id) REFERENCES dishes(id) ON DELETE SET NULL,
            INDEX idx_order (order_id),
            INDEX idx_dish (dish_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    
    app_log("Base de données initialisée avec succès", LOG_INFO);
}


if (APP_ENV === 'development') {
    init_database();
}


function clean_input($data) {
    if (is_array($data)) {
        return array_map('clean_input', $data);
    }
    
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}


function generate_token($length = 32) {
    return bin2hex(random_bytes($length));
}


function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}


function password_strength($password) {
    $score = 0;
    
    
    if (strlen($password) >= 8) $score++;
    
    
    if (preg_match('/[a-z]/', $password)) $score++;
    
  
    if (preg_match('/[A-Z]/', $password)) $score++;
    
  
    if (preg_match('/[0-9]/', $password)) $score++;
    

    if (preg_match('/[^a-zA-Z0-9]/', $password)) $score++;
    
    return $score;
}


function format_price($price, $currency = 'DT') {
    return number_format($price, 3, ',', ' ') . ' ' . $currency;
}


function format_date($date, $format = 'd/m/Y H:i') {
    if (empty($date)) return '';
    
    $timestamp = strtotime($date);
    return date($format, $timestamp);
}


function time_ago($date) {
    $timestamp = strtotime($date);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return "À l'instant";
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return "Il y a $mins minute" . ($mins > 1 ? 's' : '');
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return "Il y a $hours heure" . ($hours > 1 ? 's' : '');
    } elseif ($diff < 2592000) {
        $days = floor($diff / 86400);
        return "Il y a $days jour" . ($days > 1 ? 's' : '');
    } elseif ($diff < 31536000) {
        $months = floor($diff / 2592000);
        return "Il y a $months mois";
    } else {
        $years = floor($diff / 31536000);
        return "Il y a $years an" . ($years > 1 ? 's' : '');
    }
}


function check_directories() {
    $directories = [
        LOG_PATH,
        UPLOAD_PATH,
        UPLOAD_PATH . '/profile_pictures',
        UPLOAD_PATH . '/restaurants',
        UPLOAD_PATH . '/dishes',
        BASE_PATH . '/cache'
    ];
    
    foreach ($directories as $directory) {
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }
}


check_directories();

app_log("Application initialisée", LOG_INFO);