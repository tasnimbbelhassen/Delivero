<?php
session_start();
require_once '../admin/includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Vérifier si la table reviews existe
$tableExists = $pdo->query("SHOW TABLES LIKE 'reviews'")->fetch();

if (!$tableExists) {
    // Table non existante, afficher un message
    $error = "La fonctionnalité d'avis n'est pas encore disponible.";
} else {
    // Récupérer les avis de l'utilisateur
    $stmt = $pdo->prepare("
        SELECT r.*, d.name as dish_name, d.image as dish_image,
               res.name as restaurant_name, o.order_number
        FROM reviews r
        LEFT JOIN dishes d ON r.dish_id = d.id
        LEFT JOIN restaurants res ON d.restaurant_id = res.id
        LEFT JOIN orders o ON r.order_id = o.id
        WHERE r.user_id = ?
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $reviews = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes avis - Delivero</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        .star-rating {
            color: #ffc107;
            font-size: 1.2rem;
        }
        .review-card {
            transition: transform 0.2s;
        }
        .review-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container mt-4">
       
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 mb-1">
                            <i class="bi bi-star"></i> Mes avis
                        </h1>
                        <p class="text-muted mb-0">Historique de tous vos avis</p>
                    </div>
                    <a href="orders.php" class="btn btn-primary">
                        <i class="bi bi-list-check"></i> Mes commandes
                    </a>
                </div>
            </div>
        </div>

        <?php if (isset($error)): ?>
            
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-exclamation-triangle display-1 text-warning"></i>
                    <h3 class="mt-3">Fonctionnalité en cours de développement</h3>
                    <p class="text-muted mb-4"><?= $error ?></p>
                    <a href="home.php" class="btn btn-primary">Retour à l'accueil</a>
                </div>
            </div>
        <?php elseif (empty($reviews)): ?>
            
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-star display-1 text-muted"></i>
                    <h3 class="mt-3">Aucun avis</h3>
                    <p class="text-muted mb-4">Vous n'avez pas encore donné d'avis sur vos commandes</p>
                    <a href="orders.php" class="btn btn-primary">
                        <i class="bi bi-list-check"></i> Voir mes commandes
                    </a>
                </div>
            </div>
        <?php else: ?>
           
            <div class="row">
                <?php foreach ($reviews as $review): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card shadow-sm review-card h-100">
                            <div class="card-body">
                                
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <?php if (!empty($review['dish_name'])): ?>
                                            <h5 class="card-title mb-1">
                                                <?= htmlspecialchars($review['dish_name']) ?>
                                            </h5>
                                            <small class="text-muted">
                                                <i class="bi bi-shop"></i> <?= htmlspecialchars($review['restaurant_name'] ?? '') ?>
                                            </small>
                                        <?php elseif (!empty($review['restaurant_name'])): ?>
                                            <h5 class="card-title mb-1">
                                                <?= htmlspecialchars($review['restaurant_name']) ?>
                                            </h5>
                                            <small class="text-muted">
                                                <i class="bi bi-shop"></i> Restaurant
                                            </small>
                                        <?php else: ?>
                                            <h5 class="card-title mb-1">Avis général</h5>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted d-block">
                                            <?= date('d/m/Y', strtotime($review['created_at'])) ?>
                                        </small>
                                        <?php if (!empty($review['order_number'])): ?>
                                            <small class="text-muted">
                                                #<?= htmlspecialchars($review['order_number']) ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                
                                <div class="mb-3">
                                    <div class="star-rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="bi bi-star<?= $i <= $review['rating'] ? '-fill' : '' ?>"></i>
                                        <?php endfor; ?>
                                        <span class="ms-2"><?= $review['rating'] ?>/5</span>
                                    </div>
                                </div>

                               
                                <?php if (!empty($review['comment'])): ?>
                                    <div class="mb-3">
                                        <p class="card-text"><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                                    </div>
                                <?php endif; ?>

                               
                                <?php if (!empty($review['dish_image'])): ?>
                                    <div class="mb-3">
                                        <img src="<?= htmlspecialchars($review['dish_image']) ?>" 
                                             alt="<?= htmlspecialchars($review['dish_name'] ?? '') ?>"
                                             class="img-fluid rounded" style="max-height: 150px;">
                                    </div>
                                <?php endif; ?>

                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="bi bi-clock"></i> 
                                        <?= date('H:i', strtotime($review['created_at'])) ?>
                                    </small>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary edit-review"
                                                data-id="<?= $review['id'] ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-outline-danger delete-review"
                                                data-id="<?= $review['id'] ?>">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>

    document.querySelectorAll('.edit-review').forEach(button => {
        button.addEventListener('click', function() {
            const reviewId = this.getAttribute('data-id');
            
            window.location.href = 'edit_review.php?id=' + reviewId;
        });
    });

 
    document.querySelectorAll('.delete-review').forEach(button => {
        button.addEventListener('click', function() {
            const reviewId = this.getAttribute('data-id');
            
            if (confirm('Supprimer cet avis ? Cette action est irréversible.')) {
                fetch('ajax/delete_review.php?id=' + reviewId)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Erreur : ' + data.message);
                        }
                    })
                    .catch(error => {
                        alert('Erreur technique : ' + error.message);
                    });
            }
        });
    });
    </script>
</body>
</html>