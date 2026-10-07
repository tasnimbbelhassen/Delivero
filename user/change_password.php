<?php
session_start();
require_once '../admin/includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
 
    if (empty($current_password)) {
        $errors[] = "Le mot de passe actuel est requis";
    }
    
    if (empty($new_password)) {
        $errors[] = "Le nouveau mot de passe est requis";
    } elseif (strlen($new_password) < 6) {
        $errors[] = "Le nouveau mot de passe doit faire au moins 6 caractères";
    }
    
    if ($new_password !== $confirm_password) {
        $errors[] = "Les mots de passe ne correspondent pas";
    }
    
    if (empty($errors)) {
        
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if (!$user || !password_verify($current_password, $user['password'])) {
            $errors[] = "Le mot de passe actuel est incorrect";
        } else {
           
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            
            if ($stmt->execute([$hashed_password, $user_id])) {
                $success = true;
                header('Refresh: 3; url=profile.php');
            } else {
                $errors[] = "Erreur lors du changement de mot de passe";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Changer mot de passe - Delivero</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-6">
               
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="bi bi-check-circle me-2"></i>
                        Mot de passe changé avec succès ! Redirection...
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
                            <i class="bi bi-lock"></i> Changer mon mot de passe
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label class="form-label">Mot de passe actuel *</label>
                                <input type="password" class="form-control" name="current_password" required>
                                <div class="invalid-feedback">
                                    Veuillez entrer votre mot de passe actuel
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Nouveau mot de passe *</label>
                                <input type="password" class="form-control" name="new_password" required
                                       minlength="6">
                                <div class="form-text">
                                    Minimum 6 caractères
                                </div>
                                <div class="invalid-feedback">
                                    Le mot de passe doit faire au moins 6 caractères
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Confirmer le nouveau mot de passe *</label>
                                <input type="password" class="form-control" name="confirm_password" required>
                                <div class="invalid-feedback">
                                    Les mots de passe doivent correspondre
                                </div>
                            </div>
                            
                           
                            <div class="mb-4">
                                <label class="form-label">Force du mot de passe</label>
                                <div class="progress" style="height: 5px;">
                                    <div class="progress-bar" id="password-strength" 
                                         style="width: 0%; transition: width 0.3s;"></div>
                                </div>
                                <small class="text-muted" id="password-feedback"></small>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="profile.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left"></i> Retour
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-key"></i> Changer le mot de passe
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
           
                <div class="card shadow-sm mt-4">
                    <div class="card-body">
                        <h6 class="card-title mb-3">
                            <i class="bi bi-shield-check"></i> Conseils de sécurité
                        </h6>
                        <ul class="small text-muted mb-0">
                            <li>Utilisez au moins 8 caractères</li>
                            <li>Combinez lettres, chiffres et caractères spéciaux</li>
                            <li>Évitez les mots de passe faciles à deviner</li>
                            <li>Ne réutilisez pas vos anciens mots de passe</li>
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
                const newPassword = form.querySelector('input[name="new_password"]');
                const confirmPassword = form.querySelector('input[name="confirm_password"]');
                
                if (newPassword.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity('Les mots de passe ne correspondent pas');
                } else {
                    confirmPassword.setCustomValidity('');
                }
                
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                
                form.classList.add('was-validated');
            }, false);
        });
    })();
    
   
    const passwordInput = document.querySelector('input[name="new_password"]');
    const strengthBar = document.getElementById('password-strength');
    const feedback = document.getElementById('password-feedback');
    
    passwordInput?.addEventListener('input', function() {
        const password = this.value;
        let strength = 0;
        let feedbackText = '';
  
        if (password.length >= 8) strength += 25;
        
      
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 25;
        
        if (/\d/.test(password)) strength += 25;
        
      
        if (/[^A-Za-z0-9]/.test(password)) strength += 25;
        
       
        strengthBar.style.width = strength + '%';
        
   
        if (strength < 25) {
            strengthBar.className = 'progress-bar bg-danger';
            feedbackText = 'Très faible';
        } else if (strength < 50) {
            strengthBar.className = 'progress-bar bg-warning';
            feedbackText = 'Faible';
        } else if (strength < 75) {
            strengthBar.className = 'progress-bar bg-info';
            feedbackText = 'Moyen';
        } else {
            strengthBar.className = 'progress-bar bg-success';
            feedbackText = 'Fort';
        }
        
        feedback.textContent = feedbackText;
    });
    </script>
</body>
</html>