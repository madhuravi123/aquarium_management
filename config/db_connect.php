<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'aquarium_db';

// Connect without database first to create it if needed
$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) {
    error_log("[Aquarium] DB connection failed: " . $conn->connect_error);
    http_response_code(503);
    die('<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Service Unavailable</title>
<style>body{font-family:sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;background:#0a3d62;color:#fff;}
.box{text-align:center;padding:2rem;max-width:480px;}h2{color:#f39c12;margin-bottom:.5rem;}p{opacity:.8;line-height:1.6;}</style></head>
<body><div class="box"><h2>&#128019; Database Unavailable</h2>
<p>The database server is not responding.<br>Please ensure the <strong>XAMPP MySQL</strong> service is running, then refresh this page.</p></div></body></html>');
}

// Create database if not exists
$conn->query("CREATE DATABASE IF NOT EXISTS `$dbname`");
$conn->select_db($dbname);

// Check if tables exist (use users table as indicator)
$table_check = $conn->query("SHOW TABLES LIKE 'users'");
$is_first_run = ($table_check->num_rows == 0);

if ($is_first_run) {
    $statements = [
        "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            full_name VARCHAR(100),
            password VARCHAR(255) NOT NULL,
            role ENUM('admin', 'staff', 'customer') DEFAULT 'customer',
            phone VARCHAR(20),
            address TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS tanks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            capacity DECIMAL(10,2) NOT NULL,
            water_type ENUM('freshwater', 'saltwater', 'brackish') NOT NULL,
            temperature DECIMAL(5,2),
            ph_level DECIMAL(4,2),
            status ENUM('active', 'maintenance', 'quarantine') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS suppliers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            contact_person VARCHAR(100),
            phone VARCHAR(20),
            email VARCHAR(100),
            address TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS fish (
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
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (tank_id) REFERENCES tanks(id) ON DELETE SET NULL
        )",
        "CREATE TABLE IF NOT EXISTS customers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            phone VARCHAR(20),
            email VARCHAR(100),
            address TEXT,
            city VARCHAR(100),
            pincode VARCHAR(10),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS employees (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            role VARCHAR(50),
            salary DECIMAL(10,2),
            phone VARCHAR(20),
            email VARCHAR(100),
            hire_date DATE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS purchases (
            id INT AUTO_INCREMENT PRIMARY KEY,
            supplier_id INT,
            total_amount DECIMAL(10,2),
            purchase_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            status ENUM('pending', 'completed') DEFAULT 'completed',
            FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
        )",
        "CREATE TABLE IF NOT EXISTS purchase_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            purchase_id INT,
            fish_id INT,
            item_name VARCHAR(100),
            quantity INT,
            unit_price DECIMAL(10,2),
            total_price DECIMAL(10,2),
            FOREIGN KEY (purchase_id) REFERENCES purchases(id),
            FOREIGN KEY (fish_id) REFERENCES fish(id)
        )",
        "CREATE TABLE IF NOT EXISTS sales (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_id INT,
            user_id INT,
            total_amount DECIMAL(10,2),
            tax_amount DECIMAL(10,2) DEFAULT 0.00,
            final_amount DECIMAL(10,2),
            payment_method ENUM('cash', 'card', 'online', 'upi') DEFAULT 'cash',
            notes TEXT,
            sale_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (customer_id) REFERENCES customers(id),
            FOREIGN KEY (user_id) REFERENCES users(id)
        )",
        "CREATE TABLE IF NOT EXISTS sale_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            sale_id INT,
            fish_id INT,
            quantity INT,
            unit_price DECIMAL(10,2),
            total_price DECIMAL(10,2),
            FOREIGN KEY (sale_id) REFERENCES sales(id),
            FOREIGN KEY (fish_id) REFERENCES fish(id)
        )",
        "CREATE TABLE IF NOT EXISTS water_quality (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tank_id INT,
            recorded_by INT,
            ph_level DECIMAL(4,2),
            temperature DECIMAL(5,2),
            ammonia_level DECIMAL(5,2),
            nitrate_level DECIMAL(5,2),
            nitrite_level DECIMAL(5,2),
            recorded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (tank_id) REFERENCES tanks(id),
            FOREIGN KEY (recorded_by) REFERENCES users(id)
        )",
        "CREATE TABLE IF NOT EXISTS feeding_schedule (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tank_id INT,
            food_type VARCHAR(100),
            feeding_time TIME,
            quantity_grams DECIMAL(5,2),
            FOREIGN KEY (tank_id) REFERENCES tanks(id)
        )",
        "CREATE TABLE IF NOT EXISTS feeding_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tank_id INT,
            fed_by INT,
            food_type VARCHAR(100),
            fed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (tank_id) REFERENCES tanks(id),
            FOREIGN KEY (fed_by) REFERENCES users(id)
        )",
        "CREATE TABLE IF NOT EXISTS fish_health (
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
        )",
        "CREATE TABLE IF NOT EXISTS inventory (
            id INT AUTO_INCREMENT PRIMARY KEY,
            item_name VARCHAR(100) NOT NULL,
            category ENUM('food', 'medicine', 'equipment', 'other'),
            quantity INT DEFAULT 0,
            unit_price DECIMAL(10,2),
            reorder_level INT DEFAULT 5,
            last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS expenses (
            id INT AUTO_INCREMENT PRIMARY KEY,
            category VARCHAR(100),
            amount DECIMAL(10,2),
            description TEXT,
            expense_date DATE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS maintenance_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tank_id INT,
            performed_by INT,
            activity_type VARCHAR(100),
            description TEXT,
            maintenance_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            next_scheduled_date DATE,
            FOREIGN KEY (tank_id) REFERENCES tanks(id),
            FOREIGN KEY (performed_by) REFERENCES users(id)
        )",
        "CREATE TABLE IF NOT EXISTS notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            type ENUM('low_stock', 'maintenance', 'feeding', 'other'),
            message TEXT,
            is_read BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS cart (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            fish_id INT NOT NULL,
            quantity INT DEFAULT 1,
            added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (fish_id) REFERENCES fish(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_fish (user_id, fish_id)
        )"
    ];

    foreach ($statements as $sql) {
        if (!$conn->query($sql)) {
            error_log("[Aquarium] Table creation error: " . $conn->error);
            http_response_code(500);
            die('<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Setup Error</title>
<style>body{font-family:sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;background:#0a3d62;color:#fff;}
.box{text-align:center;padding:2rem;max-width:480px;}h2{color:#f39c12;margin-bottom:.5rem;}p{opacity:.8;line-height:1.6;}</style></head>
<body><div class="box"><h2>&#9888; Setup Error</h2>
<p>Database initialisation failed. Please check that MySQL is running and your user has CREATE privileges, then refresh.</p></div></body></html>');
        }
    }

    // Seed dummy data on first run
    require_once __DIR__ . '/seed_data.php';
    seed_dummy_data($conn);
} else {
    // Run migrations for existing databases (errors are silently ignored)
    $migrations = [
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS full_name VARCHAR(100) AFTER username",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS phone VARCHAR(20) AFTER full_name",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS address TEXT AFTER phone",
        "ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'staff', 'customer') DEFAULT 'customer'",
        "ALTER TABLE sales ADD COLUMN IF NOT EXISTS user_id INT AFTER customer_id",
        "ALTER TABLE sales ADD COLUMN IF NOT EXISTS notes TEXT AFTER payment_method",
        "ALTER TABLE sales MODIFY COLUMN payment_method ENUM('cash', 'card', 'online', 'upi') DEFAULT 'cash'",
        "ALTER TABLE customers ADD COLUMN IF NOT EXISTS city VARCHAR(100) AFTER address",
        "ALTER TABLE customers ADD COLUMN IF NOT EXISTS pincode VARCHAR(10) AFTER city",
        "CREATE TABLE IF NOT EXISTS cart (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            fish_id INT NOT NULL,
            quantity INT DEFAULT 1,
            added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (fish_id) REFERENCES fish(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_fish (user_id, fish_id)
        )"
    ];
    foreach ($migrations as $sql) {
        $conn->query($sql); // Ignore errors (column may already exist)
    }
}

// Ensure uploads directory exists
$uploads_dir = __DIR__ . '/../uploads';
if (!is_dir($uploads_dir)) {
    mkdir($uploads_dir, 0755, true);
}
?>
