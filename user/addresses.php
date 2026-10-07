<?php
session_start();
require_once '../admin/includes/config.php';

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


$stmt = $pdo->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
$stmt->execute([$user_id]);
$addresses = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes adresses - Delivero</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container mt-4">
       
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 mb-1">
                            <i class="bi bi-geo-alt"></i> Mes adresses
                        </h1>
                        <p class="text-muted mb-0">Gérez vos adresses de livraison</p>
                    </div>
                    <a href="add_address.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Ajouter une adresse
                    </a>
                </div>
            </div>
        </div>
        
        
        <div class="row">
            <?php if ($addresses): ?>
                <?php foreach ($addresses as $address): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h5 class="card-title mb-0">
                                        <?= htmlspecialchars($address['title']) ?>
                                        <?php if ($address['is_default']): ?>
                                            <span class="badge bg-primary ms-2">Par défaut</span>
                                        <?php endif; ?>
                                    </h5>
                                    <div class="btn-group btn-group-sm">
                                        <a href="edit_address.php?id=<?= $address['id'] ?>" 
                                           class="btn btn-outline-primary" title="Modifier">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button class="btn btn-outline-danger delete-address" 
                                                data-id="<?= $address['id'] ?>" 
                                                title="Supprimer">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                
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
                                    <p class="text-muted small">
                                        <?= htmlspecialchars($address['city']) ?> - 
                                        <?= htmlspecialchars($address['postal_code']) ?>
                                    </p>
                                    <?php if ($address['instructions']): ?>
                                        <div class="alert alert-info small mb-0">
                                            <i class="bi bi-info-circle"></i>
                                            <?= nl2br(htmlspecialchars($address['instructions'])) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if (!$address['is_default']): ?>
                                    <form method="POST" action="ajax/set_default_address.php" class="d-inline">
                                        <input type="hidden" name="address_id" value="<?= $address['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-primary">
                                            Définir par défaut
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-geo-alt display-1 text-muted"></i>
                            <h3 class="mt-3">Aucune adresse</h3>
                            <p class="text-muted mb-4">Ajoutez une adresse pour vos livraisons</p>
                            <a href="add_address.php" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Ajouter une adresse
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/user.js"></script>
    
    <script>
    // Suppression adresse
    /*
    document.querySelectorAll('.delete-address').forEach(button => {
        button.addEventListener('click', function() {
            const addressId = this.dataset.id;
            
            if (confirm('Supprimer cette adresse ?')) {
                fetch('ajax/delete_address.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ address_id: addressId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Erreur lors de la suppression');
                    }
                });
            }
        });
    });
*/
    document.querySelectorAll('.delete-address').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const addressId = this.dataset.id;
        console.log('Tentative suppression adresse ID:', addressId);
        
        if (confirm('Supprimer cette adresse ?')) {
            fetch('ajax/delete_address.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ address_id: addressId })
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Delete response:', data);
                if (data.success) {
                    
                    const card = this.closest('.col-md-6');
                    if (card) {
                        card.style.transition = 'opacity 0.3s';
                        card.style.opacity = '0';
                        setTimeout(() => {
                            location.reload();
                        }, 300);
                    } else {
                        location.reload();
                    }
                } else {
                    alert(data.message || 'Erreur lors de la suppression');
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                alert('Erreur réseau lors de la suppression');
            });
        }
    });
});

        
        document.querySelectorAll('form[action="ajax/set_default_address.php"]').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const addressId = formData.get('address_id');
                
                fetch(this.action, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Erreur lors de la mise à jour');
                    }
                });
            });
        });

            console.log('Test API delete');
            fetch('ajax/delete_address.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ address_id: 1 }) 
            })
            .then(response => response.json())
            .then(data => console.log('API Response:', data))
            .catch(error => console.error('API Error:', error));
    </script>
</body>
</html>