<?php
session_start();


$pdo = new PDO("mysql:host=localhost;dbname=delivero_admin;charset=utf8mb4", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "<h2>Réinitialisation du mot de passe admin</h2>";

$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Mot de passe: <strong>$password</strong><br>";
echo "Hash généré: <code>$hash</code><br>";


if (password_verify($password, $hash)) {
    echo "<span style='color:green'>✓ Hash valide</span><br>";
} else {
    echo "<span style='color:red'>✗ Hash invalide</span><br>";
}


try {
    
    $tables = $pdo->query("SHOW TABLES LIKE 'users'")->fetch();
    if ($tables) {
        $table = 'users';
    } else {
        $tables = $pdo->query("SHOW TABLES LIKE 'utilisateurs'")->fetch();
        $table = 'utilisateurs';
    }
    
    echo "Table trouvée: <strong>$table</strong><br>";
    
    
    $stmt = $pdo->prepare("UPDATE $table SET password = ? WHERE username = 'admin'");
    if ($stmt->execute([$hash])) {
        echo "<p style='color:green; font-size: 1.2em;'>✓ Mot de passe admin mis à jour avec succès !</p>";
        echo "<p>Vous pouvez maintenant vous connecter avec :</p>";
        echo "<p><strong>Nom d'utilisateur:</strong> admin</p>";
        echo "<p><strong>Mot de passe:</strong> admin123</p>";
        echo "<p><a href='login.php' style='color:blue;'>Aller à la page de connexion</a></p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>Erreur: " . $e->getMessage() . "</p>";
}
?>