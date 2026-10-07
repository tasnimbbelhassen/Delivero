<?php
session_start();
require_once __DIR__ . '/../admin/includes/config.php';


$stats = [];
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    try {
        
        $order_stats = $pdo->prepare("
            SELECT 
                COUNT(*) as total_orders,
                COALESCE(SUM(total), 0) as total_spent,
                COALESCE(AVG(total), 0) as avg_order_value
            FROM orders 
            WHERE user_id = ?
        ");
        $order_stats->execute([$user_id]);
        $order_stats_result = $order_stats->fetch();
        
        $stats['user'] = $order_stats_result ?: [
            'total_orders' => 0,
            'total_spent' => 0,
            'avg_order_value' => 0
        ];
        
    } catch (PDOException $e) {
       
        $stats['user'] = [
            'total_orders' => 0,
            'total_spent' => 0,
            'avg_order_value' => 0
        ];
    }
}

try {
    $global_stats_stmt = $pdo->query("
        SELECT 
            COALESCE((SELECT COUNT(*) FROM users WHERE is_active = 1), 0) as total_users,
            COALESCE((SELECT COUNT(*) FROM restaurants WHERE is_active = 1), 0) as total_restaurants,
            COALESCE((SELECT COUNT(*) FROM dishes WHERE is_available = 1), 0) as total_dishes,
            COALESCE((SELECT COUNT(*) FROM orders WHERE status = 'completed'), 0) as total_orders
    ");
    $global_stats = $global_stats_stmt->fetch();
} catch (PDOException $e) {
    
    $global_stats = [
        'total_users' => 10000,
        'total_restaurants' => 150,
        'total_dishes' => 2500,
        'total_orders' => 50000
    ];
}


if (!$global_stats) {
    $global_stats = [
        'total_users' => 10000,
        'total_restaurants' => 150,
        'total_dishes' => 2500,
        'total_orders' => 50000
    ];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>À propos - Delivero</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
    .hero-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 6rem 0;
        margin-bottom: 4rem;
    }
    .stat-card {
        border-radius: 15px;
        transition: transform 0.3s;
    }
    .stat-card:hover {
        transform: translateY(-10px);
    }
    .team-member {
        text-align: center;
        padding: 2rem;
        border-radius: 15px;
        transition: all 0.3s;
    }
    .team-member:hover {
        background-color: #f8f9fa;
        transform: translateY(-5px);
    }
    .timeline {
        position: relative;
        padding: 20px 0;
    }
    .timeline::before {
        content: '';
        position: absolute;
        left: 50%;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #0d6efd;
        transform: translateX(-50%);
    }
    .timeline-item {
        position: relative;
        margin-bottom: 40px;
    }
    .timeline-item:nth-child(odd) .timeline-content {
        margin-left: auto;
        text-align: right;
    }
    .timeline-item:nth-child(even) .timeline-content {
        margin-right: auto;
        text-align: left;
    }
    .timeline-dot {
        position: absolute;
        left: 50%;
        top: 0;
        width: 20px;
        height: 20px;
        background: #0d6efd;
        border-radius: 50%;
        transform: translateX(-50%);
        z-index: 1;
    }
    </style>
</head>
<body>
 
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="index.php">
                <i class="bi bi-truck"></i> Delivero
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="bi bi-house"></i> Accueil
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="about.php">
                            <i class="bi bi-info-circle"></i> À propos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="restaurants.php">
                            <i class="bi bi-shop"></i> Restaurants
                        </a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="user/home.php">
                                <i class="bi bi-person"></i> Mon compte
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i class="bi bi-box-arrow-in-right"></i> Connexion
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-primary ms-2" href="register.php">
                                <i class="bi bi-person-plus"></i> S'inscrire
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <section class="hero-section">
        <div class="container text-center">
            <h1 class="display-4 fw-bold mb-4">
                <i class="bi bi-truck"></i> Delivero
            </h1>
            <p class="lead mb-4">
                Votre partenaire de livraison de repas préféré en Tunisie
            </p>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <p>
                        Depuis 2020, nous connectons les meilleurs restaurants de Tunisie avec des milliers 
                        de clients satisfaits. Notre mission : rendre la livraison de nourriture rapide, 
                        fiable et délicieuse.
                    </p>
                </div>
            </div>
        </div>
    </section>

  
    <section class="container mb-5">
        <div class="row g-4">
            <div class="col-md-3 col-6">
                <div class="card stat-card shadow-sm text-center border-0 bg-primary text-white">
                    <div class="card-body py-4">
                        <i class="bi bi-people display-4 mb-3"></i>
                        <h3 class="fw-bold"><?= number_format($global_stats['total_users']) ?>+</h3>
                        <p class="mb-0">Clients satisfaits</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card stat-card shadow-sm text-center border-0 bg-success text-white">
                    <div class="card-body py-4">
                        <i class="bi bi-shop display-4 mb-3"></i>
                        <h3 class="fw-bold"><?= number_format($global_stats['total_restaurants']) ?>+</h3>
                        <p class="mb-0">Restaurants partenaires</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card stat-card shadow-sm text-center border-0 bg-warning text-dark">
                    <div class="card-body py-4">
                        <i class="bi bi-egg-fried display-4 mb-3"></i>
                        <h3 class="fw-bold"><?= number_format($global_stats['total_dishes']) ?>+</h3>
                        <p class="mb-0">Plats disponibles</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card stat-card shadow-sm text-center border-0 bg-info text-white">
                    <div class="card-body py-4">
                        <i class="bi bi-basket display-4 mb-3"></i>
                        <h3 class="fw-bold"><?= number_format($global_stats['total_orders']) ?>+</h3>
                        <p class="mb-0">Commandes livrées</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    
    <section class="container mb-5">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4">
                <h2 class="fw-bold mb-4">
                    <i class="bi bi-book text-primary"></i> Notre histoire
                </h2>
                <p class="lead mb-4">
                    Delivero est né d'une simple idée : faciliter l'accès à la nourriture de qualité 
                    partout en Tunisie.
                </p>
                <p>
                    En 2020, deux amis passionnés de technologie et de gastronomie ont remarqué 
                    que malgré la richesse culinaire de la Tunisie, il était difficile pour beaucoup 
                    de commander leurs plats préférés rapidement et efficacement.
                </p>
                <p>
                    Aujourd'hui, Delivero est devenu la plateforme de référence pour la livraison 
                    de repas en Tunisie, avec des centaines de restaurants partenaires et une équipe 
                    dédiée à votre satisfaction.
                </p>
            </div>
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <img src="https://images.unsplash.com/photo-1554118811-1e0d58224f24?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" 
                         class="card-img-top" 
                         alt="Équipe Delivero"
                         style="height: 300px; object-fit: cover;">
                    <div class="card-body">
                        <p class="card-text text-center text-muted">
                            Notre équipe travaille chaque jour pour améliorer votre expérience
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

   
    <section class="bg-light py-5 mb-5">
        <div class="container">
            <h2 class="text-center fw-bold mb-5">
                <i class="bi bi-bullseye text-primary"></i> Notre mission
            </h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="text-primary mb-3">
                                <i class="bi bi-lightning display-4"></i>
                            </div>
                            <h4 class="card-title">Rapidité</h4>
                            <p class="card-text">
                                Livraison en moins de 45 minutes grâce à notre réseau optimisé 
                                de livreurs et notre technologie de pointe.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="text-success mb-3">
                                <i class="bi bi-shield-check display-4"></i>
                            </div>
                            <h4 class="card-title">Qualité</h4>
                            <p class="card-text">
                                Nous sélectionnons rigoureusement nos restaurants partenaires 
                                pour garantir la fraîcheur et la qualité de vos repas.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="text-warning mb-3">
                                <i class="bi bi-heart display-4"></i>
                            </div>
                            <h4 class="card-title">Satisfaction</h4>
                            <p class="card-text">
                                Notre équipe de support est disponible 24/7 pour répondre 
                                à vos questions et résoudre vos problèmes rapidement.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

  
    <section class="container mb-5">
        <h2 class="text-center fw-bold mb-5">
            <i class="bi bi-clock-history text-primary"></i> Notre parcours
        </h2>
        
        <div class="timeline">
            <div class="timeline-item">
                <div class="timeline-dot"></div>
                <div class="timeline-content col-md-5">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Janvier 2020</h5>
                            <p class="card-text">
                                Lancement de Delivero avec 10 restaurants partenaires à Tunis
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="timeline-item">
                <div class="timeline-dot"></div>
                <div class="timeline-content col-md-5">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Juillet 2020</h5>
                            <p class="card-text">
                                Déploiement dans 5 nouvelles villes tunisiennes
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="timeline-item">
                <div class="timeline-dot"></div>
                <div class="timeline-content col-md-5">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Décembre 2020</h5>
                            <p class="card-text">
                                Lancement de l'application mobile sur iOS et Android
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="timeline-item">
                <div class="timeline-dot"></div>
                <div class="timeline-content col-md-5">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title text-primary">2023</h5>
                            <p class="card-text">
                                +50 000 clients satisfaits à travers toute la Tunisie
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

   
    <section class="container mb-5">
        <h2 class="text-center fw-bold mb-5">
            <i class="bi bi-people text-primary"></i> Notre équipe
        </h2>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="team-member">
                    <div class="mb-3">
                        <img src="https://ui-avatars.com/api/?name=Mohamed+Ali&background=0d6efd&color=fff&size=150" 
                             class="rounded-circle shadow" 
                             alt="Mohamed Ali">
                    </div>
                    <h5>Mohamed Ali</h5>
                    <p class="text-muted">CEO & Co-fondateur</p>
                    <p class="small">
                        10 ans d'expérience dans le e-commerce et la logistique
                    </p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="team-member">
                    <div class="mb-3">
                        <img src="https://ui-avatars.com/api/?name=Sarah+Ben&background=dc3545&color=fff&size=150" 
                             class="rounded-circle shadow" 
                             alt="Sarah Ben">
                    </div>
                    <h5>Sarah Ben</h5>
                    <p class="text-muted">CTO & Co-fondatrice</p>
                    <p class="small">
                        Experte en développement web et applications mobiles
                    </p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="team-member">
                    <div class="mb-3">
                        <img src="https://ui-avatars.com/api/?name=Khalil+Trabelsi&background=198754&color=fff&size=150" 
                             class="rounded-circle shadow" 
                             alt="Khalil Trabelsi">
                    </div>
                    <h5>Khalil Trabelsi</h5>
                    <p class="text-muted">Directeur des Opérations</p>
                    <p class="small">
                        Gestion des partenariats et de la logistique
                    </p>
                </div>
            </div>
        </div>
    </section>


    <section class="bg-primary text-white py-5 mb-5">
        <div class="container">
            <h2 class="text-center fw-bold mb-5">
                <i class="bi bi-star"></i> Nos valeurs
            </h2>
            
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="bi bi-shield-check display-6"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4>Transparence</h4>
                            <p>
                                Nous croyons en une communication claire et honnête avec 
                                nos clients, restaurants partenaires et livreurs.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="bi bi-arrow-repeat display-6"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4>Innovation</h4>
                            <p>
                                Nous investissons constamment dans la technologie pour 
                                améliorer votre expérience de commande et de livraison.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="bi bi-people display-6"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4>Communauté</h4>
                            <p>
                                Nous soutenons l'économie locale en collaborant avec 
                                des restaurants indépendants et en créant des emplois.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="bi bi-heart display-6"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4>Passion</h4>
                            <p>
                                Nous sommes passionnés par la nourriture et nous nous 
                                engageons à vous offrir la meilleure expérience culinaire.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

  
    <section class="container mb-5">
        <div class="card shadow-sm border-0">
            <div class="card-body text-center py-5">
                <h2 class="fw-bold mb-4">
                    <i class="bi bi-chat-heart text-primary"></i> Faites partie de l'aventure
                </h2>
                <p class="lead mb-4">
                    Que vous soyez restaurant, livreur ou client, rejoignez la communauté Delivero
                </p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="register.php" class="btn btn-primary btn-lg">
                        <i class="bi bi-person-plus"></i> Créer un compte
                    </a>
                    <a href="contact.php" class="btn btn-outline-primary btn-lg">
                        <i class="bi bi-envelope"></i> Devenir partenaire
                    </a>
                    <a href="careers.php" class="btn btn-outline-success btn-lg">
                        <i class="bi bi-briefcase"></i> Nous rejoindre
                    </a>
                </div>
            </div>
        </div>
    </section>


    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5 class="fw-bold">
                        <i class="bi bi-truck"></i> Delivero
                    </h5>
                    <p>
                        Votre partenaire de livraison de repas en Tunisie.
                        Rapide, fiable et délicieux.
                    </p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-white">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <a href="#" class="text-white">
                            <i class="bi bi-instagram"></i>
                        </a>
                        <a href="#" class="text-white">
                            <i class="bi bi-twitter"></i>
                        </a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <h5 class="fw-bold">Liens rapides</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-white text-decoration-none">Accueil</a></li>
                        <li><a href="about.php" class="text-white text-decoration-none">À propos</a></li>
                        <li><a href="restaurants.php" class="text-white text-decoration-none">Restaurants</a></li>
                        <li><a href="contact.php" class="text-white text-decoration-none">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h5 class="fw-bold">Contact</h5>
                    <ul class="list-unstyled">
                        <li><i class="bi bi-geo-alt"></i> Tunis, Tunisie</li>
                        <li><i class="bi bi-telephone"></i> +216 71 234 567</li>
                        <li><i class="bi bi-envelope"></i> contact@delivero.tn</li>
                    </ul>
                </div>
            </div>
            <hr class="bg-white">
            <div class="text-center">
                <p class="mb-0">
                    &copy; 2024 Delivero. Tous droits réservés.
                </p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function animateCounter(element, target) {
        let current = 0;
        const increment = target / 100;
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                element.textContent = target.toLocaleString() + '+';
                clearInterval(timer);
            } else {
                element.textContent = Math.floor(current).toLocaleString() + '+';
            }
        }, 20);
    }
    

    function animateStatsOnScroll() {
        const statCards = document.querySelectorAll('.stat-card h3');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const target = parseInt(entry.target.textContent);
                    animateCounter(entry.target, target);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });
        
        statCards.forEach(card => observer.observe(card));
    }
    

    document.addEventListener('DOMContentLoaded', animateStatsOnScroll);
    

    const currentPage = window.location.pathname;
    document.querySelectorAll('.nav-link').forEach(link => {
        if (link.getAttribute('href') === currentPage.split('/').pop()) {
            link.classList.add('active');
        }
    });
    </script>
</body>
</html>