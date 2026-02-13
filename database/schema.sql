-- Database Schema for Aquarium Management System

CREATE DATABASE IF NOT EXISTS aquarium_db;
USE aquarium_db;

-- 1. Users Table (Admin & Staff)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff') DEFAULT 'staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default Admin User (Password: admin123)
-- hash: $2y$10$8Wk... (Use password_hash('admin123', PASSWORD_DEFAULT) in PHP)
INSERT INTO users (username, password, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'); 

-- 2. Tanks Table
CREATE TABLE IF NOT EXISTS tanks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    capacity DECIMAL(10,2) NOT NULL, -- in liters
    water_type ENUM('freshwater', 'saltwater', 'brackish') NOT NULL,
    temperature DECIMAL(5,2),
    ph_level DECIMAL(4,2),
    status ENUM('active', 'maintenance', 'quarantine') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Suppliers Table
CREATE TABLE IF NOT EXISTS suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    contact_person VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. Fish Categories/Species (Optional but good for normalization, sticking to user req: Fish Table)
CREATE TABLE IF NOT EXISTS fish (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    species VARCHAR(100),
    water_type ENUM('freshwater', 'saltwater', 'brackish'),
    size VARCHAR(50),
    color VARCHAR(50),
    purchase_price DECIMAL(10,2),
    selling_price DECIMAL(10,2),
    stock_quantity INT DEFAULT 0,
    tank_id INT,
    image VARCHAR(255),
    FOREIGN KEY (tank_id) REFERENCES tanks(id) ON DELETE SET NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 5. Customers Table
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 6. Employees Table
CREATE TABLE IF NOT EXISTS employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    role VARCHAR(50),
    salary DECIMAL(10,2),
    phone VARCHAR(20),
    email VARCHAR(100),
    hire_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 7. Purchases Table (Buying from Suppliers)
CREATE TABLE IF NOT EXISTS purchases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT,
    total_amount DECIMAL(10,2),
    purchase_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'completed') DEFAULT 'completed',
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
);

-- 8. Purchase Items
CREATE TABLE IF NOT EXISTS purchase_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    purchase_id INT,
    fish_id INT, -- Can be null if it's a supply
    item_name VARCHAR(100), -- For non-fish items
    quantity INT,
    unit_price DECIMAL(10,2),
    total_price DECIMAL(10,2),
    FOREIGN KEY (purchase_id) REFERENCES purchases(id),
    FOREIGN KEY (fish_id) REFERENCES fish(id)
);

-- 9. Sales Table (Selling to Customers)
CREATE TABLE IF NOT EXISTS sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT,
    total_amount DECIMAL(10,2),
    tax_amount DECIMAL(10,2) DEFAULT 0.00,
    final_amount DECIMAL(10,2),
    payment_method ENUM('cash', 'card', 'online'),
    sale_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id)
);

-- 10. Sale Items
CREATE TABLE IF NOT EXISTS sale_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT,
    fish_id INT,
    quantity INT,
    unit_price DECIMAL(10,2),
    total_price DECIMAL(10,2),
    FOREIGN KEY (sale_id) REFERENCES sales(id),
    FOREIGN KEY (fish_id) REFERENCES fish(id)
);

-- 11. Water Quality Monitoring
CREATE TABLE IF NOT EXISTS water_quality (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tank_id INT,
    recorded_by INT, -- User ID
    ph_level DECIMAL(4,2),
    temperature DECIMAL(5,2),
    ammonia_level DECIMAL(5,2),
    nitrate_level DECIMAL(5,2),
    nitrite_level DECIMAL(5,2),
    recorded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tank_id) REFERENCES tanks(id),
    FOREIGN KEY (recorded_by) REFERENCES users(id)
);

-- 12. Feeding Schedule
CREATE TABLE IF NOT EXISTS feeding_schedule (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tank_id INT,
    food_type VARCHAR(100),
    feeding_time TIME,
    quantity_grams DECIMAL(5,2),
    FOREIGN KEY (tank_id) REFERENCES tanks(id)
);

-- 13. Feeding Logs
CREATE TABLE IF NOT EXISTS feeding_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tank_id INT,
    fed_by INT, -- User ID
    food_type VARCHAR(100),
    fed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tank_id) REFERENCES tanks(id),
    FOREIGN KEY (fed_by) REFERENCES users(id)
);

-- 14. Fish Health
CREATE TABLE IF NOT EXISTS fish_health (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fish_id INT,
    tank_id INT,
    disease_name VARCHAR(100),
    symptoms TEXT,
    treatment TEXT,
    status ENUM('sick', 'recovering', 'healthy', 'dead') DEFAULT 'sick',
    recorded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (fish_id) REFERENCES fish(id),
    FOREIGN KEY (tank_id) REFERENCES tanks(id)
);

-- 15. Inventory (Supplies/Equipment)
CREATE TABLE IF NOT EXISTS inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_name VARCHAR(100) NOT NULL,
    category ENUM('food', 'medicine', 'equipment', 'other'),
    quantity INT DEFAULT 0,
    unit_price DECIMAL(10,2),
    reorder_level INT DEFAULT 5,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 16. Expenses
CREATE TABLE IF NOT EXISTS expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(100), -- Rent, Salary, Electricity
    amount DECIMAL(10,2),
    description TEXT,
    expense_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 17. Maintenance Log
CREATE TABLE IF NOT EXISTS maintenance_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tank_id INT,
    performed_by INT, -- User ID
    activity_type VARCHAR(100), -- Water Change, Filter Cleaning
    description TEXT,
    maintenance_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    next_scheduled_date DATE,
    FOREIGN KEY (tank_id) REFERENCES tanks(id),
    FOREIGN KEY (performed_by) REFERENCES users(id)
);

-- 18. Notifications
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('low_stock', 'maintenance', 'feeding', 'other'),
    message TEXT,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
