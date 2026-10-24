# School Uniform Ordering System (PHP + MySQL + XAMPP)

## Folder structure
```
uniform-order-php/
├── index.php        uniform products (admin can update stock)
├── order.php        place an order (public)
├── login.php / logout.php     admin login
├── dashboard.php    total orders, total sales (admin)
├── orders.php       view, search, delete orders (admin)
├── edit_order.php   edit order + status (admin)
├── config.php       DB connection, admin login settings, helper functions
├── database.sql     tables + sample uniforms and orders
├── includes/        header.php, footer.php, order_form.php
└── assets/          style.css, script.js
```
## Install
1. Start **Apache** and **MySQL** in the XAMPP Control Panel.
2. Copy the `uniform-order-php` folder to `C:\xampp\htdocs\`.
3. Open http://localhost/phpmyadmin → Import → choose `database.sql` → Go.
4. Open http://localhost/uniform-order-php/
5. Admin login: `admin` / `admin123` (change in `config.php`).
