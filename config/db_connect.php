<?php
// ============================================================
//  DATABASE CREDENTIALS
//  XAMPP (local):  host=localhost, user=root, pass='', dbname=aquarium_db
//  InfinityFree:   host=sqlXXX.infinityfree.com, user=epiz_XXXXXXX
//                  pass=<your panel password>, dbname=epiz_XXXXXXX_aquarium
// ============================================================
$host   = "sql308.infinityfree.com";   // ← InfinityFree: change to sqlXXX.infinityfree.com
$user   = "if0_41199079";             // ← InfinityFree: change to epiz_XXXXXXX
$pass   = "Partha2002m";                          // ← InfinityFree: change to your DB password
$dbname = "if0_41199079_aquarium_db";  // ← InfinityFree: change to epiz_XXXXXXX_aquariu

// Connect directly to the named database (works on both XAMPP and shared hosting)
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    error_log("[Aquarium] DB connection failed: " . $conn->connect_error);
    http_response_code(503);
    die('<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Service Unavailable</title>
<style>body{font-family:sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;background:#0a3d62;color:#fff;}
.box{text-align:center;padding:2rem;max-width:480px;}h2{color:#f39c12;margin-bottom:.5rem;}p{opacity:.8;line-height:1.6;}</style></head>
<body><div class="box"><h2>&#128019; Database Unavailable</h2>
<p>Could not connect to the database.<br>Please check your credentials in <strong>config/db_connect.php</strong> and ensure the database exists.</p></div></body></html>');
}
$conn->set_charset('utf8mb4');

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
            image_url TEXT,
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

    // Seed dummy data on first run (run_safe_seed also creates the demo customer)
    require_once __DIR__ . '/seed.php';
    run_safe_seed($conn);
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
        )",
        "ALTER TABLE fish ADD COLUMN IF NOT EXISTS image_url TEXT AFTER image"
    ];
    foreach ($migrations as $sql) {
        $conn->query($sql); // Ignore errors (column may already exist)
    }

    // Populate fish image_url if column exists but values are empty
    $img_check = $conn->query("SELECT COUNT(*) as c FROM fish WHERE image_url IS NOT NULL AND image_url != ''");
    if ($img_check && (int)$img_check->fetch_assoc()['c'] === 0) {
        $fish_images = [
            'Silver Arowana'          => 'https://upload.wikimedia.org/wikipedia/commons/thumb/b/bf/Osteoglossum_bicirrhosum.JPG/300px-Osteoglossum_bicirrhosum.JPG',
            'Golden Arowana'          => 'https://upload.wikimedia.org/wikipedia/commons/thumb/b/b4/Arowana.jpg/300px-Arowana.jpg',
            'Flowerhorn Cichlid'      => 'https://upload.wikimedia.org/wikipedia/commons/thumb/6/68/Flowerhorn_cichlid.jpg/300px-Flowerhorn_cichlid.jpg',
            'Dragon Flowerhorn'       => 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e1/Flowerhorn.jpg/300px-Flowerhorn.jpg',
            'Koi Carp (Standard)'     => 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/10/Ojiya_Nishikigoi_no_Sato_ac_%283%29.jpg/300px-Ojiya_Nishikigoi_no_Sato_ac_%283%29.jpg',
            'Koi Carp (Premium)'      => 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/10/Ojiya_Nishikigoi_no_Sato_ac_%283%29.jpg/300px-Ojiya_Nishikigoi_no_Sato_ac_%283%29.jpg',
            'Goldfish (Common)'       => 'https://upload.wikimedia.org/wikipedia/commons/thumb/6/65/Gold_fish1.jpg/300px-Gold_fish1.jpg',
            'Goldfish (Fancy Oranda)' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/6/6b/Orange_Oranda.jpg/300px-Orange_Oranda.jpg',
            'Guppy (Male)'            => 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/a2/Guppy_pho_0048.jpg/300px-Guppy_pho_0048.jpg',
            'Guppy (Fancy Tail)'      => 'https://upload.wikimedia.org/wikipedia/commons/thumb/f/f4/Cobra_Guppy_%28Poecilia_reticulata%29.JPG/300px-Cobra_Guppy_%28Poecilia_reticulata%29.JPG',
            'Molly (Black)'           => 'https://upload.wikimedia.org/wikipedia/commons/thumb/7/73/Black_Molly.jpg/300px-Black_Molly.jpg',
            'Molly (Dalmatian)'       => 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/16/Poecilia_latipinna.jpg/300px-Poecilia_latipinna.jpg',
            'Betta Fish (Male)'       => 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/10/HM_Orange_M_Sarawut.jpg/300px-HM_Orange_M_Sarawut.jpg',
            'Betta Fish (Crown Tail)' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/42/Betta_splendens_male_crowntail.png/300px-Betta_splendens_male_crowntail.png',
            'Oscar Fish'              => 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/08/Astronotus_ocellatus.jpg/300px-Astronotus_ocellatus.jpg',
            'Angelfish'               => 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/11/Pterophyllum_scalare_Natural_History_Museum_University_of_Pisa.jpg/300px-Pterophyllum_scalare_Natural_History_Museum_University_of_Pisa.jpg',
            'Neon Tetra'              => 'https://upload.wikimedia.org/wikipedia/commons/thumb/9/97/Neonsalmler_Paracheirodon_innesi.jpg/300px-Neonsalmler_Paracheirodon_innesi.jpg',
            'Cardinal Tetra'          => 'https://upload.wikimedia.org/wikipedia/commons/thumb/3/3e/Paracheirodon_cardinalis.JPG/300px-Paracheirodon_cardinalis.JPG',
            'Tiger Barb'              => 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/12/Tiger_Barb_700.jpg/300px-Tiger_Barb_700.jpg',
            'Cherry Barb'             => 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/10/Cyprinidae_Puntius_titteya_3.jpg/300px-Cyprinidae_Puntius_titteya_3.jpg',
            'Swordtail'               => 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/14/Xiphophorus_helleri_03.jpg/300px-Xiphophorus_helleri_03.jpg',
            'Platy'                   => 'https://upload.wikimedia.org/wikipedia/commons/thumb/b/b6/Xiphophorus_maculatus_in_aqarium.JPG/300px-Xiphophorus_maculatus_in_aqarium.JPG',
            'Corydoras Catfish'       => 'https://upload.wikimedia.org/wikipedia/commons/thumb/6/69/Corydoras_paleatus_by_NiKo.jpg/300px-Corydoras_paleatus_by_NiKo.jpg',
            'Discus Fish'             => 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/83/Symphysodon_aequifasciatus_-_Karlsruhe_Zoo_04.jpg/300px-Symphysodon_aequifasciatus_-_Karlsruhe_Zoo_04.jpg',
            'Peacock Cichlid'         => 'https://upload.wikimedia.org/wikipedia/commons/thumb/f/fb/Aulonocara_hansbaenschi_RB2.jpg/300px-Aulonocara_hansbaenschi_RB2.jpg',
            'Pearl Gourami'           => 'https://upload.wikimedia.org/wikipedia/commons/thumb/9/97/Trichopodus_leerii.jpg/300px-Trichopodus_leerii.jpg',
            'Blue Gourami'            => 'https://upload.wikimedia.org/wikipedia/commons/thumb/f/f0/Trichopodus_trichopterus_%283_spot_gourami%2C_Philippines%29_04.jpg/300px-Trichopodus_trichopterus_%283_spot_gourami%2C_Philippines%29_04.jpg',
            'Clown Loach'             => 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e3/Chromobotia_macracanthus_01.jpg/300px-Chromobotia_macracanthus_01.jpg',
            'Rainbow Fish'            => 'https://upload.wikimedia.org/wikipedia/commons/thumb/9/94/Melanotaenia_boesemani_-_Karlsruhe_Zoo_01.jpg/300px-Melanotaenia_boesemani_-_Karlsruhe_Zoo_01.jpg',
            'Zebra Danio'             => 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/ac/Zebrafisch.jpg/300px-Zebrafisch.jpg',
            'Suckermouth Catfish'     => 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/c1/Hypostomus_plecostomus_-_Rapha%C3%ABl_Covain.png/300px-Hypostomus_plecostomus_-_Rapha%C3%ABl_Covain.png',
            'Red Tail Black Shark'    => 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/01/Epalzeorhynchos_bicolor1.jpg/300px-Epalzeorhynchos_bicolor1.jpg',
            'Clownfish'               => 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/ad/Amphiprion_ocellaris_%28Clown_anemonefish%29_by_Nick_Hobgood.jpg/300px-Amphiprion_ocellaris_%28Clown_anemonefish%29_by_Nick_Hobgood.jpg',
            'Blue Tang'               => 'https://upload.wikimedia.org/wikipedia/commons/thumb/5/5b/Paracanthurus-hepatus-paletten-doktorfisch.jpg/300px-Paracanthurus-hepatus-paletten-doktorfisch.jpg',
            'Lionfish'                => 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/8d/Common_lion_fish_Pterois_volitans.jpg/300px-Common_lion_fish_Pterois_volitans.jpg',
            'Mandarin Dragonet'       => 'https://upload.wikimedia.org/wikipedia/commons/thumb/2/2d/Synchiropus_splendidus_2_Luc_Viatour.jpg/300px-Synchiropus_splendidus_2_Luc_Viatour.jpg',
            'Flame Angelfish'         => 'https://upload.wikimedia.org/wikipedia/commons/thumb/5/5b/Flame_angelfish_%28Centropyge_loricula%29.jpg/300px-Flame_angelfish_%28Centropyge_loricula%29.jpg',
            'Royal Gramma'            => 'https://upload.wikimedia.org/wikipedia/commons/thumb/b/bc/Gramma_loreto_01.jpg/300px-Gramma_loreto_01.jpg',
            'Archer Fish'             => 'https://upload.wikimedia.org/wikipedia/commons/thumb/d/d6/Toxotes_jaculatrix.jpg/300px-Toxotes_jaculatrix.jpg',
            'Figure Eight Puffer'     => 'https://upload.wikimedia.org/wikipedia/commons/thumb/f/f7/Figure8pufferfish.jpg/300px-Figure8pufferfish.jpg',
            'Mono Argentus'           => 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/03/Monodactylus_argenteus_173820575.jpg/300px-Monodactylus_argenteus_173820575.jpg',
            'Green Spotted Puffer'    => 'https://upload.wikimedia.org/wikipedia/commons/thumb/b/b4/Green_Spotted_puffer.jpg/300px-Green_Spotted_puffer.jpg',
            'Mudskipper'              => 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/ab/Schlammspringer_Periophthalmus_sp.jpg/300px-Schlammspringer_Periophthalmus_sp.jpg',
        ];
        $img_stmt = $conn->prepare("UPDATE fish SET image_url = ? WHERE name = ?");
        if ($img_stmt) {
            foreach ($fish_images as $fname => $furl) {
                $img_stmt->bind_param('ss', $furl, $fname);
                $img_stmt->execute();
            }
            $img_stmt->close();
        }
    }
}

// Ensure uploads directory exists
$uploads_dir = __DIR__ . '/../uploads';
if (!is_dir($uploads_dir)) {
    mkdir($uploads_dir, 0755, true);
}

// ============================================================================
//  DEMO MODE: Automatic database reset for shared-hosting deployment.
//  This resets the database to fresh demo state every 10 minutes.
//  To DISABLE: simply comment out or delete the line below.
// ============================================================================
require_once __DIR__ . '/auto_reset.php';
?>
