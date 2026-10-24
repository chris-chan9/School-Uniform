-- School Uniform Ordering System. Import in phpMyAdmin (Import tab) or run in MySQL.
CREATE DATABASE IF NOT EXISTS uniform_db CHARACTER SET utf8mb4;
USE uniform_db;

CREATE TABLE uniforms (
  uniform_id INT AUTO_INCREMENT PRIMARY KEY,
  uniform_name VARCHAR(100) NOT NULL,
  size VARCHAR(20) NOT NULL,
  price DECIMAL(10,2) NOT NULL CHECK (price >= 0),
  stock_quantity INT NOT NULL DEFAULT 0 CHECK (stock_quantity >= 0),
  UNIQUE KEY one_row_per_item_size (uniform_name, size)
);

CREATE TABLE orders (
  order_id INT AUTO_INCREMENT PRIMARY KEY,
  student_id VARCHAR(20) NOT NULL,
  student_name VARCHAR(100) NOT NULL,
  uniform_id INT NOT NULL,
  quantity INT NOT NULL CHECK (quantity > 0),
  total_price DECIMAL(10,2) NOT NULL,
  order_status ENUM('Pending','Processing','Completed') NOT NULL DEFAULT 'Pending',
  order_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (uniform_id) REFERENCES uniforms(uniform_id)   -- an uniform with orders cannot be deleted
);

-- Sample uniforms (one row per uniform + size)
INSERT INTO uniforms (uniform_name, size, price, stock_quantity) VALUES
('Polo Shirt','S',12.00,20),('Polo Shirt','M',12.00,30),('Polo Shirt','L',12.00,25),('Polo Shirt','XL',13.00,10),
('School Pants','S',18.00,15),('School Pants','M',18.00,20),('School Pants','L',18.00,15),
('School Skirt','S',16.00,12),('School Skirt','M',16.00,18),('School Skirt','L',16.00,10),
('PE T-Shirt','M',10.00,40),('PE T-Shirt','L',10.00,35),
('PE Jogging Pants','M',14.00,25),('School Necktie','One Size',5.00,50);

-- Sample orders
INSERT INTO orders (student_id, student_name, uniform_id, quantity, total_price, order_status) VALUES
('2026-001','Maria Santos',2,2,24.00,'Pending'),
('2026-002','Juan Dela Cruz',6,1,18.00,'Processing'),
('2026-003','Ana Reyes',9,2,32.00,'Completed'),
('2026-004','Pedro Garcia',11,3,30.00,'Pending');

-- Subtract the sample orders from stock so the numbers stay consistent
UPDATE uniforms u JOIN (SELECT uniform_id, SUM(quantity) q FROM orders GROUP BY uniform_id) o
  ON o.uniform_id = u.uniform_id SET u.stock_quantity = u.stock_quantity - o.q;
