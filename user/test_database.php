<?php
require_once 'admin/includes/config.php';

echo "<h2>Test de Base de Données</h2>";

try {
    
    echo "<p>Connexion à la base de données... ";
    $pdo->query('SELECT 1');
    echo "OK ✓</p>";
    
    echo "<p>Test table 'dishes'... ";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM dishes");
    $result = $stmt->fetch();
    echo "OK ({$result['count']} plats) ✓</p>";
    
    
    $stmt = $pdo->query("SELECT id, name, price, restaurant_id FROM dishes LIMIT 5");
    $dishes = $stmt->fetchAll();
    
    echo "<h3>5 premiers plats:</h3>";
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Nom</th><th>Prix</th><th>Restaurant ID</th></tr>";
    foreach ($dishes as $dish) {
        echo "<tr>";
        echo "<td>{$dish['id']}</td>";
        echo "<td>{$dish['name']}</td>";
        echo "<td>{$dish['price']} DT</td>";
        echo "<td>{$dish['restaurant_id']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p style='color: red'>Erreur: " . $e->getMessage() . "</p>";
}
?>