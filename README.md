# 🍔 Delivero

Delivero is a PHP-based food delivery web application that allows users to discover restaurants, browse menus, manage their carts, place orders, and track deliveries.

## ✨ Features

### 👤 User

* User registration and login
* Browse restaurants
* Browse restaurant menus
* Add dishes to cart
* Manage cart items
* Place orders
* Track orders
* Manage addresses
* Manage favorites
* Write reviews
* Manage user profile

### 🔐 Admin

* Admin authentication
* Manage restaurants
* Manage categories
* Manage dishes
* Manage orders
* Update order status
* Manage users and settings
* Dashboard and administration interface

## 🛠️ Technologies

* **PHP**
* **MySQL**
* **HTML5**
* **CSS3**
* **JavaScript**
* **AJAX**
* **WAMP**

## 📁 Project Structure

```text
Delivero/
│
├── admin/
│   ├── includes/
│   ├── templates/
│   ├── css/
│   └── js/
│
├── user/
│   ├── ajax/
│   ├── assets/
│   ├── includes/
│   └── ...
│
├── login.php
├── register.php
├── .gitignore
└── README.md
```

## 🚀 Installation

### 1. Clone the repository

```bash
git clone https://github.com/tasnimbbelhassen/Delivero.git
```

### 2. Move the project

Place the project inside your WAMP `www` directory:

```text
C:\wamp64\www\
```

### 3. Create the database

Open **phpMyAdmin** and create the Delivero database.

Then import the SQL file:

```text
admin/bd_deliverov2.sql
```

### 4. Configure the database

Update the database configuration in the project's configuration files with your local MySQL credentials.

### 5. Run the application

Start **Apache** and **MySQL** from WAMP.

Then open:

```text
http://localhost/mini-projet/mini-projet/
```

## 👥 Project

**Delivero** — Food Delivery Web Application

Developed as an academic web development project using PHP and MySQL.
