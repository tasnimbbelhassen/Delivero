<?php
session_start();
require_once '../admin/includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject']);
    $category = $_POST['category'];
    $message = trim($_POST['message']);
    $priority = $_POST['priority'] ?? 'normal';
    $order_id = $_POST['order_id'] ?? null;
    

    if (empty($subject)) {
        $errors[] = "Le sujet est requis";
    }
    
    if (empty($category)) {
        $errors[] = "La catégorie est requise";
    }
    
    if (empty($message) || strlen($message) < 10) {
        $errors[] = "Le message doit faire au moins 10 caractères";
    }
    
    if (empty($errors)) {
        
        $ticket_number = 'TICKET-' . date('YmdHis') . '-' . rand(100, 999);
        
        $stmt = $pdo->prepare("
            INSERT INTO support_tickets 
            (ticket_number, user_id, subject, category, message, priority, order_id, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'open')
        ");
        
        if ($stmt->execute([$ticket_number, $user_id, $subject, $category, $message, $priority, $order_id])) {
            $ticket_id = $pdo->lastInsertId();
            $success = true;
            unset($_POST);
        } else {
            $errors[] = "Erreur lors de l'envoi du message";
        }
    }
}


$stmt = $pdo->prepare("
    SELECT id, order_number, created_at 
    FROM orders 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 10
");
$stmt->execute([$user_id]);
$recent_orders = $stmt->fetchAll();


$stmt = $pdo->prepare("
    SELECT * FROM support_tickets 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 5
");
$stmt->execute([$user_id]);
$previous_tickets = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contactez-nous - Delivero</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
              
                <div class="text-center mb-5">
                    <h1 class="display-6 fw-bold mb-3">
                        <i class="bi bi-headset text-primary"></i> Contactez-nous
                    </h1>
                    <p class="lead text-muted">
                        Notre équipe est là pour vous aider
                    </p>
                </div>

       
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="bi bi-check-circle me-2"></i>
                        Votre message a été envoyé avec succès. Notre équipe vous répondra dans les plus brefs délais.
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

                <div class="row">
               
                    <div class="col-lg-8">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-envelope"></i> Formulaire de contact
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" id="contact-form">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Votre nom *</label>
                                            <input type="text" class="form-control" 
                                                   value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" 
                                                   readonly>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Votre email *</label>
                                            <input type="email" class="form-control" 
                                                   value="<?= htmlspecialchars($user['email'] ?? '') ?>" 
                                                   readonly>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Sujet *</label>
                                        <input type="text" class="form-control" name="subject" 
                                               value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>" 
                                               placeholder="Décrivez brièvement votre problème" required>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Catégorie *</label>
                                            <select class="form-select" name="category" required>
                                                <option value="">Choisir une catégorie</option>
                                                <option value="order" <?= ($_POST['category'] ?? '') == 'order' ? 'selected' : '' ?>>Problème de commande</option>
                                                <option value="payment" <?= ($_POST['category'] ?? '') == 'payment' ? 'selected' : '' ?>>Problème de paiement</option>
                                                <option value="delivery" <?= ($_POST['category'] ?? '') == 'delivery' ? 'selected' : '' ?>>Problème de livraison</option>
                                                <option value="food" <?= ($_POST['category'] ?? '') == 'food' ? 'selected' : '' ?>>Problème avec la nourriture</option>
                                                <option value="account" <?= ($_POST['category'] ?? '') == 'account' ? 'selected' : '' ?>>Problème de compte</option>
                                                <option value="technical" <?= ($_POST['category'] ?? '') == 'technical' ? 'selected' : '' ?>>Problème technique</option>
                                                <option value="other" <?= ($_POST['category'] ?? '') == 'other' ? 'selected' : '' ?>>Autre</option>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Priorité</label>
                                            <select class="form-select" name="priority">
                                                <option value="low">Basse</option>
                                                <option value="normal" selected>Normale</option>
                                                <option value="high">Haute</option>
                                                <option value="urgent">Urgente</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Lier à une commande (optionnel)</label>
                                        <select class="form-select" name="order_id">
                                            <option value="">Aucune commande</option>
                                            <?php foreach ($recent_orders as $order): ?>
                                                <option value="<?= $order['id'] ?>"
                                                        <?= ($_POST['order_id'] ?? '') == $order['id'] ? 'selected' : '' ?>>
                                                    #<?= $order['order_number'] ?> - 
                                                    <?= date('d/m/Y', strtotime($order['created_at'])) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Message *</label>
                                        <textarea class="form-control" name="message" rows="6" required
                                                  placeholder="Décrivez votre problème en détail..."><?= 
                                                  htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                                        <div class="form-text">
                                            Soyez le plus précis possible pour nous aider à résoudre votre problème rapidement.
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Pièces jointes (optionnel)</label>
                                        <input type="file" class="form-control" id="attachments" 
                                               accept=".jpg,.jpeg,.png,.pdf,.doc,.docx" multiple>
                                        <div class="form-text">
                                            Formats acceptés : JPG, PNG, PDF, DOC (max 5Mo par fichier)
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid mt-4">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="bi bi-send"></i> Envoyer le message
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                 
                    <div class="col-lg-4">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-info-circle"></i> Informations de contact
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-4">
                                    <h6 class="text-primary">
                                        <i class="bi bi-headset"></i> Support client
                                    </h6>
                                    <ul class="list-unstyled text-muted">
                                        <li class="mb-2">
                                            <i class="bi bi-telephone"></i> +216 71 234 567
                                        </li>
                                        <li class="mb-2">
                                            <i class="bi bi-whatsapp"></i> +216 23 456 789
                                        </li>
                                        <li>
                                            <i class="bi bi-envelope"></i> support@delivero.tn
                                        </li>
                                    </ul>
                                </div>
                                
                                <div class="mb-4">
                                    <h6 class="text-primary">
                                        <i class="bi bi-clock"></i> Horaires
                                    </h6>
                                    <ul class="list-unstyled text-muted">
                                        <li class="mb-1">Lun-Ven: 8h-22h</li>
                                        <li class="mb-1">Samedi: 9h-20h</li>
                                        <li>Dimanche: 10h-18h</li>
                                    </ul>
                                </div>
                                
                                <div>
                                    <h6 class="text-primary">
                                        <i class="bi bi-lightning"></i> Temps de réponse
                                    </h6>
                                    <div class="alert alert-info small mb-0">
                                        <i class="bi bi-info-circle"></i>
                                        Temps de réponse moyen : 
                                        <strong>15 minutes</strong> pendant les heures d'ouverture
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title mb-3">
                                    <i class="bi bi-chat-quote"></i> Questions fréquentes
                                </h6>
                                <div class="accordion accordion-flush" id="contact-faq">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" 
                                                    data-bs-toggle="collapse" 
                                                    data-bs-target="#faq1">
                                                Quel est le délai de réponse ?
                                            </button>
                                        </h2>
                                        <div id="faq1" class="accordion-collapse collapse" 
                                             data-bs-parent="#contact-faq">
                                            <div class="accordion-body small">
                                                Nous répondons généralement dans les 15 minutes pendant nos heures d'ouverture.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" 
                                                    data-bs-toggle="collapse" 
                                                    data-bs-target="#faq2">
                                                Puis-je joindre des photos ?
                                            </button>
                                        </h2>
                                        <div id="faq2" class="accordion-collapse collapse" 
                                             data-bs-parent="#contact-faq">
                                            <div class="accordion-body small">
                                                Oui, vous pouvez joindre des photos (JPG, PNG) pour nous aider à comprendre votre problème.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" 
                                                    data-bs-toggle="collapse" 
                                                    data-bs-target="#faq3">
                                                Comment suivre mon ticket ?
                                            </button>
                                        </h2>
                                        <div id="faq3" class="accordion-collapse collapse" 
                                             data-bs-parent="#contact-faq">
                                            <div class="accordion-body small">
                                                Vous recevrez un email avec un numéro de ticket pour suivre l'avancement.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
              
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="bi bi-ticket-detailed"></i> Suivi de vos tickets
                                </h5>
                                <a href="tickets.php" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-list-ul"></i> Voir tous les tickets
                                </a>
                            </div>
                            <div class="card-body">
                                <?php if ($previous_tickets): ?>
                                    <div class="row">
                                        <?php 
                                      
                                        $status_counts = [
                                            'open' => 0,
                                            'answered' => 0,
                                            'resolved' => 0,
                                            'closed' => 0
                                        ];
                                        
                                        foreach ($previous_tickets as $ticket) {
                                            if (isset($status_counts[$ticket['status']])) {
                                                $status_counts[$ticket['status']]++;
                                            }
                                        }
                                        ?>
                                        
                                        <div class="col-md-3 mb-3">
                                            <div class="card border-warning">
                                                <div class="card-body text-center">
                                                    <h2 class="text-warning"><?= $status_counts['open'] ?></h2>
                                                    <p class="text-muted mb-0">Tickets ouverts</p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-3 mb-3">
                                            <div class="card border-info">
                                                <div class="card-body text-center">
                                                    <h2 class="text-info"><?= $status_counts['answered'] ?></h2>
                                                    <p class="text-muted mb-0">En cours</p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-3 mb-3">
                                            <div class="card border-success">
                                                <div class="card-body text-center">
                                                    <h2 class="text-success"><?= $status_counts['resolved'] ?></h2>
                                                    <p class="text-muted mb-0">Résolus</p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-3 mb-3">
                                            <div class="card border-secondary">
                                                <div class="card-body text-center">
                                                    <h2 class="text-secondary"><?= $status_counts['closed'] ?></h2>
                                                    <p class="text-muted mb-0">Fermés</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="table-responsive mt-3">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>N° Ticket</th>
                                                    <th>Sujet</th>
                                                    <th>Date</th>
                                                    <th>Dernière mise à jour</th>
                                                    <th>Statut</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($previous_tickets as $ticket): 
                                                    
                                                    $stmt = $pdo->prepare("
                                                        SELECT created_at 
                                                        FROM support_replies 
                                                        WHERE ticket_id = ? 
                                                        ORDER BY created_at DESC 
                                                        LIMIT 1
                                                    ");
                                                    $stmt->execute([$ticket['id']]);
                                                    $last_reply = $stmt->fetch();
                                                ?>
                                                    <tr>
                                                        <td>
                                                            <strong><?= $ticket['ticket_number'] ?></strong>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <?php 
                                                                $category_icons = [
                                                                    'order' => 'bi-cart',
                                                                    'payment' => 'bi-credit-card',
                                                                    'delivery' => 'bi-truck',
                                                                    'food' => 'bi-egg-fried',
                                                                    'account' => 'bi-person',
                                                                    'technical' => 'bi-tools',
                                                                    'other' => 'bi-question-circle'
                                                                ];
                                                                ?>
                                                                <i class="bi <?= $category_icons[$ticket['category']] ?? 'bi-question-circle' ?> me-2 text-muted"></i>
                                                                <?= htmlspecialchars($ticket['subject']) ?>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <?= date('d/m/Y H:i', strtotime($ticket['created_at'])) ?>
                                                        </td>
                                                        <td>
                                                            <?php if ($last_reply): ?>
                                                                <?= date('d/m/Y H:i', strtotime($last_reply['created_at'])) ?>
                                                            <?php else: ?>
                                                                <span class="text-muted">Aucune réponse</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?php 
                                                            $status_badges = [
                                                                'open' => ['warning', 'bi-clock'],
                                                                'answered' => ['info', 'bi-chat-dots'],
                                                                'resolved' => ['success', 'bi-check-circle'],
                                                                'closed' => ['secondary', 'bi-archive']
                                                            ];
                                                            $status = $status_badges[$ticket['status']] ?? $status_badges['open'];
                                                            ?>
                                                            <span class="badge bg-<?= $status[0] ?>">
                                                                <i class="bi <?= $status[1] ?> me-1"></i>
                                                                <?= ucfirst($ticket['status']) ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="btn-group btn-group-sm">
                                                                <a href="view_ticket.php?id=<?= $ticket['id'] ?>" 
                                                                   class="btn btn-outline-primary">
                                                                    <i class="bi bi-eye"></i> Voir
                                                                </a>
                                                                <a href="reply_ticket.php?id=<?= $ticket['id'] ?>" 
                                                                   class="btn btn-outline-secondary">
                                                                    <i class="bi bi-reply"></i> Répondre
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-5">
                                        <i class="bi bi-inbox display-1 text-muted mb-3"></i>
                                        <h5 class="text-muted">Aucun ticket précédent</h5>
                                        <p class="text-muted mb-0">
                                            Vous n'avez pas encore contacté notre support.
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

  
    <div class="modal fade" id="helpModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-question-circle text-primary"></i> Aide pour remplir le formulaire
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h6>Conseils pour obtenir une réponse rapide :</h6>
                    <ul>
                        <li>Soyez précis dans la description de votre problème</li>
                        <li>Incluez toutes les informations pertinentes (numéro de commande, dates, etc.)</li>
                        <li>Joignez des captures d'écran si nécessaire</li>
                        <li>Choisissez la bonne catégorie pour que votre ticket soit traité par l'équipe appropriée</li>
                        <li>Utilisez le champ "Priorité" avec discernement</li>
                    </ul>
                    
                    <h6 class="mt-3">Priorités :</h6>
                    <ul>
                        <li><strong>Basse</strong> : Question générale sans urgence</li>
                        <li><strong>Normale</strong> : Problème standard</li>
                        <li><strong>Haute</strong> : Problème affectant votre expérience</li>
                        <li><strong>Urgente</strong> : Problème critique nécessitant une attention immédiate</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
  
    const contactForm = document.getElementById('contact-form');
    
    contactForm?.addEventListener('submit', function(e) {
        const message = this.querySelector('textarea[name="message"]');
        
        if (message.value.trim().length < 10) {
            e.preventDefault();
            message.classList.add('is-invalid');
            
            let feedback = message.nextElementSibling;
            if (!feedback.classList.contains('invalid-feedback')) {
                feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                message.parentNode.appendChild(feedback);
            }
            
            feedback.textContent = 'Le message doit faire au moins 10 caractères';
            message.focus();
        }
    });
  
    const attachmentsInput = document.getElementById('attachments');
    
    if (attachmentsInput) {
        attachmentsInput.addEventListener('change', function() {
            const files = Array.from(this.files);
            const maxSize = 5 * 1024 * 1024; // 5MB
            const allowedTypes = ['image/jpeg', 'image/png', 'application/pdf', 
                                  'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            
            files.forEach(file => {
                if (file.size > maxSize) {
                    showNotification('error', `Le fichier ${file.name} dépasse 5Mo`);
                    this.value = '';
                }
                
                if (!allowedTypes.includes(file.type)) {
                    showNotification('error', `Le format ${file.name} n'est pas accepté`);
                    this.value = '';
                }
            });
        });
    }
    
   
    const categorySelect = document.querySelector('select[name="category"]');
    const subjectInput = document.querySelector('input[name="subject"]');
    
    categorySelect?.addEventListener('change', function() {
        const category = this.value;
        const currentSubject = subjectInput.value.trim();
        
      
        if (currentSubject === '' || currentSubject.startsWith('[Problème de')) {
            const subjects = {
                'order': 'Problème de commande - ',
                'payment': 'Problème de paiement - ',
                'delivery': 'Problème de livraison - ',
                'food': 'Problème avec la nourriture - ',
                'account': 'Problème de compte - ',
                'technical': 'Problème technique - ',
                'other': 'Autre demande - '
            };
            
            if (subjects[category]) {
                subjectInput.value = subjects[category];
                subjectInput.focus();
            }
        }
    });
    

    function showNotification(type, message) {
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
        alert.style.zIndex = '1050';
        alert.innerHTML = `
            <i class="bi ${type === 'success' ? 'bi-check-circle' : 'bi-exclamation-circle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alert);
        
        
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    }
    

    document.querySelectorAll('#contact-form input, #contact-form textarea, #contact-form select').forEach(element => {
        element.addEventListener('input', function() {
            if (this.classList.contains('is-invalid')) {
                this.classList.remove('is-invalid');
                const feedback = this.nextElementSibling;
                if (feedback && feedback.classList.contains('invalid-feedback')) {
                    feedback.remove();
                }
            }
        });
    });
    

    contactForm?.addEventListener('submit', function(e) {
        const submitBtn = this.querySelector('button[type="submit"]');
        
        if (submitBtn.disabled) {
            e.preventDefault();
            return;
        }
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Envoi en cours...';
        
        setTimeout(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-send"></i> Envoyer le message';
        }, 5000);
    });
    
   
    const helpButton = document.createElement('button');
    helpButton.className = 'btn btn-primary rounded-circle position-fixed';
    helpButton.style.cssText = 'bottom: 20px; right: 20px; width: 60px; height: 60px; z-index: 1000;';
    helpButton.innerHTML = '<i class="bi bi-question-lg fs-4"></i>';
    helpButton.setAttribute('data-bs-toggle', 'modal');
    helpButton.setAttribute('data-bs-target', '#helpModal');
    helpButton.title = 'Besoin d\'aide ?';
    document.body.appendChild(helpButton);


    let draftTimeout;
    const saveDraft = () => {
        const formData = {
            subject: subjectInput?.value,
            category: categorySelect?.value,
            message: document.querySelector('textarea[name="message"]')?.value,
            priority: document.querySelector('select[name="priority"]')?.value,
            order_id: document.querySelector('select[name="order_id"]')?.value
        };
        
        localStorage.setItem('contact_draft', JSON.stringify(formData));
        console.log('Brouillon sauvegardé');
    };
    

    const loadDraft = () => {
        const draft = localStorage.getItem('contact_draft');
        if (draft) {
            try {
                const data = JSON.parse(draft);
                if (data.subject && !subjectInput.value) subjectInput.value = data.subject;
                if (data.category) categorySelect.value = data.category;
                if (data.message) document.querySelector('textarea[name="message"]').value = data.message;
                if (data.priority) document.querySelector('select[name="priority"]').value = data.priority;
                if (data.order_id) document.querySelector('select[name="order_id"]').value = data.order_id;
                

                if (confirm('Un brouillon a été trouvé. Voulez-vous le charger ?')) {
                    console.log('Brouillon chargé');
                } else {
                    localStorage.removeItem('contact_draft');
                }
            } catch (e) {
                console.error('Erreur lors du chargement du brouillon:', e);
            }
        }
    };
    

    document.querySelectorAll('#contact-form input, #contact-form textarea, #contact-form select').forEach(element => {
        element.addEventListener('input', () => {
            clearTimeout(draftTimeout);
            draftTimeout = setTimeout(saveDraft, 1000);
        });
    });
    

    window.addEventListener('load', loadDraft);
    

    contactForm?.addEventListener('submit', () => {
        localStorage.removeItem('contact_draft');
    });
    
    const prioritySelect = document.querySelector('select[name="priority"]');
    prioritySelect?.addEventListener('change', function() {
        if (this.value === 'urgent') {
            if (!confirm('La priorité "Urgente" est réservée aux problèmes critiques. Êtes-vous sûr de vouloir utiliser cette priorité ?')) {
                this.value = 'high';
            }
        }
    });
    </script>
</body>
</html>