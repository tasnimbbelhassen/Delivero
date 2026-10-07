<style>
/* Footer Styles */
.footer {
    background: #fbfcf8;
    color: var(--text-dark);
    margin-top: 5rem;
    padding-top: 5rem;
    position: relative;
}

.footer::before {
    content: '';
    position: absolute;
    top: -5px;
    left: 0;
    width: 100%;
    height: 60px;
    border-top: 2px solid var(--primary-green);
    border-radius: 50% 50% 0 0 / 100% 100% 0 0;

}

.footer-container {
    max-width: 1320px;
    margin: 0 auto;
    padding: 0 15px;
}

.footer-grid {
    display: grid;
    grid-template-columns: 2fr 1fr 1.5fr 1.5fr;
    gap: 3rem;
    margin-bottom: 3rem;
}

.footer-brand h5 {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
    color: var(--primary-green);
}

.footer-brand p {
    color: var(--text-gray);
    font-size: 0.9rem;
    line-height: 1.6;
    margin-bottom: 1.5rem;
}

.footer-social {
    display: flex;
    gap: 1rem;
}

.footer-social a {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    border-radius: 50%;
    color: var(--primary-green);
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.footer-social a:hover {
    background: var(--primary-green);
    color: white;
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(57, 193, 193, 0.3);
}

.footer-section h6 {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    color: var(--text-dark);
}

.footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-links li {
    margin-bottom: 0.75rem;
}

.footer-links a {
    color: var(--text-gray);
    text-decoration: none;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    display: inline-block;
}

.footer-links a:hover {
    color: var(--primary-green);
    transform: translateX(5px);
}

.footer-contact {
    list-style: none;
    padding: 0;
    margin: 0;
    color: var(--text-gray);
    font-size: 0.9rem;
}

.footer-contact li {
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.footer-contact i {
    color: var(--primary-green);
    font-size: 1.1rem;
}

.footer-bottom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.footer-copyright {
    color: var(--text-gray);
    font-size: 0.85rem;
    margin: 0;
}

.footer-payments {
    color: var(--text-gray);
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.footer-payments i {
    font-size: 1.2rem;
    color: var(--primary-green);
}


.back-to-top {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 50px;
    height: 50px;
    background: var(--primary-green);
    color: white;
    border: none;
    border-radius: 50%;
    display: none;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 999;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(57, 193, 193, 0.3);
}

.back-to-top:hover {
    background: var(--dark-green);
    transform: translateY(-5px);
    box-shadow: 0 6px 20px rgba(57, 193, 193, 0.5);
}

.back-to-top i {
    font-size: 1.2rem;
}


@media (max-width: 992px) {
    .footer-grid {
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
    }
}

@media (max-width: 576px) {
    .footer {
        padding-top: 3rem;
    }
    
    .footer-grid {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .footer-bottom {
        flex-direction: column;
        text-align: center;
    }
    
    .back-to-top {
        width: 45px;
        height: 45px;
        bottom: 20px;
        right: 20px;
    }
}
</style>

<footer class="footer">
    <div class="footer-container">
        <div class="footer-grid">
            
            <div class="footer-brand">
                <h5>
                    <i class="bi bi-truck"></i> Delivero
                </h5>
                <p>
                    Livraison de repas rapide et fiable dans toute la ville. 
                    Commandez vos plats préférés et recevez-les chez vous en quelques minutes.
                </p>
                <div class="footer-social">
                    <a href="#" aria-label="Facebook">
                        <i class="bi bi-facebook"></i>
                    </a>
                    <a href="#" aria-label="Instagram">
                        <i class="bi bi-instagram"></i>
                    </a>
                    <a href="#" aria-label="Twitter">
                        <i class="bi bi-twitter"></i>
                    </a>
                </div>
            </div>
            
            <div class="footer-section">
                <h6>Liens rapides</h6>
                <ul class="footer-links">
                    <li><a href="home.php">Restaurants</a></li>
                    <li><a href="orders.php">Mes commandes</a></li>
                    <li><a href="profile.php">Mon compte</a></li>
                    <li><a href="cart.php">Mon panier</a></li>
                </ul>
            </div>
            
           
            <div class="footer-section">
                <h6>Informations</h6>
                <ul class="footer-links">
                    <li><a href="about.php">À propos</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="help.php">Aide & FAQ</a></li>
                    <li><a href="#">CGU</a></li>
                    <li><a href="#">Confidentialité</a></li>
                </ul>
            </div>
            
          
            <div class="footer-section">
                <h6>Contactez-nous</h6>
                <ul class="footer-contact">
                    <li>
                        <i class="bi bi-telephone"></i>
                        <span>+216 71 234 567</span>
                    </li>
                    <li>
                        <i class="bi bi-envelope"></i>
                        <span>support@delivero.tn</span>
                    </li>
                    <li>
                        <i class="bi bi-clock"></i>
                        <span>24h/24, 7j/7</span>
                    </li>
                    <li>
                        <i class="bi bi-geo-alt"></i>
                        <span>Tunis, Tunisie</span>
                    </li>
                </ul>
            </div>
        </div>
        
        
     
        <div class="footer-bottom">
            <p class="footer-copyright">
                &copy; <?= date('Y') ?> Delivero. Tous droits réservés.
            </p>
            <div class="footer-payments">
                <span>Paiements sécurisés:</span>
                <i class="bi bi-credit-card"></i>
                <i class="bi bi-paypal"></i>
            </div>
        </div>
    </div>
</footer>


<button id="back-to-top" class="back-to-top" aria-label="Retour en haut">
    <i class="bi bi-arrow-up"></i>
</button>

<script>

const backToTopButton = document.getElementById('back-to-top');

window.addEventListener('scroll', () => {
    if (window.pageYOffset > 300) {
        backToTopButton.style.display = 'flex';
    } else {
        backToTopButton.style.display = 'none';
    }
});

backToTopButton.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
});
</script>
<script src="assets/js/cart.js"></script>