<?php
echo "<h2>Test de chemins</h2>";

echo "<h3>Current directory info:</h3>";
echo "__DIR__: " . __DIR__ . "<br>";
echo "getcwd(): " . getcwd() . "<br>";

echo "<h3>Trying different paths:</h3>";

$paths = [
    '1. ../admin/includes/config.php' => __DIR__ . '/../admin/includes/config.php',
    '2. ../../admin/includes/config.php' => __DIR__ . '/../../admin/includes/config.php',
    '3. C:/wamp64/www/mini-projet/admin/includes/config.php' => 'C:/wamp64/www/mini-projet/admin/includes/config.php',
    '4. /../admin/includes/config.php' => __DIR__ . '/../admin/includes/config.php',
    '5. Relative from current dir' => realpath('../admin/includes/config.php') ?: 'Not found',
    '6. Absolute from server root' => $_SERVER['DOCUMENT_ROOT'] . '/mini-projet/admin/includes/config.php',
];

foreach ($paths as $label => $path) {
    echo "<strong>$label:</strong> ";
    if (file_exists($path)) {
        echo "<span style='color:green'>EXISTS ✓</span> ($path)";
    } else {
        echo "<span style='color:red'>NOT FOUND ✗</span> ($path)";
    }
    echo "<br>";
}

echo "<h3>Server info:</h3>";
echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "SCRIPT_FILENAME: " . $_SERVER['SCRIPT_FILENAME'] . "<br>";
?>