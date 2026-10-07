<?php
session_start();
require_once '../admin/includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$faq_categories = [
    'commandes' => [
        'title' => 'Commandes et livraison',
        'icon' => 'bi bi-cart'
    ],
    'paiement' => [
        'title' => 'Paiement et facturation',
        'icon' => 'bi bi-credit-card'
    ],
    'compte' => [
        'title' => 'Compte et profil',
        'icon' => 'bi bi-person'
    ],
    'restaurants' => [
        'title' => 'Restaurants et plats',
        'icon' => 'bi bi-shop'
    ],
    'problemes' => [
        'title' => 'Problèmes techniques',
        'icon' => 'bi bi-tools'
    ]
];

$faqs = [
    'commandes' => [
        [
            'question' => 'Comment passer une commande ?',
            'answer' => '1. Sélectionnez un restaurant<br>
                        2. Choisissez vos plats<br>
                        3. Ajoutez-les au panier<br>
                        4. Validez votre commande<br>
                        5. Choisissez l\'adresse de livraison<br>
                        6. Effectuez le paiement'
        ],
        [
            'question' => 'Quels sont les délais de livraison ?',
            'answer' => 'Les délais varient selon le restaurant et votre localisation :
                        <ul>
                            <li>Fast-food : 20-30 minutes</li>
                            <li>Restaurants : 30-45 minutes</li>
                            <li>Repas spéciaux : 45-60 minutes</li>
                        </ul>'
        ],
        [
            'question' => 'Puis-je modifier ou annuler ma commande ?',
            'answer' => 'Oui, tant que le restaurant n\'a pas commencé la préparation.
                        Rendez-vous dans "Mes commandes" et cliquez sur "Annuler".'
        ]
    ],
    'paiement' => [
        [
            'question' => 'Quels modes de paiement acceptez-vous ?',
            'answer' => 'Nous acceptons :
                        <ul>
                            <li>Carte bancaire (Visa, MasterCard)</li>
                            <li>Espèces à la livraison</li>
                            <li>Cartes cadeaux Delivero</li>
                        </ul>'
        ],
        [
            'question' => 'Comment obtenir une facture ?',
            'answer' => 'Une facture est automatiquement générée pour chaque commande.
                        Vous pouvez la télécharger depuis la page de détails de votre commande.'
        ]
    ],
    'compte' => [
        [
            'question' => 'Comment modifier mes informations personnelles ?',
            'answer' => 'Rendez-vous dans "Mon profil" > "Modifier le profil".'
        ],
        [
            'question' => 'Comment réinitialiser mon mot de passe ?',
            'answer' => 'Sur la page de connexion, cliquez sur "Mot de passe oublié"
                        et suivez les instructions.'
        ]
    ]
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aide et FAQ - Delivero</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link href="assets/css/user.css" rel="stylesheet">
    <style>
    .faq-category {
        border-radius: 15px;
        transition: transform 0.3s;
    }
    .faq-category:hover {
        transform: translateY(-5px);
    }
    .accordion-button:not(.collapsed) {
        background-color: rgba(13, 110, 253, 0.1);
        color: #0d6efd;
    }
    .search-box {
        max-width: 600px;
        margin: 0 auto;
    }
    .help-card {
        border-left: 4px solid #0d6efd;
    }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container mt-4">
        
        <div class="row mb-5">
            <div class="col-12 text-center">
                <h1 class="display-6 fw-bold mb-3">
                    <i class="bi bi-question-circle text-primary"></i> Comment pouvons-nous vous aider ?
                </h1>
                <p class="lead text-muted mb-4">
                    Trouvez rapidement des réponses à vos questions
                </p>
                
              
                <div class="search-box">
                    <div class="input-group input-group-lg shadow-sm">
                        <span class="input-group-text bg-white">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" class="form-control" 
                               placeholder="Rechercher une question..." id="help-search">
                        <button class="btn btn-primary" type="button">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

      
        <div class="row mb-5">
            <div class="col-12">
                <h2 class="h4 mb-4">
                    <i class="bi bi-grid"></i> Parcourir par catégorie
                </h2>
            </div>
            
            <?php foreach ($faq_categories as $key => $category): ?>
                <div class="col-md-4 col-lg-3 mb-3">
                    <a href="#<?= $key ?>" class="text-decoration-none">
                        <div class="card h-100 faq-category shadow-sm text-center">
                            <div class="card-body">
                                <div class="text-primary mb-3">
                                    <i class="<?= $category['icon'] ?> display-4"></i>
                                </div>
                                <h5 class="card-title"><?= $category['title'] ?></h5>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

      
        <div class="row mb-5">
            <div class="col-12">
                <h2 class="h4 mb-4">
                    <i class="bi bi-chat-quote"></i> Questions fréquentes
                </h2>
                
                <?php foreach ($faqs as $category => $questions): ?>
                    <div class="mb-4" id="<?= $category ?>">
                        <h3 class="h5 mb-3">
                            <i class="<?= $faq_categories[$category]['icon'] ?>"></i>
                            <?= $faq_categories[$category]['title'] ?>
                        </h3>
                        
                        <div class="accordion mb-4" id="accordion-<?= $category ?>">
                            <?php foreach ($questions as $index => $faq): ?>
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" 
                                                data-bs-toggle="collapse" 
                                                data-bs-target="#collapse-<?= $category . $index ?>">
                                            <?= htmlspecialchars($faq['question']) ?>
                                        </button>
                                    </h2>
                                    <div id="collapse-<?= $category . $index ?>" 
                                         class="accordion-collapse collapse" 
                                         data-bs-parent="#accordion-<?= $category ?>">
                                        <div class="accordion-body">
                                            <?= $faq['answer'] ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

       
        <div class="row mb-5">
            <div class="col-12">
                <h2 class="h4 mb-4">
                    <i class="bi bi-book"></i> Guides utiles
                </h2>
            </div>
            
            <div class="col-md-6 mb-3">
                <div class="card h-100 help-card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-phone text-primary"></i> Utiliser l'application mobile
                        </h5>
                        <p class="card-text text-muted">
                            Téléchargez notre application pour une expérience optimale :
                        </p>
                        <div class="d-flex gap-2">
                            <a href="#" class="btn btn-outline-dark">
                                <i class="bi bi-apple"></i> App Store
                            </a>
                            <a href="#" class="btn btn-outline-dark">
                                <i class="bi bi-google-play"></i> Play Store
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <div class="card h-100 help-card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-percent text-success"></i> Utiliser un code promo
                        </h5>
                        <p class="card-text text-muted">
                            Apprenez à utiliser vos codes promo pour bénéficier de réductions.
                        </p>
                        <a href="#" class="btn btn-outline-primary">Voir le guide</a>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-headset display-1 text-primary mb-3"></i>
                        <h3 class="mb-3">Vous ne trouvez pas de réponse ?</h3>
                        <p class="text-muted mb-4">
                            Notre équipe de support est là pour vous aider
                        </p>
                        <div class="d-flex justify-content-center gap-3 flex-wrap">
                            <a href="contact.php" class="btn btn-primary btn-lg">
                                <i class="bi bi-envelope"></i> Nous contacter
                            </a>
                            <a href="tel:+21671234567" class="btn btn-outline-primary btn-lg">
                                <i class="bi bi-telephone"></i> Appeler le support
                            </a>
                        </div>
                        <div class="mt-4">
                            <p class="text-muted small mb-1">
                                <i class="bi bi-clock"></i> Support disponible 24h/24, 7j/7
                            </p>
                            <p class="text-muted small">
                                <i class="bi bi-chat"></i> Temps de réponse moyen : 15 minutes
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
   
    const searchInput = document.getElementById('help-search');
    const allQuestions = document.querySelectorAll('.accordion-button');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            
            if (searchTerm.length < 2) {
              
                allQuestions.forEach(question => {
                    const accordionItem = question.closest('.accordion-item');
                    accordionItem.style.display = 'block';
                });
                return;
            }
            
         
            allQuestions.forEach(question => {
                const questionText = question.textContent.toLowerCase();
                const answerText = question.nextElementSibling?.querySelector('.accordion-body')?.textContent.toLowerCase() || '';
                const accordionItem = question.closest('.accordion-item');
                
                if (questionText.includes(searchTerm) || answerText.includes(searchTerm)) {
                    accordionItem.style.display = 'block';
                    
                   
                    const collapseId = question.dataset.bsTarget;
                    const collapseElement = document.querySelector(collapseId);
                    if (collapseElement && !collapseElement.classList.contains('show')) {
                        new bootstrap.Collapse(collapseElement, { toggle: true });
                    }
                } else {
                    accordionItem.style.display = 'none';
                }
            });
        });
    }
    
   
    document.querySelectorAll('.faq-category').forEach(card => {
        card.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.closest('a').hash;
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                targetElement.scrollIntoView({ behavior: 'smooth' });
                
              
                const accordion = targetElement.querySelector('.accordion');
                if (accordion) {
                    const collapses = accordion.querySelectorAll('.accordion-collapse');
                    collapses.forEach(collapse => {
                        new bootstrap.Collapse(collapse, { show: true });
                    });
                }
            }
        });
    });
    
    
    function trackHelpful(questionId, isHelpful) {
        fetch('ajax/track_helpful.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                question_id: questionId,
                helpful: isHelpful
            })
        });
    }
    
   
    document.querySelectorAll('.helpful-buttons').forEach(container => {
        const yesBtn = container.querySelector('.helpful-yes');
        const noBtn = container.querySelector('.helpful-no');
        const questionId = container.dataset.questionId;
        
        yesBtn?.addEventListener('click', function() {
            this.classList.add('btn-success');
            noBtn?.classList.remove('btn-danger');
            trackHelpful(questionId, true);
            showNotification('success', 'Merci pour votre retour !');
        });
        
        noBtn?.addEventListener('click', function() {
            this.classList.add('btn-danger');
            yesBtn?.classList.remove('btn-success');
            trackHelpful(questionId, false);
           
            document.getElementById('feedback-form')?.classList.remove('d-none');
        });
    });
    </script>
</body>
</html>