<?php
$is_logged_in = isset($_SESSION['user_id']);
$user_name = $_SESSION['user_name'] ?? '';
$cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
?>

<style>
    @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montez&display=swap");


:root {
    --primary-green: #39c1c1;
    --dark-green: #216767;
    --bg-light: #fbfcf8;
    --card-bg: #ffffff;
    --text-dark: #216767;
    --text-gray: #888888;
    --border-color: #e0e0e0;
    --shadow-light: 0 4px 20px rgba(0, 0, 0, 0.05);
    --shadow-medium: 0 8px 30px rgba(0, 0, 0, 0.08);
    --success-color: #28a745;
    --warning-color: #ffc107;
    --danger-color: #dc3545;
    --info-color: #17a2b8;
}

.navbar {
    background: var(--primary-green) !important;
    box-shadow: none;
    position: relative;
    padding-bottom: 0;
}

.navbar::after {
    content: '';
    position: absolute;
    bottom: -30px;
    left: 0;
    width: 100%;
    height: 40px;
    background: var(--primary-green);
    clip-path: ellipse(70% 100% at 50% 0%);
    z-index: -1;
}

.navbar-brand {
    color: white !important;
    font-weight: 700;
    font-size: 1.5rem;
    transition: transform 0.3s ease;
}

.navbar-brand:hover {
    transform: scale(1.05);
}
.logo{
    margin-top: 0.75%;
}

#navbarNav{
    margin-top: 1%;
}

.truck-logo{
    color: #ffffff;
}
.nav-link {
    color: rgba(255, 255, 255, 0.9) !important;
    transition: all 0.3s ease;
    position: relative;
    padding: 0.5rem 1rem !important;
    text-decoration: none !important;
}

.nav-link::after {
    content: '';
    position: absolute;
    bottom: 10%;
    left: 50%;
    width: 0;
    height: 2px;
    background: white;
    transition: all 0.3s ease;
    transform: translateX(-50%);
}

.nav-link:hover {
    color: white !important;
    transform: translateY(-2px);
}

.nav-link:hover::after {
    width: 80%;
}

.btn-light {
    background: white;
    border: none;
    color: var(--text-dark);
    border-radius: 20px;
    padding: 0.5rem 1rem;
    transition: all 0.3s ease;
}

.btn-light:hover {
    background: var(--bg-light);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn-outline-light {
    border-radius: 20px;
    transition: all 0.3s ease;
}

.btn-outline-light:hover {
    background: var(--dark-green);
    border-color: var(--dark-green);
    transform: translateY(-2px);
}

.dropdown-menu {
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-medium);
    border-radius: 12px;
    margin-top: 0.5rem;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.dropdown-item {
    transition: all 0.2s ease;
    border-radius: 8px;
    margin: 0.2rem 0.5rem;
    max-width:150px ;
}

.dropdown-item:hover {
    background-color: var(--bg-light);
    color: var(--primary-green);
    max-width:150px ;
}

.cart-count {
    background: var(--danger-color) !important;
}

.btn-primary {
    background: var(--primary-green) !important;
    border-color: var(--primary-green) !important;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background: var(--dark-green) !important;
    border-color: var(--dark-green) !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(57, 193, 193, 0.3);
}

.btn-success {
    background: var(--success-color) !important;
    border-color: var(--success-color) !important;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}

.mini-cart {
    border-radius: 12px;
}

/* Custom Cart Dropdown Styles */
.mini-cart {
    min-width: 360px;
    border: none;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    overflow: hidden;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
}

.cart-header {
    padding: 1.2rem;
    color: var(--dark-green);
}

.cart-header p {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
}

.cart-items {
    max-height: 300px;
    overflow-y: auto;
    padding: 1rem;
}

.cart-items::-webkit-scrollbar {
    width: 6px;
}

.cart-items::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.cart-items::-webkit-scrollbar-thumb {
    background: var(--primary-green);
    border-radius: 10px;
}

.cart-empty {
    text-align: center;
    padding: 3rem 1rem;
}

.empty-cart-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
    animation: float 3s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

.cart-empty p {
    color: var(--text-dark);
    font-weight: 600;
    margin: 0.5rem 0;
}

.cart-empty small {
    color: var(--text-gray);
}

.cart-footer {
    border-top: 2px solid var(--border-color);
    padding: 1.2rem;
    background: white;
}

.cart-summary {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    font-size: 1.1rem;
}

.cart-summary strong {
    color: var(--primary-green);
    font-size: 1.3rem;
}

.cart-actions {
    display: flex;
    flex-direction: column;
    gap: 0.8rem;
}

.btn-cart-view,
.btn-checkout {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.9rem 1.2rem;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.btn-cart-view {
    background: linear-gradient(135deg, var(--primary-green) 0%, #4dd4d4 100%);
    color: white;
}

.btn-cart-view:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(57, 193, 193, 0.4);
}

.btn-cart-view .btn-arrow {
    transition: transform 0.3s ease;
}

.btn-cart-view:hover .btn-arrow {
    transform: translateX(5px);
}

.btn-checkout {
    background: linear-gradient(135deg, var(--success-color) 0%, #34ce57 100%);
    color: white;
    position: relative;
}

.btn-checkout::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.5s ease;
}

.btn-checkout:hover::before {
    left: 100%;
}

.btn-checkout:hover {
    transform: translateY(-2px) scale(1.02);
    box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
}

.btn-checkout.disabled {
    background: linear-gradient(135deg, #cccccc 0%, #999999 100%);
    cursor: not-allowed;
    opacity: 0.6;
}

.btn-checkout.disabled:hover {
    transform: none;
    box-shadow: none;
}

.btn-sparkle {
    animation: sparkle 2s ease-in-out infinite;
}

@keyframes sparkle {
    0%, 100% { 
        opacity: 1;
        transform: scale(1) rotate(0deg);
    }
    50% { 
        opacity: 0.7;
        transform: scale(1.2) rotate(20deg);
    }
}

.navbar-toggler {
    border: 2px solid white;
    transition: all 0.3s ease;
}

.navbar-toggler:hover {
    transform: rotate(90deg);
}
.span-plus{
    font-family: 'Montez', cursive; 
    color: var(--dark-green);
    font-size: 25px;
    }
</style>

<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container">
    
        <a class="navbar-brand fw-bold logo" href="index.php">
            <i class="bi bi-truck truck-logo"></i> Delivero
        </a>
        
     
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" 
                data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
  
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Tableau de bord</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="home.php">Restaurants</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="orders.php">Mes commandes</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="profile.php">Mon compte</a>
                </li>
            </ul>
            
           
                    
            <div class="d-flex align-items-center">
               
                <div class="dropdown me-3">
                    <button class="btn btn-light dropdown-toggle position-relative" 
                            type="button" 
                            data-bs-toggle="dropdown"
                            id="cartDropdown">
                        <i class="bi bi-cart"></i>
                        <span class="cart-count badge bg-danger">
                            <?= $cart_count ?>
                        </span>
                    </button>
                    
                
                    <div class="dropdown-menu dropdown-menu-end mini-cart" 
                         aria-labelledby="cartDropdown">
                        <div class="cart-header">
                            <p>Mon <span class="span-plus">Panier</span></p>
                        </div>
                        
                        
                        <div class="cart-items">
                           
                        </div>
                        
                    
                        <div class="cart-empty" style="<?= $cart_count > 0 ? 'display: none;' : '' ?>">
                            <p>Votre panier est vide</p>
                            <small>Ajoutez vos plats favoris !</small>
                        </div>
                        
                     
                        <div class="cart-footer">
                            <div class="cart-summary">
                                <span>Sous-total</span>
                                <strong class="cart-subtotal">0,000 DT</strong>
                            </div>
                            <div class="cart-actions">
                                <a href="cart.php" class="btn-cart-view">
                                    <span>Voir le panier</span>
                                    <span class="btn-arrow">→</span>
                                </a>
                                <a href="checkout.php" class="btn-checkout <?= $cart_count == 0 ? 'disabled' : '' ?>" 
                                   <?= $cart_count == 0 ? 'onclick="return false;"' : '' ?>>
                                    <span>Commander</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                

                <a href="cart.php" class="btn btn-light d-lg-none me-3 position-relative">
                    <i class="bi bi-cart"></i>
                    <?php if ($cart_count > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?= $cart_count ?>
                        </span>
                    <?php endif; ?>
                </a>
                
              
                <div class="dropdown">
                    <button class="btn btn-outline-light dropdown-toggle" 
                            type="button" data-bs-toggle="dropdown">
                        <span class="d-none d-md-inline"><?= htmlspecialchars($user_name) ?></span>
                        <span class="d-md-none">Menu</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profile.php">Mon profil</a></li>
                        <li><a class="dropdown-item" href="orders.php">Mes commandes</a></li>
                        <li><a class="dropdown-item" href="favorites.php">Favoris</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php">Déconnexion</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>

<div style="height: 76px;"></div> 


<script>

if (typeof window.DeliveroCart !== 'undefined') {
   
    window.DeliveroCart.updateCartDisplay();
    
    
    const phpCartCount = <?= $cart_count ?>;
    const jsCartCount = window.DeliveroCart.getTotalItems();
    
    if (phpCartCount !== jsCartCount) {
        const badges = document.querySelectorAll('.badge.bg-danger');
        badges.forEach(badge => {
            if (badge.classList.contains('cart-count')) {
                badge.textContent = jsCartCount;
                badge.style.display = jsCartCount > 0 ? 'inline' : 'none';
            }
        });
    }
}


document.addEventListener('DOMContentLoaded', function() {
    const checkoutBtn = document.querySelector('.checkout-btn');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', function(e) {
            if (this.hasAttribute('disabled')) {
                e.preventDefault();
                alert('Votre panier est vide. Ajoutez des articles avant de commander.');
            }
        });
    }
    
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.classList.add('add-to-cart');
    });
});
</script>