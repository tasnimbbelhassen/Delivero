<?php
session_start();
require_once '../admin/includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];


$address_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("
    SELECT * FROM user_addresses 
    WHERE id = ? AND user_id = ?
");
$stmt->execute([$address_id, $user_id]);
$address = $stmt->fetch();

if (!$address) {
    header('Location: addresses.php');
    exit();
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $address_text = trim($_POST['address']); 
    $city = trim($_POST['city']);
    $postal_code = trim($_POST['postal_code']);
    $instructions = trim($_POST['instructions'] ?? ''); 
    $is_default = isset($_POST['is_default']) ? 1 : 0;
    

    if (empty($title)) {
        $errors[] = "Le titre est requis";
    }
    
    if (empty($full_name)) {
        $errors[] = "Le nom complet est requis";
    }
    
    if (empty($address_text)) {
        $errors[] = "L'adresse est requise";
    }
    
    if (empty($city)) {
        $errors[] = "La ville est requise";
    }
    
    if (!empty($phone) && !preg_match('/^[0-9+\s\-()]{8,20}$/', $phone)) {
        $errors[] = "Numéro de téléphone invalide";
    }
    
    if (empty($errors)) {
        
        if ($is_default && !$address['is_default']) {
            $stmt = $pdo->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = ?");
            $stmt->execute([$user_id]);
        }
        
        
        $stmt = $pdo->prepare("
            UPDATE user_addresses 
            SET title = ?, full_name = ?, phone = ?, address = ?, city = ?, 
                postal_code = ?, instructions = ?, is_default = ?, updated_at = NOW()
            WHERE id = ? AND user_id = ?
        ");
        
        if ($stmt->execute([
            $title, $full_name, $phone, $address_text, $city, 
            $postal_code, $instructions, $is_default, $address_id, $user_id
        ])) {
            $success = true;
            $message = "Adresse mise à jour avec succès";
            
            
            $address = array_merge($address, [
                'title' => $title,
                'full_name' => $full_name,
                'phone' => $phone,
                'address' => $address_text,
                'city' => $city,
                'postal_code' => $postal_code,
                'instructions' => $instructions,
                'is_default' => $is_default
            ]);
        } else {
            $errors[] = "Erreur lors de la mise à jour de l'adresse";
        }
    }
}


$stmt = $pdo->prepare("
    SELECT COUNT(*) as count FROM user_addresses 
    WHERE user_id = ? AND id != ?
");
$stmt->execute([$user_id, $address_id]);
$result = $stmt->fetch();
$other_addresses = $result ? $result['count'] : 0;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier l'adresse - Delivero</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
    .address-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 1.5rem;
    }
    .form-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
        z-index: 10;
    }
    .form-group-icon {
        position: relative;
    }
    .form-group-icon input,
    .form-group-icon textarea {
        padding-left: 45px;
    }
    .btn-geolocation {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        z-index: 10;
    }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
               
                <div class="address-header mb-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h3 mb-1">
                                <i class="bi bi-geo-alt"></i> Modifier l'adresse
                            </h1>
                            <p class="mb-0 opacity-75">
                                Mettez à jour les informations de cette adresse
                            </p>
                        </div>
                        <div class="badge bg-white text-primary p-2">
                            <i class="bi bi-clock"></i> Modifiée le <?= date('d/m/Y', strtotime($address['updated_at'])) ?>
                        </div>
                    </div>
                </div>

                
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="bi bi-check-circle me-2"></i>
                        <?= $message ?>
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
                    <div class="card-body">
                        <form method="POST" id="edit-address-form">
                        
                            <div class="mb-4 form-group-icon">
                                <i class="bi bi-card-heading form-icon"></i>
                                <label class="form-label">Titre de l'adresse *</label>
                                <input type="text" class="form-control" name="title"
                                       value="<?= htmlspecialchars($address['title']) ?>" 
                                       placeholder="Ex: Maison, Bureau, Parents..." required>
                                <div class="form-text">
                                    Donnez un nom facile à reconnaître pour cette adresse
                                </div>
                            </div>
                            
                          
                            <div class="mb-4 form-group-icon">
                                <i class="bi bi-person form-icon"></i>
                                <label class="form-label">Nom complet *</label>
                                <input type="text" class="form-control" name="full_name"
                                    value="<?= htmlspecialchars($address['full_name']) ?>" 
                                    required>
                            </div>

                 
                            <div class="mb-4 form-group-icon">
                                <i class="bi bi-geo-alt form-icon"></i>
                                <label class="form-label">Adresse *</label>
                                <textarea class="form-control" name="address" rows="3" required
                                        placeholder="Numéro, rue, résidence, étage..."><?= 
                                        htmlspecialchars($address['address']) ?></textarea>
                                <button type="button" class="btn btn-outline-info btn-sm btn-geolocation" 
                                        onclick="getCurrentLocation()">
                                    <i class="bi bi-geo-alt"></i> Ma position
                                </button>
                            </div>
                            
                          
                            <div class="row mb-4">
                                <div class="col-md-6 form-group-icon">
                                    <i class="bi bi-building form-icon"></i>
                                    <label class="form-label">Ville *</label>
                                    <input type="text" class="form-control" name="city"
                                           value="<?= htmlspecialchars($address['city']) ?>" 
                                           required list="cities-list">
                                    <datalist id="cities-list">
                                        <?php
                                        $cities = ['Tunis', 'Ariana', 'Ben Arous', 'Manouba', 'Nabeul', 'Zaghouan', 'Bizerte', 
                                                  'Béja', 'Jendouba', 'Kef', 'Siliana', 'Kairouan', 'Kasserine', 'Sidi Bouzid',
                                                  'Sousse', 'Monastir', 'Mahdia', 'Sfax', 'Gabès', 'Médenine', 'Tataouine', 'Tozeur', 'Kébili'];
                                        foreach ($cities as $city): ?>
                                            <option value="<?= $city ?>">
                                        <?php endforeach; ?>
                                    </datalist>
                                </div>
                                
                                <div class="col-md-6 form-group-icon">
                                    <i class="bi bi-postcard form-icon"></i>
                                    <label class="form-label">Code postal</label>
                                    <input type="text" class="form-control" name="postal_code"
                                           value="<?= htmlspecialchars($address['postal_code'] ?? '') ?>"
                                           placeholder="Ex: 1000">
                                </div>
                            </div>
                            
                            <div class="mb-4 form-group-icon">
                                <i class="bi bi-telephone form-icon"></i>
                                <label class="form-label">Téléphone</label>
                                <input type="tel" class="form-control" name="phone"
                                       value="<?= htmlspecialchars($address['phone'] ?? '') ?>" 
                                       placeholder="+216 12 345 678">
                                <div class="form-text">
                                    Numéro pour le livreur (optionnel)
                                </div>
                            </div>
                           
                            <div class="mb-4 form-group-icon">
                                <i class="bi bi-chat-text form-icon"></i>
                                <label class="form-label">Instructions pour le livreur</label>
                                <textarea class="form-control" name="instructions" rows="2"
                                        placeholder="Code de la porte, étage, etc."><?= 
                                        htmlspecialchars($address['instructions'] ?? '') ?></textarea>
                            </div>

                        
                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_default"
                                           id="is_default" <?= $address['is_default'] ? 'checked' : '' ?>
                                           <?= $other_addresses == 0 ? 'disabled' : '' ?>>
                                    <label class="form-check-label" for="is_default">
                                        Définir comme adresse par défaut
                                    </label>
                                    <div class="form-text">
                                        <?php if ($other_addresses == 0): ?>
                                            <span class="text-warning">
                                                <i class="bi bi-info-circle"></i> 
                                                C'est votre seule adresse, elle est automatiquement définie comme défaut
                                            </span>
                                        <?php else: ?>
                                            Cette adresse sera utilisée par défaut pour vos livraisons
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                           
                            <div class="card bg-light mb-4">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="bi bi-info-circle"></i> Informations
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <small class="text-muted d-block">
                                                <i class="bi bi-calendar"></i> Créée le: 
                                                <?= date('d/m/Y', strtotime($address['created_at'])) ?>
                                            </small>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted d-block">
                                                <i class="bi bi-clock-history"></i> Dernière modification: 
                                                <?= date('d/m/Y H:i', strtotime($address['updated_at'])) ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        
                            <div class="d-flex justify-content-between">
                                <div>
                                    <a href="addresses.php" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-left"></i> Retour aux adresses
                                    </a>
                                    <a href="?delete=<?= $address['id'] ?>" 
                                       class="btn btn-outline-danger"
                                       onclick="return confirmDeleteAddress()"
                                       <?= $address['is_default'] ? 'disabled' : '' ?>>
                                        <i class="bi bi-trash"></i> Supprimer
                                    </a>
                                </div>
                                
                                <div class="btn-group">
                                    <a href="addresses.php" class="btn btn-secondary">
                                        Annuler
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save"></i> Enregistrer les modifications
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
               
                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="bi bi-map"></i> Prévisualisation de l'adresse
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h6 class="text-primary">
                                    <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($address['title']) ?>
                                    <?php if ($address['is_default']): ?>
                                        <span class="badge bg-success ms-2">Par défaut</span>
                                    <?php endif; ?>
                                </h6>

                                <div class="mb-3">
                                    <p class="mb-1">
                                        <strong><?= htmlspecialchars($address['full_name']) ?></strong>
                                    </p>
                                    <p class="text-muted mb-1">
                                        <i class="bi bi-telephone"></i> <?= htmlspecialchars($address['phone']) ?>
                                    </p>
                                    <p class="mb-1">
                                        <?= nl2br(htmlspecialchars($address['address'])) ?>
                                    </p>
                                    <p class="mb-0">
                                        <strong><?= htmlspecialchars($address['city']) ?></strong>
                                        <?php if ($address['postal_code']): ?>
                                            - <?= htmlspecialchars($address['postal_code']) ?>
                                        <?php endif; ?>
                                    </p>
                                    <?php if ($address['instructions']): ?>
                                        <p class="mt-2 mb-0 text-info small">
                                            <i class="bi bi-info-circle"></i> <?= htmlspecialchars($address['instructions']) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="alert alert-info small">
                                    <i class="bi bi-info-circle"></i>
                                    Cette adresse sera visible par nos livreurs pour vos commandes.
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                               
                                <div class="border rounded bg-light d-flex align-items-center justify-content-center" 
                                     style="height: 150px;">
                                    <div class="text-center">
                                        <i class="bi bi-map display-4 text-muted"></i>
                                        <p class="mt-2 small">Carte de localisation</p>
                                    </div>
                                </div>
                                <div class="text-center mt-2">
                                    <button class="btn btn-sm btn-outline-primary" onclick="openInMaps()">
                                        <i class="bi bi-geo-alt"></i> Voir sur Google Maps
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>

    function confirmDeleteAddress() {
        return confirm('Êtes-vous sûr de vouloir supprimer cette adresse ? Cette action est irréversible.');
    }
    

    function getCurrentLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const latitude = position.coords.latitude;
                    const longitude = position.coords.longitude;
                    
                   
                    showNotification('info', 'Position détectée. Remplissage simulé...');
                    
                    
                    setTimeout(() => {
                        const form = document.getElementById('edit-address-form');
                        if (form) {
                            form.querySelector('[name="address"]').value = '123 Rue de la Paix';
                            form.querySelector('[name="city"]').value = 'Tunis';
                            form.querySelector('[name="postal_code"]').value = '1000';
                            showNotification('success', 'Adresse remplie automatiquement');
                        }
                    }, 1000);
                },
                function(error) {
                    console.error('Erreur géolocalisation:', error);
                    showNotification('error', 'Impossible d\'accéder à votre position. Vérifiez les permissions.');
                }
            );
        } else {
            showNotification('error', 'La géolocalisation n\'est pas supportée par votre navigateur');
        }
    }
    
  
    function openInMaps() {
        const form = document.getElementById('edit-address-form');
        const address = encodeURIComponent(
            form.querySelector('[name="address"]').value + ', ' +
            form.querySelector('[name="city"]').value
        );
        window.open(`https://www.google.com/maps/search/?api=1&query=${address}`, '_blank');
    }
    
    
    const editAddressForm = document.getElementById('edit-address-form');
    
    editAddressForm?.addEventListener('submit', function(e) {
        let isValid = true;
        
       
        const requiredFields = this.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });
      
        const phoneField = this.querySelector('[name="phone"]');
        if (phoneField.value && !phoneField.value.match(/^[0-9+\s\-()]{8,20}$/)) {
            phoneField.classList.add('is-invalid');
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
            showNotification('error', 'Veuillez corriger les erreurs dans le formulaire');
        }
    });
    
   
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('delete')) {
        const deleteConfirmed = confirm('Supprimer cette adresse ? Cette action est irréversible.');
        if (deleteConfirmed) {
            fetch('ajax/delete_address.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ address_id: <?= $address['id'] ?> })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'addresses.php?deleted=1';
                }
            });
        }
    }


    function showNotification(type, message) {

    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show`;
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.style.minWidth = '300px';
    
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
    `;
    
    document.body.appendChild(notification);
    
 
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}
    </script>
</body>
</html>