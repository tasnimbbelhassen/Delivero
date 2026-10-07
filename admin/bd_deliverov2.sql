
CREATE DATABASE IF NOT EXISTS delivero_admin CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE delivero_admin;


DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS dishes;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS restaurants;
DROP TABLE IF EXISTS users;


CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    role ENUM('admin', 'user') DEFAULT 'user',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(50),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_active (is_active),
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS restaurants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    address TEXT,
    phone VARCHAR(20),
    email VARCHAR(100),
    image VARCHAR(255),
    delivery_time VARCHAR(20) DEFAULT '30-40 min',
    rating DECIMAL(3,1) DEFAULT 4.0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_active (is_active),
    INDEX idx_rating (rating)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS dishes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category_id INT,
    restaurant_id INT,
    image VARCHAR(255),
    preparation_time INT DEFAULT 20,
    is_available BOOLEAN DEFAULT TRUE,
    is_special BOOLEAN DEFAULT FALSE,
    is_vegetarian BOOLEAN DEFAULT FALSE,
    calories INT,
    ingredients TEXT,
    rating DECIMAL(3,1) DEFAULT 4.0,
    order_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE SET NULL,
    INDEX idx_category (category_id),
    INDEX idx_restaurant (restaurant_id),
    INDEX idx_available (is_available),
    INDEX idx_special (is_special),
    INDEX idx_price (price)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(20) UNIQUE NOT NULL,
    user_id INT,
    total DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'preparing', 'delivering', 'completed', 'cancelled') DEFAULT 'pending',
    delivery_address TEXT,
    delivery_time TIME,
    payment_method VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_order_number (order_number),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    dish_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (dish_id) REFERENCES dishes(id) ON DELETE SET NULL,
    INDEX idx_order (order_id),
    INDEX idx_dish (dish_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    dish_id INT,
    restaurant_id INT,
    order_id INT,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (dish_id) REFERENCES dishes(id) ON DELETE SET NULL,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE SET NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_dish (dish_id),
    INDEX idx_restaurant (restaurant_id),
    INDEX idx_rating (rating)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



INSERT INTO users (username, email, password, full_name, role, is_active) VALUES
('admin', 'admin@delivero.com', '$2y$10$7C3POf29pRYt5vwn/Ja48.gnEEfvyUzR97pDCPRczwiEE.TB8hOD6', 'Administrateur', 'admin', 1),
('moez', 'moez@delivero.com', '$2y$10$sCT/w61/7yFZLcQr6/NLTu3PJO4riLo3lFtMxtPjR2fXDf1k35UAq', 'Moez Ben Ali', 'admin', 1),
('ranim', 'ranim@delivero.com', '$2y$10$Vpasb/uT0oShikk92jtsO.sn7FrG4fVgo6.Yuam9giT8YxHaQO.Jm', 'Ranim Khouja', 'admin', 1),
('client1', 'client1@email.com', '$2y$10$aCmvb3L0NEPO2BKKm9F/l.z61t9GRWiddtgzQ6U/T./rfLa.Jw3P2', 'Jean Dupont', 'user', 1),
('client2', 'client2@email.com', '$2y$10$xyoreaJ1ld4SWE5hp4p4F.D6zIyY1EqbcxLgbYJxS7klS2LZSjZiu', 'Marie Martin', 'user', 1);


INSERT INTO categories (name, description, icon, is_active) VALUES
('Pizza', 'Délicieuses pizzas fraîchement préparées', 'bi bi-egg-fried', 1),
('Burger', 'Burgers gourmands avec viande fraîche', 'bi bi-cup-straw', 1),
('Sushi', 'Sushi traditionnel japonais', 'bi bi-droplet', 1),
('Pâtes', 'Pâtes italiennes authentiques', 'bi bi-egg', 1),
('Salades', 'Salades fraîches et légères', 'bi bi-flower1', 1),
('Desserts', 'Desserts sucrés pour terminer le repas', 'bi bi-cake', 1),
('Boissons', 'Boissons rafraîchissantes', 'bi bi-cup', 1),
('Asiatique', 'Cuisine asiatique variée', 'bi bi-bowl', 1),
('Fast Food', 'Repas rapides et délicieux', 'bi bi-lightning', 1),
('Traditionnel', 'Plats traditionnels tunisiens', 'bi bi-house', 1);


INSERT INTO restaurants (name, address, phone, email, delivery_time, rating, is_active) VALUES
('Plan B', '123 Rue de la Pizza, Tunis', '71234567', 'contact@planb.tn', '30-40 min', 4.2, 1),
('Tacos Chneb', '456 Avenue du Burger, Tunis', '71234568', 'info@tacoschneb.tn', '25-35 min', 4.5, 1),
('Baguette & Baguette', '789 Rue des Sushis, Tunis', '71234569', 'baguette@email.com', '35-45 min', 4.1, 1),
('Cosmitto', '101 Rue des Pâtes, Tunis', '71234570', 'cosmitto@restaurant.tn', '20-30 min', 4.9, 1),
('Sakura', '202 Avenue des Salades, Tunis', '71234571', 'sakura@sushi.tn', '30-40 min', 4.0, 1),
('Pizza Hut', '303 Avenue Habib Bourguiba', '71234572', 'pizzahut@tunis.tn', '25-35 min', 4.3, 1),
('Burger King', '404 Rue de la République', '71234573', 'bk@delivery.tn', '20-30 min', 4.2, 1),
('McDonald''s', '505 Avenue Mohamed V', '71234574', 'mcdonalds@tn.com', '15-25 min', 4.1, 1);


INSERT INTO dishes (name, description, price, category_id, restaurant_id, preparation_time, ingredients, is_available, is_special, is_vegetarian, calories, rating, order_count) VALUES
('Pizza Margherita', 'Sauce tomate, mozzarella, basilic frais - La classique italienne', 12.99, 1, 1, 15, 'Tomate, Mozzarella, Basilic, Huile d''olive', 1, 0, 1, 850, 4.5, 125),
('Pizza 4 Fromages', 'Mozzarella, gorgonzola, parmesan, chèvre - Pour les amateurs de fromage', 15.99, 1, 1, 18, 'Mozzarella, Gorgonzola, Parmesan, Chèvre', 1, 1, 1, 950, 4.7, 98),
('Burger Deluxe', 'Steak 180g, cheddar, bacon, salade, tomate - Le burger ultime', 15.50, 2, 2, 10, 'Steak de bœuf, Cheddar, Bacon, Salade, Tomate, Oignon', 1, 1, 0, 780, 4.8, 156),
('Burger Végétarien', 'Steak végétal, avocat, tomate, salade - Option healthy', 13.50, 2, 2, 10, 'Steak végétal, Avocat, Tomate, Salade, Sauce spéciale', 1, 0, 1, 650, 4.3, 87),
('Sushi Saumon', '6 pièces de sushi au saumon frais - Fraîcheur garantie', 18.75, 3, 5, 5, 'Riz, Saumon frais, Algues nori, Wasabi', 1, 0, 0, 420, 4.9, 203),
('Sushi Mix', '12 pièces variées de sushi - Pour découvrir différentes saveurs', 28.50, 3, 5, 8, 'Riz, Saumon, Thon, Crevette, Algues nori, Wasabi, Gingembre', 1, 1, 0, 680, 4.8, 145),
('Pâtes Carbonara', 'Pâtes fraîches avec sauce carbonara - Recette traditionnelle', 14.25, 4, 4, 12, 'Pâtes, Lardons, Crème fraîche, Parmesan, Œuf, Poivre', 1, 0, 0, 720, 4.6, 112),
('Pâtes Bolognaise', 'Pâtes avec sauce bolognaise maison - Le classique revisité', 13.75, 4, 4, 15, 'Pâtes, Viande hachée, Tomate, Oignon, Ail, Herbes de Provence', 1, 0, 0, 690, 4.5, 134),
('Salade César', 'Salade romaine, croûtons, parmesan, poulet grillé', 10.99, 5, 3, 8, 'Laitue romaine, Croûtons, Parmesan, Poulet grillé, Sauce césar', 1, 0, 0, 450, 4.4, 178),
('Salade Grecque', 'Tomates, concombre, feta, olives, oignon rouge', 11.50, 5, 3, 7, 'Tomate, Concombre, Feta, Olives Kalamata, Oignon rouge, Huile d''olive', 1, 1, 1, 380, 4.6, 95),
('Tiramisu', 'Dessert italien au café et mascarpone', 6.50, 6, 4, 5, 'Mascarpone, Œufs, Café, Cacao, Biscuits à la cuillère', 1, 0, 1, 320, 4.7, 167),
('Fondant au Chocolat', 'Gâteau au chocolat fondant, cœur coulant', 7.25, 6, 1, 12, 'Chocolat noir, Beurre, Œufs, Sucre, Farine', 1, 1, 1, 480, 4.9, 189),
('Coca-Cola', 'Boisson gazeuse rafraîchissante 33cl', 2.50, 7, 1, 2, 'Eau gazéifiée, Sucre, Colorant, Arômes', 1, 0, 1, 140, 4.5, 345),
('Jus d''Orange', 'Jus d''orange pressé frais 25cl', 3.75, 7, 3, 2, 'Oranges fraîches', 1, 0, 1, 110, 4.3, 234),
('Nuggets de Poulet', '6 pièces de nuggets de poulet croustillants', 8.99, 9, 7, 8, 'Poulet, Chapelure, Œuf, Farine, Épices', 1, 0, 0, 420, 4.2, 198),
('Frites Maison', 'Frites coupées maison, croustillantes à l''extérieur, moelleuses à l''intérieur', 4.50, 9, 7, 10, 'Pommes de terre, Huile, Sel', 1, 0, 1, 320, 4.4, 267),
('Couscous Royal', 'Couscous avec viandes multiples et légumes', 22.99, 10, 4, 25, 'Semoule, Agneau, Poulet, Merguez, Légumes, Sauce tomate', 1, 1, 0, 850, 4.8, 89),
('Brik à l''Œuf', 'Feuille de brick croustillante avec œuf et thon', 5.50, 10, 3, 6, 'Feuille de brick, Œuf, Thon, Persil, Câpres', 1, 0, 0, 280, 4.6, 156);


INSERT INTO orders (order_number, user_id, total, status, delivery_address, payment_method, notes) VALUES
('ORD-20241217-ABC123', 1, 42.48, 'completed', '15 Rue de Carthage, Tunis 1000', 'Carte bancaire', 'Porte bleue, 3ème étage'),
('ORD-20241217-DEF456', 2, 28.75, 'delivering', '27 Avenue Habib Bourguiba, Tunis 1001', 'Espèces', 'Sonner chez Martin'),
('ORD-20241217-GHI789', 1, 56.99, 'preparing', '8 Rue de la République, Ariana 2080', 'Carte bancaire', 'Livrer avant 14h si possible'),
('ORD-20241216-JKL012', 3, 31.50, 'completed', '42 Avenue Mohamed V, La Marsa 2070', 'Espèces', 'Appeler à l''arrivée'),
('ORD-20241216-MNO345', 4, 18.74, 'pending', '18 Rue Ali Belhouane, Ben Arous 2013', 'Carte bancaire', ''),
('ORD-20241215-PQR678', 5, 24.99, 'cancelled', '5 Rue de Palestine, Le Bardo 2000', 'Espèces', 'Annulation client');


INSERT INTO order_items (order_id, dish_id, quantity, price, subtotal) VALUES
(1, 1, 2, 12.99, 25.98),
(1, 9, 1, 10.99, 10.99),
(1, 7, 1, 14.25, 14.25),
(2, 5, 1, 18.75, 18.75),
(2, 10, 1, 11.50, 11.50),
(3, 3, 2, 15.50, 31.00),
(3, 6, 1, 28.50, 28.50),
(4, 4, 1, 13.50, 13.50),
(4, 8, 1, 13.75, 13.75),
(5, 13, 3, 2.50, 7.50),
(5, 1, 1, 12.99, 12.99),
(6, 11, 1, 6.50, 6.50),
(6, 15, 1, 8.99, 8.99),
(6, 16, 2, 4.50, 9.00);



SELECT '=== BASE DE DONNÉES DELIVERO CRÉÉE AVEC SUCCÈS ===' as Message;
SELECT ' ' as Blank;

SELECT '=== IDENTIFIANTS DE CONNEXION ===' as Info;
SELECT 'Administrateur principal:' as Compte, 'admin' as Username, 'admin123' as Password, 'admin@delivero.com' as Email, 'admin' as Role
UNION ALL
SELECT 'Client test:' as Compte, 'client1' as Username, 'admin123' as Password, 'client1@email.com' as Email, 'user' as Role;

SELECT ' ' as Blank;
SELECT '=== STATISTIQUES ===' as Stats;
SELECT 
    (SELECT COUNT(*) FROM users) as Total_Users,
    (SELECT COUNT(*) FROM categories) as Total_Categories,
    (SELECT COUNT(*) FROM restaurants) as Total_Restaurants,
    (SELECT COUNT(*) FROM dishes) as Total_Dishes,
    (SELECT COUNT(*) FROM orders) as Total_Orders,
    (SELECT SUM(total) FROM orders WHERE status = 'completed') as Revenue_Total;

