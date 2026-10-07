
document.addEventListener('DOMContentLoaded', function() {
    
    const searchInput = document.querySelector('input[name="search"]');
    const categorySelect = document.querySelector('select[name="category"]');
    
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (this.value.length >= 2 || this.value.length === 0) {
                    this.form.submit();
                }
            }, 500);
        });
    }
    
    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            this.form.submit();
        });
    }
    
  
    document.querySelectorAll('.restaurant-rating').forEach(rating => {
        const score = parseFloat(rating.dataset.rating);
        const stars = rating.querySelectorAll('.bi-star');
        
        stars.forEach((star, index) => {
            if (index < Math.floor(score)) {
                star.classList.remove('bi-star');
                star.classList.add('bi-star-fill', 'text-warning');
            } else if (index === Math.floor(score) && score % 1 >= 0.5) {
                star.classList.remove('bi-star');
                star.classList.add('bi-star-half', 'text-warning');
            }
        });
    });
    
    
    const sortSelect = document.querySelector('#sort-restaurants');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            const restaurants = document.querySelectorAll('.restaurant-card');
            const container = document.querySelector('.restaurants-container');
            const sortedRestaurants = Array.from(restaurants);
            
            sortedRestaurants.sort((a, b) => {
                const getValue = (element, type) => {
                    switch(type) {
                        case 'rating':
                            return parseFloat(element.querySelector('.rating-badge').textContent);
                        case 'delivery':
                            return parseInt(element.querySelector('.delivery-time').textContent);
                        case 'name':
                            return element.querySelector('.restaurant-name').textContent.toLowerCase();
                        default:
                            return 0;
                    }
                };
                
                const aValue = getValue(a, this.value);
                const bValue = getValue(b, this.value);
                
                if (this.value === 'rating' || this.value === 'delivery') {
                    return bValue - aValue;
                } else {
                    return aValue.localeCompare(bValue);
                }
            });

            sortedRestaurants.forEach(restaurant => {
                container.appendChild(restaurant);
            });
        });
    }
    
    const locationBtn = document.querySelector('#detect-location');
    if (locationBtn) {
        locationBtn.addEventListener('click', function() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    position => {
                        const { latitude, longitude } = position.coords;
                        
                        fetch('ajax/get_nearby_restaurants.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({ lat: latitude, lng: longitude })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showNotification('success', 'Restaurants à proximité chargés');
                                updateRestaurantsList(data.restaurants);
                            }
                        });
                    },
                    error => {
                        showNotification('error', 'Impossible de détecter votre position');
                    }
                );
            } else {
                showNotification('error', 'Géolocalisation non supportée');
            }
        });
    }
    

    function updateRestaurantsList(restaurants) {
        const container = document.querySelector('.restaurants-container');
        container.innerHTML = '';
        
        restaurants.forEach(restaurant => {
            const card = createRestaurantCard(restaurant);
            container.appendChild(card);
        });
    }
    
    
    function createRestaurantCard(restaurant) {
        const card = document.createElement('div');
        card.className = 'col-md-6 col-lg-4 mb-4';
        card.innerHTML = `
            <div class="card h-100 restaurant-card">
                <div class="position-relative">
                    <img src="${restaurant.image || 'assets/images/default-restaurant.jpg'}" 
                         class="card-img-top" 
                         alt="${restaurant.name}"
                         style="height: 200px; object-fit: cover;">
                    
                    <div class="position-absolute top-0 end-0 m-2">
                        <span class="badge bg-warning text-dark">
                            <i class="bi bi-star-fill"></i> ${restaurant.rating}
                        </span>
                    </div>
                    
                    ${restaurant.distance ? `
                        <div class="position-absolute bottom-0 start-0 m-2">
                            <span class="badge bg-dark">
                                <i class="bi bi-geo-alt"></i> ${restaurant.distance} km
                            </span>
                        </div>
                    ` : ''}
                </div>
                
                <div class="card-body">
                    <h5 class="card-title">${restaurant.name}</h5>
                    
                    <div class="mb-3">
                        <small class="text-muted">
                            <i class="bi bi-geo-alt"></i> 
                            ${restaurant.address.substring(0, 50)}${restaurant.address.length > 50 ? '...' : ''}
                        </small>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="badge bg-light text-dark border delivery-time">
                            <i class="bi bi-clock"></i> ${restaurant.delivery_time}
                        </span>
                    </div>
                    
                    <div class="d-grid">
                        <a href="menu.php?restaurant_id=${restaurant.id}" 
                           class="btn btn-primary">
                            <i class="bi bi-eye"></i> Voir le menu
                        </a>
                    </div>
                </div>
            </div>
        `;
        
        return card;
    }
    
  
    document.querySelectorAll('.favorite-restaurant').forEach(button => {
        button.addEventListener('click', function() {
            const restaurantId = this.dataset.restaurantId;
            const isFavorite = this.classList.contains('active');
            
            fetch('ajax/toggle_restaurant_favorite.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    restaurant_id: restaurantId,
                    action: isFavorite ? 'remove' : 'add'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.classList.toggle('active');
                    const icon = this.querySelector('i');
                    icon.className = isFavorite ? 
                        'bi bi-heart' : 
                        'bi bi-heart-fill text-danger';
                    
                    showNotification(
                        'success', 
                        isFavorite ? 'Retiré des favoris' : 'Ajouté aux favoris'
                    );
                }
            });
        });
    });
});