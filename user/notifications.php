<?php
session_start();
require_once '../admin/includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];


$stmt = $pdo->prepare("
    SELECT n.*, 
           CASE 
               WHEN n.type = 'order' THEN 'bi bi-cart'
               WHEN n.type = 'promo' THEN 'bi bi-percent'
               WHEN n.type = 'system' THEN 'bi bi-bell'
               WHEN n.type = 'update' THEN 'bi bi-info-circle'
               ELSE 'bi bi-bell'
           END as icon_class,
           CASE 
               WHEN n.type = 'order' THEN 'text-primary'
               WHEN n.type = 'promo' THEN 'text-success'
               WHEN n.type = 'system' THEN 'text-warning'
               WHEN n.type = 'update' THEN 'text-info'
               ELSE 'text-secondary'
           END as text_class
    FROM notifications n
    WHERE n.user_id = ? OR n.user_id IS NULL
    ORDER BY n.created_at DESC
    LIMIT 50
");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll();


if ($notifications) {
    $unread_ids = array_column(array_filter($notifications, fn($n) => !$n['is_read']), 'id');
    if ($unread_ids) {
        $placeholders = str_repeat('?,', count($unread_ids) - 1) . '?';
        $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id IN ($placeholders)")
            ->execute($unread_ids);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Delivero</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    
    <style>
    .notification-item {
        border-left: 4px solid transparent;
        transition: all 0.3s;
    }
    .notification-item:hover {
        background-color: rgba(13, 110, 253, 0.05);
        transform: translateX(5px);
    }
    .notification-item.unread {
        border-left-color: #0d6efd;
        background-color: rgba(13, 110, 253, 0.1);
    }
    .notification-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .badge-notification {
        position: absolute;
        top: -5px;
        right: -5px;
        font-size: 0.7rem;
        padding: 2px 6px;
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
                            <i class="bi bi-bell"></i> Notifications
                        </h1>
                        <p class="text-muted mb-0">
                            Restez informé de vos commandes et promotions
                        </p>
                    </div>
                    <div class="btn-group">
                        <button class="btn btn-outline-primary" id="mark-all-read">
                            <i class="bi bi-check-all"></i> Tout marquer comme lu
                        </button>
                        <button class="btn btn-outline-danger" id="clear-all">
                            <i class="bi bi-trash"></i> Tout effacer
                        </button>
                    </div>
                </div>
            </div>
        </div>

       
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    <button class="btn btn-outline-primary active filter-btn" data-filter="all">
                        Toutes
                    </button>
                    <button class="btn btn-outline-primary filter-btn" data-filter="unread">
                        Non lues
                    </button>
                    <button class="btn btn-outline-primary filter-btn" data-filter="order">
                        <i class="bi bi-cart"></i> Commandes
                    </button>
                    <button class="btn btn-outline-primary filter-btn" data-filter="promo">
                        <i class="bi bi-percent"></i> Promotions
                    </button>
                    <button class="btn btn-outline-primary filter-btn" data-filter="system">
                        <i class="bi bi-bell"></i> Système
                    </button>
                </div>
            </div>
        </div>

        
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <?php if (empty($notifications)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-bell-slash display-1 text-muted"></i>
                        <h3 class="mt-3">Aucune notification</h3>
                        <p class="text-muted mb-0">
                            Vous n'avez pas encore de notifications
                        </p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($notifications as $notification): ?>
                            <div class="list-group-item notification-item 
                                 <?= !$notification['is_read'] ? 'unread' : '' ?> 
                                 notification-<?= $notification['type'] ?>"
                                 data-type="<?= $notification['type'] ?>"
                                 data-read="<?= $notification['is_read'] ? 'true' : 'false' ?>">
                                
                                <div class="d-flex align-items-start">
                                   
                                    <div class="position-relative me-3">
                                        <div class="notification-icon bg-light <?= $notification['text_class'] ?>">
                                            <i class="<?= $notification['icon_class'] ?>"></i>
                                        </div>
                                        <?php if (!$notification['is_read']): ?>
                                            <span class="badge bg-danger badge-notification"></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <h6 class="mb-1"><?= htmlspecialchars($notification['title']) ?></h6>
                                            <small class="text-muted">
                                                <?php
                                                $now = new DateTime();
                                                $created = new DateTime($notification['created_at']);
                                                $interval = $now->diff($created);
                                                
                                                if ($interval->y > 0) {
                                                    echo $interval->y . ' an' . ($interval->y > 1 ? 's' : '');
                                                } elseif ($interval->m > 0) {
                                                    echo $interval->m . ' mois';
                                                } elseif ($interval->d > 0) {
                                                    echo $interval->d . ' jour' . ($interval->d > 1 ? 's' : '');
                                                } elseif ($interval->h > 0) {
                                                    echo $interval->h . ' heure' . ($interval->h > 1 ? 's' : '');
                                                } elseif ($interval->i > 0) {
                                                    echo $interval->i . ' minute' . ($interval->i > 1 ? 's' : '');
                                                } else {
                                                    echo 'À l\'instant';
                                                }
                                                ?>
                                            </small>
                                        </div>
                                        
                                        <p class="mb-2 text-muted">
                                            <?= nl2br(htmlspecialchars($notification['message'])) ?>
                                        </p>
                                        
                                        
                                        <div class="mt-2">
                                            <?php if ($notification['action_url']): ?>
                                                <a href="<?= htmlspecialchars($notification['action_url']) ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <?= htmlspecialchars($notification['action_text'] ?: 'Voir') ?>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php if (!$notification['is_read']): ?>
                                                <button class="btn btn-sm btn-outline-secondary mark-read" 
                                                        data-id="<?= $notification['id'] ?>">
                                                    <i class="bi bi-check"></i> Marquer comme lu
                                                </button>
                                            <?php endif; ?>
                                            
                                            <button class="btn btn-sm btn-outline-danger delete-notification" 
                                                    data-id="<?= $notification['id'] ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        
        <?php if (!empty($notifications)): ?>
            <div class="card shadow-sm mt-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="bi bi-bar-chart"></i> Statistiques
                    </h5>
                    <div class="row">
                        <div class="col-md-3 text-center mb-3">
                            <div class="display-6 fw-bold text-primary">
                                <?= count($notifications) ?>
                            </div>
                            <small class="text-muted">Total notifications</small>
                        </div>
                        <div class="col-md-3 text-center mb-3">
                            <div class="display-6 fw-bold text-warning">
                                <?= count(array_filter($notifications, fn($n) => !$n['is_read'])) ?>
                            </div>
                            <small class="text-muted">Non lues</small>
                        </div>
                        <div class="col-md-3 text-center mb-3">
                            <div class="display-6 fw-bold text-success">
                                <?= count(array_filter($notifications, fn($n) => $n['type'] == 'order')) ?>
                            </div>
                            <small class="text-muted">Commandes</small>
                        </div>
                        <div class="col-md-3 text-center mb-3">
                            <div class="display-6 fw-bold text-info">
                                <?= count(array_filter($notifications, fn($n) => $n['type'] == 'promo')) ?>
                            </div>
                            <small class="text-muted">Promotions</small>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/user.js"></script>
    
    <script>
    document.querySelectorAll('.filter-btn').forEach(button => {
        button.addEventListener('click', function() {
            
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            this.classList.add('active');
            
            
            const filter = this.dataset.filter;
            const notifications = document.querySelectorAll('.notification-item');
            
            notifications.forEach(notification => {
                const type = notification.dataset.type;
                const isRead = notification.dataset.read === 'true';
                
                let show = true;
                
                if (filter === 'unread' && isRead) show = false;
                if (filter !== 'all' && filter !== 'unread' && type !== filter) show = false;
                
                notification.style.display = show ? 'block' : 'none';
            });
        });
    });
    
    
    document.querySelectorAll('.mark-read').forEach(button => {
        button.addEventListener('click', function() {
            const notificationId = this.dataset.id;
            const notificationItem = this.closest('.notification-item');
            
            fetch('ajax/mark_notification_read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ notification_id: notificationId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    notificationItem.classList.remove('unread');
                    notificationItem.dataset.read = 'true';
                    this.remove();
                    
                    
                    updateNotificationCount(-1);
                }
            });
        });
    });
    
    
    document.querySelectorAll('.delete-notification').forEach(button => {
        button.addEventListener('click', function() {
            const notificationId = this.dataset.id;
            const notificationItem = this.closest('.notification-item');
            const wasUnread = notificationItem.classList.contains('unread');
            
            if (confirm('Supprimer cette notification ?')) {
                fetch('ajax/delete_notification.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ notification_id: notificationId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        notificationItem.remove();
                        
                        if (wasUnread) {
                            updateNotificationCount(-1);
                        }
                    }
                });
            }
        });
    });
    
   
    document.getElementById('mark-all-read')?.addEventListener('click', function() {
        const unreadNotifications = document.querySelectorAll('.notification-item.unread');
        
        if (unreadNotifications.length === 0) {
            showNotification('info', 'Toutes les notifications sont déjà lues');
            return;
        }
        
        const notificationIds = Array.from(unreadNotifications).map(n => 
            n.querySelector('.mark-read')?.dataset.id
        ).filter(id => id);
        
        fetch('ajax/mark_all_notifications_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ notification_ids: notificationIds })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                unreadNotifications.forEach(notification => {
                    notification.classList.remove('unread');
                    notification.dataset.read = 'true';
                    const markReadBtn = notification.querySelector('.mark-read');
                    if (markReadBtn) markReadBtn.remove();
                });
                
                
                updateNotificationCount(-unreadNotifications.length);
                
                showNotification('success', 'Toutes les notifications ont été marquées comme lues');
            }
        });
    });
    
 
    document.getElementById('clear-all')?.addEventListener('click', function() {
        if (!confirm('Supprimer toutes les notifications ?')) return;
        
        fetch('ajax/clear_all_notifications.php', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.querySelectorAll('.notification-item').forEach(item => item.remove());
                
               
                updateNotificationCount(-999); 
                
             
                const container = document.querySelector('.card-body');
                container.innerHTML = `
                    <div class="text-center py-5">
                        <i class="bi bi-bell-slash display-1 text-muted"></i>
                        <h3 class="mt-3">Aucune notification</h3>
                        <p class="text-muted mb-0">
                            Vous n'avez pas encore de notifications
                        </p>
                    </div>
                `;
            }
        });
    });
    
    
    function updateNotificationCount(change) {
        const badge = document.querySelector('.navbar .badge.bg-danger');
        if (badge) {
            let currentCount = parseInt(badge.textContent) || 0;
            currentCount += change;
            currentCount = Math.max(0, currentCount);
            
            if (currentCount > 0) {
                badge.textContent = currentCount;
                badge.style.display = 'inline';
            } else {
                badge.style.display = 'none';
            }
        }
    }
    </script>
</body>
</html>