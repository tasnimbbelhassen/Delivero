<?php
session_start();
require_once __DIR__ . '/../admin/includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

$pdo->exec("
    CREATE TABLE IF NOT EXISTS user_addresses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(100) NOT NULL DEFAULT 'Maison',
        full_name VARCHAR(100) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        address TEXT NOT NULL,
        city VARCHAR(100) NOT NULL,
        postal_code VARCHAR(20) NOT NULL,
        instructions TEXT,
        is_default BOOLEAN DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $postal_code = trim($_POST['postal_code']);
    $instructions = trim($_POST['instructions']);
    $is_default = isset($_POST['is_default']) ? 1 : 0;
    
   
    if (empty($title)) $errors[] = "Le titre est requis";
    if (empty($full_name)) $errors[] = "Le nom complet est requis";
    if (empty($phone)) $errors[] = "Le téléphone est requis";
    if (empty($address)) $errors[] = "L'adresse est requise";
    if (empty($city)) $errors[] = "La ville est requise";
    if (empty($postal_code)) $errors[] = "Le code postal est requis";
    
    if (empty($errors)) {
        try {
          
            if ($is_default) {
                $pdo->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = ?")
                    ->execute([$user_id]);
            }
            
           
            $stmt = $pdo->prepare("
                INSERT INTO user_addresses 
                (user_id, title, full_name, phone, address, city, postal_code, instructions, is_default)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            if ($stmt->execute([
                $user_id, $title, $full_name, $phone, $address, 
                $city, $postal_code, $instructions, $is_default
            ])) {
                $success = true;
                header('Refresh: 2; url=addresses.php');
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une adresse - Delivero</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="bi bi-check-circle me-2"></i>
                        Adresse ajoutée avec succès ! Redirection...
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <h5><i class="bi bi-exclamation-triangle me-2"></i> Erreurs</h5>
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                

                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="bi bi-plus-circle"></i> Ajouter une adresse
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="needs-validation" novalidate>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Titre *</label>
                                    <input type="text" class="form-control" name="title" 
                                           placeholder="Ex: Maison, Bureau, etc." required>
                                    <div class="invalid-feedback">
                                        Veuillez donner un titre à cette adresse
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nom complet *</label>
                                    <input type="text" class="form-control" name="full_name" required>
                                    <div class="invalid-feedback">
                                        Veuillez entrer le nom complet
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Téléphone *</label>
                                    <input type="tel" class="form-control" name="phone" required>
                                    <div class="invalid-feedback">
                                        Veuillez entrer un numéro de téléphone
                                    </div>
                                </div>
                                
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Adresse *</label>
                                    <textarea class="form-control" name="address" rows="3" required
                                              placeholder="Numéro, rue, bâtiment..."></textarea>
                                    <div class="invalid-feedback">
                                        Veuillez entrer l'adresse complète
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ville *</label>
                                    <input type="text" class="form-control" name="city" required>
                                    <div class="invalid-feedback">
                                        Veuillez entrer la ville
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Code postal *</label>
                                    <input type="text" class="form-control" name="postal_code" required>
                                    <div class="invalid-feedback">
                                        Veuillez entrer le code postal
                                    </div>
                                </div>
                                
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Instructions pour le livreur</label>
                                    <textarea class="form-control" name="instructions" rows="2"
                                              placeholder="Code de la porte, étage, etc."></textarea>
                                </div>
                                
                                <div class="col-md-12 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               name="is_default" id="is_default">
                                        <label class="form-check-label" for="is_default">
                                            Définir comme adresse par défaut
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-4">
                                <a href="addresses.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left"></i> Annuler
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Enregistrer l'adresse
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                
                <div class="card shadow-sm mt-4">
                    <div class="card-body">
                        <h6 class="card-title mb-3">
                            <i class="bi bi-info-circle"></i> Conseils
                        </h6>
                        <ul class="small text-muted mb-0">
                            <li>Assurez-vous que l'adresse est précise et complète</li>
                            <li>Incluez le numéro d'étage et le code de l'immeuble si nécessaire</li>
                            <li>Une adresse par défaut sera utilisée automatiquement</li>
                            <li>Vous pouvez ajouter plusieurs adresses</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    (function() {
        'use strict';
        const forms = document.querySelectorAll('.needs-validation');
        
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    })();
    </script>
</body>
</html>