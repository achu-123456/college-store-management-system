-- College Store Management System Database
-- Run this SQL in phpMyAdmin or MySQL CLI

CREATE DATABASE IF NOT EXISTS college_store;
USE college_store;

-- Students Table
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    roll_no VARCHAR(50) NOT NULL UNIQUE,
    phone VARCHAR(15),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Admin Table
CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- Products Table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock INT DEFAULT 0,
    category VARCHAR(100),
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Orders Table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending','confirmed','delivered','cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- Order Items Table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Cart Table
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Password Reset Tokens
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default Admin (password: admin123)
INSERT INTO admin (username, password) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Sample Products
INSERT INTO products (name, description, price, stock, category, image) VALUES
('Scientific Calculator', 'Casio FX-991EX Advanced Scientific Calculator', 850.00, 50, 'Stationery', 'calculator.jpg'),
('Graph Paper Notebook', 'A4 Graph Paper Notebook 100 pages', 120.00, 200, 'Stationery', 'notebook.jpg'),
('Drawing Kit', 'Engineering Drawing Kit with compass and scales', 350.00, 75, 'Drawing', 'drawingkit.jpg'),
('USB Flash Drive 32GB', 'SanDisk 32GB USB Flash Drive', 450.00, 100, 'Electronics', 'usb.jpg'),
('College T-Shirt (M)', 'Official College T-Shirt Medium Size', 299.00, 150, 'Clothing', 'tshirt.jpg'),
('Lab Coat', 'White Lab Coat for practical sessions', 499.00, 60, 'Clothing', 'labcoat.jpg'),
('Pen Set (10 pcs)', 'Blue & Black ballpoint pen pack', 80.00, 300, 'Stationery', 'pens.jpg'),
('C Programming Book', 'Let Us C by Yashavant Kanetkar', 375.00, 40, 'Books', 'book.jpg'),
('Mechanical Pencil', '0.5mm Mechanical Pencil with 10 refills', 95.00, 180, 'Stationery', 'pencil.jpg'),
('Highlighter Set', '5-colour highlighter set', 110.00, 220, 'Stationery', 'highlighters.jpg');
