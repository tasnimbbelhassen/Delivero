

class CartManager {
    constructor() {
        this.init();
    }

    init() {
        console.log('Cart Manager initialisé');
        this.addEventListeners();
        this.updateCartCount();
        this.ensureModalExists();
    }

    ensureModalExists() {
        
        if (!document.getElementById('restaurantConflictModal')) {
            this.createConflictModal();
        }
    }

    createConflictModal() {
        const modalHTML = `
            <div class="modal fade" id="restaurantConflictModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                                Conflit de restaurant
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body" id="restaurantConflictBody">
                            <!-- Contenu dynamique -->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-arrow-left"></i> Annuler
                            </button>
                            <button type="button" class="btn btn-primary" id="confirmSwitchRestaurant">
                                <i class="bi bi-check-circle"></i> Changer de restaurant
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    }

    addEventListeners() {
        
        document.addEventListener('click', async (e) => {
            if (e.target.closest('.add-to-cart')) {
                e.preventDefault();
                await this.addToCart(e.target.closest('.add-to-cart'));
            }
            
            
            if (e.target.closest('.remove-item, .remove-mini-cart-item')) {
                e.preventDefault();
                await this.removeItem(e.target.closest('.remove-item, .remove-mini-cart-item'));
            }
        });
        
     
        const cartDropdown = document.getElementById('cartDropdown');
        if (cartDropdown) {
            cartDropdown.addEventListener('show.bs.dropdown', () => {
                this.updateMiniCart();
            });
        }
        
       
        document.addEventListener('click', (e) => {
            if (e.target.id === 'confirmSwitchRestaurant') {
                this.handleSwitchRestaurant();
            }
        });
    }

    async addToCart(button) {
    const dishId = button.dataset.dishId;
    const dishName = button.dataset.dishName || 'Article';
    const restaurantId = button.dataset.restaurantId;
    const restaurantName = button.dataset.restaurantName || 'Restaurant';
    
    if (!dishId || !restaurantId) {
        this.showNotification('error', 'Données incomplètes');
        return;
    }

    
    const originalText = button.innerHTML;
    const originalState = button.disabled;
    
  
    button.disabled = true;
    button.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

    try {
        const response = await fetch('ajax/add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ 
                dish_id: dishId, 
                quantity: 1
            })
        });

        const data = await response.json();
        
        if (data.success) {
            await this.updateCartCount(data.count);
            await this.updateMiniCart();

            this.playAddAnimation(button);
            

            this.showNotification('success', 
                `<strong>${dishName}</strong> ajouté au panier<br>
                 <small>Restaurant: ${restaurantName}</small>`);
            
        } else if (data.action_required) {
            this.currentDishId = dishId;
            this.currentRestaurantName = data.details?.restaurant_name || restaurantName;
            this.currentRestaurantId = data.details?.new_restaurant;
            this.showRestaurantConflictModal(this.currentRestaurantName);
        } else {
            this.showNotification('error', data.message || 'Erreur lors de l\'ajout');
        }
    } catch (error) {
        console.error('Erreur:', error);
        this.showNotification('error', 'Erreur de connexion au serveur');
    } finally {
        button.disabled = originalState;
        button.innerHTML = originalText;
    }
}

    showRestaurantConflictModal(restaurantName) {
        const modalElement = document.getElementById('restaurantConflictModal');
        if (!modalElement) {
            this.showNotification('error', 'Modal non disponible');
            return;
        }
        
        const modalBody = document.getElementById('restaurantConflictBody');
        
        modalBody.innerHTML = `
            <div class="text-center">
                <i class="bi bi-exclamation-triangle text-warning display-4 mb-3"></i>
                <h5>Conflit de restaurant</h5>
                <p>Votre panier contient déjà des articles d'un restaurant différent.</p>
                
                <div class="alert alert-info mt-3">
                    <p class="mb-0">
                        <strong>Nouveau restaurant:</strong> ${restaurantName}
                    </p>
                    <p class="mb-0 mt-2">
                        Souhaitez-vous vider votre panier actuel et commander chez <strong>${restaurantName}</strong> ?
                    </p>
                </div>
                
                <div class="alert alert-warning mt-3">
                    <small>
                        <i class="bi bi-info-circle"></i> 
                        Vous ne pouvez commander que d'un seul restaurant à la fois.
                    </small>
                </div>
            </div>
        `;
        

        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    }

    async handleSwitchRestaurant() {
    if (!this.currentDishId) {
        this.showNotification('error', 'Erreur: plat non défini');
        return;
    }
    
    try {
        const response = await fetch('ajax/switch_restaurant.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ 
                dish_id: this.currentDishId, 
                quantity: 1 
            })
        });

        const data = await response.json();
        
        if (data.success) {

            await this.updateCartCount(data.count);
            await this.updateMiniCart();
            
       
            const modalElement = document.getElementById('restaurantConflictModal');
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
                modal.hide();
            }
            
            this.showNotification('success', 
                data.message || `Panier mis à jour. Vous commandez maintenant chez ${this.currentRestaurantName}`);
            

            this.currentDishId = null;
            this.currentRestaurantName = null;
            this.currentRestaurantId = null;
            
        } else {
            this.showNotification('error', data.message || 'Erreur lors du changement');
        }
    } catch (error) {
        console.error('Erreur:', error);
        this.showNotification('error', 'Erreur de connexion');
    }
}

    async removeItem(button) {
        const dishId = button.dataset.dishId;
        
        if (!confirm('Supprimer cet article du panier ?')) {
            return;
        }

        try {
            const response = await fetch('ajax/remove_from_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ dish_id: dishId })
            });

            const data = await response.json();
            
            if (data.success) {
                await this.updateCartCount(data.count);
                await this.updateMiniCart();
                
              
                const item = button.closest('.cart-item, .mini-cart-item');
                if (item) {
                    item.style.opacity = '0';
                    item.style.height = '0';
                    item.style.margin = '0';
                    item.style.padding = '0';
                    setTimeout(() => {
                        if (item.parentNode) {
                            item.remove();
                            this.checkEmptyCart();
                        }
                    }, 300);
                }
                
                this.showNotification('info', 'Article retiré du panier');
            }
        } catch (error) {
            console.error('Erreur:', error);
            this.showNotification('error', 'Erreur lors de la suppression');
        }
    }

    async updateCartCount(count = null) {
        if (count === null) {
            try {
                const response = await fetch('ajax/get_cart_count.php');
                const data = await response.json();
                count = data.success ? data.count : 0;
            } catch (error) {
                console.error('Erreur:', error);
                count = 0;
            }
        }

      
        const counters = document.querySelectorAll('.cart-count');
        counters.forEach(counter => {
            counter.textContent = count;
            if (counter.classList.contains('badge')) {
                counter.style.display = count > 0 ? 'inline-block' : 'none';
            }
        });

        return count;
    }

    async updateMiniCart() {
    const miniCart = document.querySelector('.mini-cart');
    if (!miniCart) return;
    
    try {
        const response = await fetch('ajax/get_mini_cart.php');
        const data = await response.json();
        
        const cartItems = miniCart.querySelector('.cart-items');
        const emptyMessage = miniCart.querySelector('.cart-empty');
        
        if (cartItems) {
            if (data.has_items) {
                cartItems.innerHTML = data.html;
                if (emptyMessage) emptyMessage.style.display = 'none';
                
             
                cartItems.querySelectorAll('.remove-mini-cart-item').forEach(button => {
                    button.addEventListener('click', (e) => {
                        e.preventDefault();
                        this.removeItem(button);
                    });
                });
            } else {
                cartItems.innerHTML = '';
                if (emptyMessage) emptyMessage.style.display = 'block';
            }
        }
        
        
        const subtotalEl = miniCart.querySelector('.cart-subtotal');
        if (subtotalEl && data.total !== undefined) {
            
            subtotalEl.textContent = data.total.toFixed(3).replace('.', ',') + ' DT';
        }
        
      
        const checkoutBtn = miniCart.querySelector('.checkout-btn');
        if (checkoutBtn) {
            checkoutBtn.disabled = !data.has_items;
            checkoutBtn.classList.toggle('disabled', !data.has_items);
        }
        
    } catch (error) {
        console.error('Erreur:', error);
    }
}

    playAddAnimation(button) {

        button.classList.add('added');
        setTimeout(() => button.classList.remove('added'), 300);
        const cartIcon = document.querySelector('#cartDropdown');
        if (cartIcon) {
            cartIcon.classList.add('bounce');
            setTimeout(() => cartIcon.classList.remove('bounce'), 300);
        }
    }

    showNotification(type, message) {
        const oldNotifications = document.querySelectorAll('.cart-notification');
        oldNotifications.forEach(n => n.remove());
        
      
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed cart-notification`;
        notification.style.cssText = `
            top: 80px;
            right: 20px;
            z-index: 1060;
            min-width: 300px;
            max-width: 400px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        `;
        
        const icons = {
            success: 'bi-check-circle-fill',
            error: 'bi-exclamation-circle-fill',
            warning: 'bi-exclamation-triangle-fill',
            info: 'bi-info-circle-fill'
        };
        
        notification.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="bi ${icons[type]} me-2 fs-5 text-${type}"></i>
                <div class="flex-grow-1">${message}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 4000);
    }

    checkEmptyCart() {
        const cartCountElement = document.querySelector('.cart-count');
        if (cartCountElement) {
            const count = parseInt(cartCountElement.textContent);
            if (count === 0) {
                this.showNotification('info', 'Votre panier est vide');
            }
        }
    }
}


let cartManager = null;

document.addEventListener('DOMContentLoaded', function() {
    if (typeof bootstrap !== 'undefined') {
        cartManager = new CartManager();
        window.cartManager = cartManager;
    } else {
        console.error('Bootstrap non chargé');
    }
});