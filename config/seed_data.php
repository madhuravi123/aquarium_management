<?php
function seed_dummy_data($conn) {

    // =====================================================================
    // 1. USERS - Admin (Harini) + Demo Customer
    // =====================================================================
    $admin_pass   = password_hash('harini@123', PASSWORD_DEFAULT);
    $staff_pass   = password_hash('staff@123', PASSWORD_DEFAULT);
    $customer_pass = password_hash('demo@123', PASSWORD_DEFAULT);

    $conn->query("INSERT INTO users (username, full_name, password, role, phone, address) VALUES
        ('harini',  'Harini',           '$admin_pass',    'admin',    '9876543210', 'St. Joseph College of Arts and Science, Manjakuppam, Cuddalore – 607001'),
        ('rajan',   'Rajan Murugan',    '$staff_pass',    'staff',    '9876543211', 'Cuddalore'),
        ('preethi', 'Preethi Kumari',   '$staff_pass',    'staff',    '9876543212', 'Cuddalore'),
        ('demo',    'Demo Customer',    '$customer_pass', 'customer', '9876500001', '12 Anna Nagar, Cuddalore – 607001')
    ");

    // =====================================================================
    // 2. TANKS (10 tanks)
    // =====================================================================
    $conn->query("INSERT INTO tanks (name, capacity, water_type, temperature, ph_level, status) VALUES
        ('Freshwater Display A',   500.00, 'freshwater', 26.00, 7.00, 'active'),
        ('Freshwater Display B',   300.00, 'freshwater', 25.00, 6.80, 'active'),
        ('Saltwater Reef Tank',    800.00, 'saltwater',  26.50, 8.20, 'active'),
        ('Saltwater Display',      600.00, 'saltwater',  25.50, 8.10, 'active'),
        ('Brackish Tank',          400.00, 'brackish',   24.50, 7.50, 'active'),
        ('Quarantine Tank',        150.00, 'freshwater', 27.00, 7.00, 'quarantine'),
        ('Maintenance Tank',       200.00, 'freshwater', 25.00, 7.20, 'maintenance'),
        ('Tropical Display',       450.00, 'freshwater', 27.50, 6.90, 'active'),
        ('Koi Pond Display',      2000.00, 'freshwater', 24.00, 7.30, 'active'),
        ('Planted Aquarium',       300.00, 'freshwater', 25.50, 6.70, 'active')
    ");

    // =====================================================================
    // 3. SUPPLIERS (Tamil Nadu based)
    // =====================================================================
    $conn->query("INSERT INTO suppliers (name, contact_person, phone, email, address) VALUES
        ('Cuddalore Aqua Traders',       'Murugesan Pillai',   '9842100001', 'cuddalore.aqua@gmail.com',   '45 Harbour Road, Cuddalore – 607001'),
        ('Chennai Marine Life',           'Rajkumar Selvam',    '9842100002', 'chennai.marine@gmail.com',   '12 Kasimir Lane, Chennai – 600001'),
        ('Pondicherry Fish World',        'Anbu Rajan',         '9842100003', 'pondy.fish@gmail.com',        'MG Road, Pondicherry – 605001'),
        ('Villupuram Aqua Farm',          'Kannan Subramani',   '9842100004', 'vpm.aquafarm@gmail.com',      'Nehru Street, Villupuram – 605601'),
        ('Neyveli Aquarium Supplies',     'Balu Krishnan',      '9842100005', 'neyveli.aquarium@gmail.com',  'Lignite City, Neyveli – 607801')
    ");

    // =====================================================================
    // 4. FISH (32 Indian variety fish with INR prices)
    // =====================================================================
    $conn->query("INSERT INTO fish (name, species, water_type, size, color, purchase_price, selling_price, stock_quantity, tank_id) VALUES
        ('Silver Arowana',          'Osteoglossum bicirrhosum',  'freshwater', 'Large (40cm)',   'Silver',         1500.00, 2500.00,   8,  8),
        ('Golden Arowana',          'Scleropages formosus',      'freshwater', 'Large (45cm)',   'Gold',          10000.00,15000.00,   3,  8),
        ('Flowerhorn Cichlid',      'Hybrid Cichlid',            'freshwater', 'Large (25cm)',   'Red/Gold/Pink',   800.00, 1500.00,  12,  2),
        ('Dragon Flowerhorn',       'Hybrid Cichlid SRD',        'freshwater', 'Large (30cm)',   'Red/Black',      2500.00, 4000.00,   5,  2),
        ('Koi Carp (Standard)',     'Cyprinus rubrofuscus',      'freshwater', 'Large (35cm)',   'Orange/White',    250.00,  500.00,  25,  9),
        ('Koi Carp (Premium)',      'Cyprinus rubrofuscus',      'freshwater', 'Large (45cm)',   'Red/White/Black',1000.00, 2000.00,  10,  9),
        ('Goldfish (Common)',       'Carassius auratus',         'freshwater', 'Small (10cm)',   'Orange/Gold',      35.00,   80.00,  50,  1),
        ('Goldfish (Fancy Oranda)', 'Carassius auratus',         'freshwater', 'Medium (15cm)',  'Red/White',       180.00,  350.00,  20,  1),
        ('Guppy (Male)',            'Poecilia reticulata',       'freshwater', 'Tiny (4cm)',     'Multi-color',      20.00,   50.00, 100,  2),
        ('Guppy (Fancy Tail)',      'Poecilia reticulata',       'freshwater', 'Tiny (5cm)',     'Rainbow',          60.00,  120.00,  60,  2),
        ('Molly (Black)',           'Poecilia sphenops',         'freshwater', 'Small (6cm)',    'Black',            25.00,   60.00,  40,  1),
        ('Molly (Dalmatian)',       'Poecilia sphenops',         'freshwater', 'Small (7cm)',    'White/Black',      35.00,   80.00,  30,  1),
        ('Betta Fish (Male)',       'Betta splendens',           'freshwater', 'Small (7cm)',    'Red/Blue',         80.00,  200.00,  25, 10),
        ('Betta Fish (Crown Tail)', 'Betta splendens',           'freshwater', 'Small (7cm)',    'Blue/Purple',     200.00,  400.00,  15, 10),
        ('Oscar Fish',              'Astronotus ocellatus',      'freshwater', 'Large (28cm)',   'Black/Orange',    150.00,  350.00,  18,  8),
        ('Angelfish',               'Pterophyllum scalare',      'freshwater', 'Medium (15cm)',  'Silver/Black',    100.00,  250.00,  22,  2),
        ('Neon Tetra',              'Paracheirodon innesi',      'freshwater', 'Tiny (3cm)',     'Blue/Red',         20.00,   50.00, 120,  1),
        ('Cardinal Tetra',          'Paracheirodon axelrodi',    'freshwater', 'Tiny (4cm)',     'Blue/Red',         30.00,   70.00,  80, 10),
        ('Tiger Barb',              'Puntigrus tetrazona',       'freshwater', 'Small (7cm)',    'Orange/Black',     25.00,   60.00,  60,  2),
        ('Cherry Barb',             'Pethia titteya',            'freshwater', 'Small (5cm)',    'Cherry Red',       20.00,   50.00,  50, 10),
        ('Swordtail',               'Xiphophorus hellerii',      'freshwater', 'Small (10cm)',   'Red/Green',        35.00,   80.00,  35,  1),
        ('Platy',                   'Xiphophorus maculatus',     'freshwater', 'Small (6cm)',    'Red/Yellow',       30.00,   70.00,  40,  1),
        ('Corydoras Catfish',       'Corydoras paleatus',        'freshwater', 'Small (6cm)',    'Brown/Silver',     70.00,  150.00,  30, 10),
        ('Discus Fish',             'Symphysodon aequifasciatus','freshwater', 'Large (20cm)',   'Blue/Red/Brown',  600.00, 1200.00,  10,  8),
        ('Peacock Cichlid',         'Aulonocara baenschi',       'freshwater', 'Medium (15cm)',  'Blue/Yellow',     180.00,  400.00,  20,  2),
        ('Pearl Gourami',           'Trichopodus leerii',        'freshwater', 'Small (12cm)',   'Pearl/Orange',     80.00,  180.00,  25, 10),
        ('Blue Gourami',            'Trichopodus trichopterus',  'freshwater', 'Small (12cm)',   'Blue/Silver',      50.00,  120.00,  30,  1),
        ('Clown Loach',             'Chromobotia macracanthus',  'freshwater', 'Medium (15cm)',  'Orange/Black',    150.00,  300.00,  15,  2),
        ('Rainbow Fish',            'Melanotaenia boesemani',    'freshwater', 'Small (10cm)',   'Blue/Orange',      90.00,  200.00,  20, 10),
        ('Zebra Danio',             'Danio rerio',               'freshwater', 'Tiny (5cm)',     'Blue/Silver',      15.00,   40.00,  70,  1),
        ('Suckermouth Catfish',     'Hypostomus plecostomus',    'freshwater', 'Medium (15cm)',  'Brown/Gray',      100.00,  250.00,  12,  8),
        ('Red Tail Black Shark',    'Epalzeorhynchos bicolor',   'freshwater', 'Small (12cm)',   'Black/Red',       130.00,  300.00,  10,  2)
    ");

    // =====================================================================
    // 5. CUSTOMERS (22 Tamil Nadu customers)
    // =====================================================================
    $conn->query("INSERT INTO customers (name, phone, email, address) VALUES
        ('Arjun Kumar',         '9841100001', 'arjun.kumar@gmail.com',       '12 Gandhi Road, Cuddalore – 607001'),
        ('Karthik Rajan',       '9841100002', 'karthik.rajan@gmail.com',     '34 Raja Street, Chidambaram – 608001'),
        ('Priya Sundaram',      '9841100003', 'priya.sundaram@gmail.com',    '56 Pondy Road, Pondicherry – 605001'),
        ('Harish Babu',         '9841100004', 'harish.babu@gmail.com',       '78 Bus Stand Road, Villupuram – 605601'),
        ('Nivetha Krishnan',    '9841100005', 'nivetha.k@gmail.com',         '90 Anna Nagar, Chennai – 600040'),
        ('Saravanan Murugan',   '9841100006', 'saravanan.m@gmail.com',       '23 Neyveli Township, Neyveli – 607801'),
        ('Meena Rajesh',        '9841100007', 'meena.rajesh@gmail.com',      '45 Sivaji Nagar, Cuddalore – 607001'),
        ('Vignesh Anand',       '9841100008', 'vignesh.anand@gmail.com',     '67 Natarajan Street, Chidambaram – 608001'),
        ('Lakshmi Devi',        '9841100009', 'lakshmi.devi@gmail.com',      '89 Marine Street, Pondicherry – 605002'),
        ('Rajkumar Selvaraj',   '9841100010', 'rajkumar.s@gmail.com',        '10 Cuddalore Port Road, Cuddalore – 607003'),
        ('Divya Mohan',         '9841100011', 'divya.mohan@gmail.com',       '22 T Nagar, Chennai – 600017'),
        ('Senthil Kumar',       '9841100012', 'senthil.k@gmail.com',         '44 Market Road, Villupuram – 605602'),
        ('Anitha Suresh',       '9841100013', 'anitha.suresh@gmail.com',     '66 Block 5, Neyveli – 607803'),
        ('Balamurugan Ravi',    '9841100014', 'balamurugan.r@gmail.com',     '88 Nethaji Street, Cuddalore – 607001'),
        ('Kavitha Chandran',    '9841100015', 'kavitha.c@gmail.com',         '11 East Car Street, Chidambaram – 608001'),
        ('Murugesan Pillai',    '9841100016', 'murugesan.p@gmail.com',       '33 White Town, Pondicherry – 605001'),
        ('Sangeetha Natarajan', '9841100017', 'sangeetha.n@gmail.com',       '55 GEB Colony, Villupuram – 605602'),
        ('Dhanushkodi Raj',     '9841100018', 'dhanushkodi.r@gmail.com',     '77 Velachery Road, Chennai – 600042'),
        ('Pavithra Shankar',    '9841100019', 'pavithra.s@gmail.com',        '99 Indra Nagar, Cuddalore – 607002'),
        ('Gopalan Subramanian', '9841100020', 'gopalan.s@gmail.com',         '14 School Road, Neyveli – 607801'),
        ('Ramya Venkatesh',     '9841100021', 'ramya.v@gmail.com',           '28 Natarajan Colony, Chidambaram – 608002'),
        ('Suresh Pandian',      '9841100022', 'suresh.pandian@gmail.com',    'MG Road, Pondicherry – 605001')
    ");

    // =====================================================================
    // 6. EMPLOYEES (Indian names, Indian salary in INR)
    // =====================================================================
    $conn->query("INSERT INTO employees (name, role, salary, phone, email, hire_date) VALUES
        ('Arunachalam R.',   'Store Manager',     25000.00, '9842200001', 'arunachalam@hariniaquarium.in',  '2023-06-01'),
        ('Kamala Sundari',   'Fish Specialist',   20000.00, '9842200002', 'kamala@hariniaquarium.in',       '2023-08-15'),
        ('Velmurugan K.',    'Tank Maintenance',  18000.00, '9842200003', 'velmurugan@hariniaquarium.in',   '2023-09-01'),
        ('Santhosh Kumar',   'Sales Associate',   15000.00, '9842200004', 'santhosh@hariniaquarium.in',     '2024-01-10'),
        ('Lakshmi Priya',    'Accountant',        22000.00, '9842200005', 'lakshmipriya@hariniaquarium.in', '2023-07-20')
    ");

    // =====================================================================
    // 7. PURCHASES (INR amounts)
    // =====================================================================
    $m1 = date('Y-m-d', strtotime('-45 days'));
    $m2 = date('Y-m-d', strtotime('-40 days'));
    $m3 = date('Y-m-d', strtotime('-30 days'));
    $m4 = date('Y-m-d', strtotime('-20 days'));
    $m5 = date('Y-m-d', strtotime('-10 days'));
    $m6 = date('Y-m-d', strtotime('-5 days'));

    $conn->query("INSERT INTO purchases (supplier_id, total_amount, purchase_date, status) VALUES
        (1, 25000.00,  '{$m1} 10:00:00', 'completed'),
        (2, 18000.00,  '{$m2} 14:00:00', 'completed'),
        (3,  9500.00,  '{$m3} 09:30:00', 'completed'),
        (4, 32000.00,  '{$m4} 11:00:00', 'completed'),
        (1, 15000.00,  '{$m5} 16:00:00', 'completed'),
        (5, 12000.00,  '{$m6} 10:00:00', 'completed')
    ");

    // =====================================================================
    // 8. PURCHASE ITEMS
    // =====================================================================
    $conn->query("INSERT INTO purchase_items (purchase_id, fish_id, item_name, quantity, unit_price, total_price) VALUES
        (1,  7, NULL,                    50, 35.00,   1750.00),
        (1,  9, NULL,                    80, 20.00,   1600.00),
        (1,  NULL, 'Premium Fish Food',  20, 450.00,  9000.00),
        (1,  NULL, 'Water Conditioner',  15, 350.00,  5250.00),
        (2,  3, NULL,                    10, 800.00,  8000.00),
        (2,  5, NULL,                    20, 250.00,  5000.00),
        (2,  NULL, 'Fish Medications',    5, 1000.00, 5000.00),
        (3,  17, NULL,                  100, 20.00,   2000.00),
        (3,  NULL, 'Filter Cartridges',  10, 550.00,  5500.00),
        (3,  NULL, 'Air Pumps',           4, 500.00,  2000.00),
        (4,  24, NULL,                    5, 600.00,  3000.00),
        (4,   1, NULL,                    5, 1500.00, 7500.00),
        (4,  16, NULL,                   15, 100.00,  1500.00),
        (4,  NULL, 'Aquarium Salt',      10, 200.00,  2000.00),
        (4,  NULL, 'Test Kits',           5, 800.00,  4000.00),
        (5,  13, NULL,                   20, 80.00,   1600.00),
        (5,  11, NULL,                   30, 25.00,    750.00),
        (5,  NULL, 'LED Grow Lights',     3, 1500.00, 4500.00),
        (6,  NULL, 'Aquarium Gravel',    10, 300.00,  3000.00),
        (6,  NULL, 'Live Plants Pack',    5, 800.00,  4000.00),
        (6,  NULL, 'CO2 Kit',             2, 2500.00, 5000.00)
    ");

    // =====================================================================
    // 9. SALES (INR, dynamic dates for live dashboard)
    // =====================================================================
    $today      = date('Y-m-d');
    $yesterday  = date('Y-m-d', strtotime('-1 day'));
    $two_days   = date('Y-m-d', strtotime('-2 days'));
    $three_days = date('Y-m-d', strtotime('-3 days'));
    $four_days  = date('Y-m-d', strtotime('-4 days'));
    $five_days  = date('Y-m-d', strtotime('-5 days'));
    $week_ago   = date('Y-m-d', strtotime('-7 days'));
    $ten_days   = date('Y-m-d', strtotime('-10 days'));
    $two_weeks  = date('Y-m-d', strtotime('-14 days'));
    $three_wk   = date('Y-m-d', strtotime('-21 days'));
    $month_ago  = date('Y-m-d', strtotime('-30 days'));

    $conn->query("INSERT INTO sales (customer_id, total_amount, tax_amount, final_amount, payment_method, sale_date) VALUES
        (1,   2450.00, 0.00,  2450.00, 'cash',   '$today 10:30:00'),
        (2,   1800.00, 0.00,  1800.00, 'upi',    '$today 12:15:00'),
        (3,   4200.00, 0.00,  4200.00, 'card',   '$today 14:00:00'),
        (4,   8500.00, 0.00,  8500.00, 'cash',   '$yesterday 11:00:00'),
        (5,   1500.00, 0.00,  1500.00, 'upi',    '$yesterday 14:30:00'),
        (6,  15000.00, 0.00, 15000.00, 'online', '$two_days 09:45:00'),
        (7,   3200.00, 0.00,  3200.00, 'cash',   '$two_days 16:00:00'),
        (8,   2400.00, 0.00,  2400.00, 'upi',    '$three_days 11:30:00'),
        (9,  25000.00, 0.00, 25000.00, 'card',   '$three_days 13:00:00'),
        (10,  1200.00, 0.00,  1200.00, 'cash',   '$four_days 10:00:00'),
        (11,  4800.00, 0.00,  4800.00, 'upi',    '$four_days 15:30:00'),
        (12,  9600.00, 0.00,  9600.00, 'online', '$five_days 09:00:00'),
        (13,  2100.00, 0.00,  2100.00, 'cash',   '$week_ago 10:30:00'),
        (14,  6000.00, 0.00,  6000.00, 'upi',    '$week_ago 13:00:00'),
        (15,  1400.00, 0.00,  1400.00, 'cash',   '$ten_days 11:00:00'),
        (16,  3500.00, 0.00,  3500.00, 'card',   '$ten_days 15:00:00'),
        (17, 18000.00, 0.00, 18000.00, 'online', '$two_weeks 10:00:00'),
        (18,  2800.00, 0.00,  2800.00, 'cash',   '$two_weeks 14:00:00'),
        (19,  1600.00, 0.00,  1600.00, 'upi',    '$three_wk 11:30:00'),
        (20,  5400.00, 0.00,  5400.00, 'cash',   '$three_wk 16:00:00'),
        (21, 12000.00, 0.00, 12000.00, 'card',   '$month_ago 10:00:00'),
        (22,  2200.00, 0.00,  2200.00, 'cash',   '$month_ago 14:30:00')
    ");

    // =====================================================================
    // 10. SALE ITEMS (matching the INR sales above)
    // =====================================================================
    $conn->query("INSERT INTO sale_items (sale_id, fish_id, quantity, unit_price, total_price) VALUES
        (1,  17, 20,   50.00,  1000.00),
        (1,   7, 10,   80.00,   800.00),
        (1,  30, 10,   40.00,   400.00),
        (1,  22,  1,   70.00,    70.00),
        (2,  13,  5,  200.00,  1000.00),
        (2,  14,  2,  400.00,   800.00),
        (3,   3,  1, 1500.00,  1500.00),
        (3,  15,  2,  350.00,   700.00),
        (3,  27,  1,  120.00,   120.00),
        (4,   4,  1, 4000.00,  4000.00),
        (4,  26,  3,  180.00,   540.00),
        (4,  23,  3,  150.00,   450.00),
        (5,  21,  5,   80.00,   400.00),
        (5,  20, 10,   50.00,   500.00),
        (5,  11, 10,   60.00,   600.00),
        (6,   2,  1,15000.00, 15000.00),
        (7,   5,  4,  500.00,  2000.00),
        (7,  16,  3,  250.00,   750.00),
        (7,  29,  1,  200.00,   200.00),
        (8,  13,  8,  200.00,  1600.00),
        (8,  14,  2,  400.00,   800.00),
        (9,   1,  1, 2500.00,  2500.00),
        (9,   6,  5, 2000.00, 10000.00),
        (9,  24,  2, 1200.00,  2400.00),
        (9,  25,  2,  400.00,   800.00),
        (10, 17, 10,   50.00,   500.00),
        (10, 30, 10,   40.00,   400.00),
        (10,  7,  2,   80.00,   160.00),
        (11,  3,  1, 1500.00,  1500.00),
        (11, 15,  1,  350.00,   350.00),
        (11, 16,  1,  250.00,   250.00),
        (11, 27,  2,  180.00,   360.00),
        (12,  4,  2, 4000.00,  8000.00),
        (12, 28,  2,  300.00,   600.00),
        (12, 25,  1,  400.00,   400.00),
        (13, 21,  5,   80.00,   400.00),
        (13, 22,  5,   70.00,   350.00),
        (13, 11, 10,   60.00,   600.00),
        (14,  5,  5,  500.00,  2500.00),
        (14, 16,  5,  250.00,  1250.00),
        (14, 25,  3,  400.00,  1200.00),
        (15, 17, 10,   50.00,   500.00),
        (15,  9, 20,   50.00,  1000.00),
        (16,  3,  1, 1500.00,  1500.00),
        (16, 15,  2,  350.00,   700.00),
        (16, 16,  1,  250.00,   250.00),
        (17,  1,  1, 2500.00,  2500.00),
        (17,  6,  5, 2000.00, 10000.00),
        (17, 24,  1, 1200.00,  1200.00),
        (17, 29,  2,  200.00,   400.00),
        (18,  5,  3,  500.00,  1500.00),
        (18, 28,  2,  300.00,   600.00),
        (18, 19,  5,   60.00,   300.00),
        (19, 17, 10,   50.00,   500.00),
        (19, 18, 10,   70.00,   700.00),
        (19,  9, 10,   50.00,   500.00),
        (20,  4,  1, 4000.00,  4000.00),
        (20, 27,  2,  180.00,   360.00),
        (20, 23,  2,  150.00,   300.00),
        (21,  2,  1,15000.00, 15000.00),
        (21,  1,  1, 2500.00,  2500.00),
        (21, 25,  1,  400.00,   400.00),
        (22, 13,  5,  200.00,  1000.00),
        (22, 14,  2,  400.00,   800.00),
        (22, 26,  2,  180.00,   360.00)
    ");

    // =====================================================================
    // 11. WATER QUALITY RECORDS
    // =====================================================================
    $conn->query("INSERT INTO water_quality (tank_id, recorded_by, ph_level, temperature, ammonia_level, nitrate_level, nitrite_level, recorded_at) VALUES
        (1, 1, 7.00, 26.00, 0.00, 10.00, 0.00, '$yesterday 08:00:00'),
        (2, 1, 6.80, 25.00, 0.00, 15.00, 0.00, '$yesterday 08:15:00'),
        (3, 1, 8.20, 26.50, 0.00,  5.00, 0.00, '$yesterday 08:30:00'),
        (4, 2, 8.10, 25.50, 0.02,  8.00, 0.00, '$yesterday 08:45:00'),
        (5, 2, 7.50, 24.50, 0.00, 12.00, 0.00, '$yesterday 09:00:00'),
        (8, 2, 6.90, 27.50, 0.00, 10.00, 0.00, '$yesterday 09:15:00'),
        (9, 1, 7.30, 24.00, 0.00,  8.00, 0.00, '$yesterday 09:30:00'),
        (10, 2, 6.70, 25.50, 0.00, 12.00, 0.00, '$yesterday 09:45:00'),
        (1, 1, 7.10, 25.80, 0.00, 12.00, 0.00, '$three_days 08:00:00'),
        (3, 1, 8.15, 26.30, 0.01,  6.00, 0.00, '$three_days 08:30:00')
    ");

    // =====================================================================
    // 12. FEEDING SCHEDULES
    // =====================================================================
    $conn->query("INSERT INTO feeding_schedule (tank_id, food_type, feeding_time, quantity_grams) VALUES
        (1,  'Tropical Flakes',        '08:00:00', 15.00),
        (1,  'Tropical Flakes',        '18:00:00', 15.00),
        (2,  'Cichlid Pellets',        '08:30:00', 20.00),
        (2,  'Cichlid Pellets',        '18:30:00', 20.00),
        (3,  'Marine Flakes',          '09:00:00', 12.00),
        (3,  'Frozen Brine Shrimp',    '17:00:00',  8.00),
        (4,  'Marine Pellets',         '09:00:00', 10.00),
        (8,  'Arowana Sticks',         '08:00:00', 25.00),
        (8,  'Bloodworms (Frozen)',    '17:30:00', 15.00),
        (9,  'Koi Pellets (Large)',    '08:00:00', 80.00),
        (9,  'Wheat Germ Pellets',     '17:00:00', 60.00),
        (10, 'Micro Pellets',          '08:30:00',  5.00),
        (10, 'Daphnia (Frozen)',       '17:30:00',  3.00)
    ");

    // =====================================================================
    // 13. FEEDING LOGS
    // =====================================================================
    $conn->query("INSERT INTO feeding_logs (tank_id, fed_by, food_type, fed_at) VALUES
        (1,  1, 'Tropical Flakes',     '$today 08:05:00'),
        (2,  1, 'Cichlid Pellets',     '$today 08:35:00'),
        (3,  2, 'Marine Flakes',       '$today 09:05:00'),
        (8,  2, 'Arowana Sticks',      '$today 08:10:00'),
        (9,  1, 'Koi Pellets (Large)', '$today 08:15:00'),
        (10, 2, 'Micro Pellets',       '$today 08:35:00'),
        (1,  1, 'Tropical Flakes',     '$yesterday 08:00:00'),
        (1,  1, 'Tropical Flakes',     '$yesterday 18:00:00'),
        (2,  2, 'Cichlid Pellets',     '$yesterday 08:30:00'),
        (3,  1, 'Marine Flakes',       '$yesterday 09:00:00'),
        (3,  2, 'Frozen Brine Shrimp', '$yesterday 17:00:00'),
        (8,  1, 'Arowana Sticks',      '$yesterday 08:05:00'),
        (9,  2, 'Koi Pellets (Large)', '$yesterday 08:10:00')
    ");

    // =====================================================================
    // 14. FISH HEALTH
    // =====================================================================
    $conn->query("INSERT INTO fish_health (fish_id, tank_id, disease_name, symptoms, treatment, status, recorded_at) VALUES
        (5,  3, 'Ich (White Spot)',      'White spots on fins and body', 'Raised temperature to 30°C, added aquarium salt',        'recovering', '$three_days 10:00:00'),
        (7,  1, 'Fin Rot',              'Edges of fins appear ragged and frayed', 'Melafix treatment, water quality improvement',   'healthy',    '$week_ago 09:00:00'),
        (24, 8, 'Head Hole Disease',    'Small pits forming near head region', 'Metronidazole treatment, vitamin supplements',      'sick',       '$yesterday 14:00:00'),
        (1,  8, 'Swim Bladder Disorder','Fish swimming sideways', 'Fasting for 48 hours, feeding frozen peas',                     'recovering', '$two_days 11:00:00'),
        (3,  2, 'Bacterial Infection',  'Red streaks on body, lethargy', 'Antibiotics in water, isolation recommended',            'sick',       '$yesterday 09:00:00')
    ");

    // =====================================================================
    // 15. INVENTORY (INR prices, Indian brands)
    // =====================================================================
    $conn->query("INSERT INTO inventory (item_name, category, quantity, unit_price, reorder_level) VALUES
        ('Tropical Fish Flakes 100g (Taiyo)',    'food',      25,  150.00,  5),
        ('Koi Pellets Large 500g (Hikari)',       'food',      15,  450.00,  3),
        ('Arowana Bites 250g (Ocean Free)',       'food',      10,  800.00,  3),
        ('Frozen Bloodworms Pack (Sera)',          'food',      30,  120.00, 10),
        ('Frozen Brine Shrimp Pack',              'food',      20,  100.00, 10),
        ('Anti-Ich Medication (Tetra)',           'medicine',   8,  350.00,  3),
        ('Melafix 250ml (API)',                   'medicine',   6,  450.00,  3),
        ('Water Conditioner 500ml (Prime)',       'medicine',  12,  250.00,  5),
        ('KH/GH Test Kit (API)',                 'equipment',  10,  650.00,  3),
        ('Air Pump (Sobo WP-3500)',               'equipment',   5,  750.00,  2),
        ('Filter Cartridge Pack x3 (Boyu)',       'equipment',  20,  280.00,  5),
        ('Aquarium Heater 200W (Resun)',           'equipment',   4,  900.00,  2),
        ('LED Aquarium Light 60cm (Odyssea)',      'equipment',   3, 1800.00,  2),
        ('CO2 Diffuser (Azoo)',                   'equipment',   5,  650.00,  2),
        ('Fish Net Medium',                       'other',       8,   80.00,  3),
        ('Aquarium Gravel 5kg (Natural)',         'other',      15,  250.00,  5)
    ");

    // =====================================================================
    // 16. EXPENSES (INR, Tamil Nadu business context)
    // =====================================================================
    $this_month = date('Y-m');
    $last_month = date('Y-m', strtotime('-1 month'));
    $conn->query("INSERT INTO expenses (category, amount, description, expense_date) VALUES
        ('Rent',        15000.00, 'Monthly shop rent – Cuddalore market',         '{$this_month}-01'),
        ('Electricity',  3500.00, 'TANGEDCO electricity bill – monthly',          '{$this_month}-05'),
        ('Salary',     100000.00, 'Staff salaries for the month (5 employees)',   '{$this_month}-01'),
        ('Maintenance',  2500.00, 'Filter replacements and tank repairs',         '{$this_month}-10'),
        ('Supplies',     8000.00, 'Packing bags, salt, water chemicals',          '{$this_month}-08'),
        ('Transport',    1500.00, 'Fish transport from Chennai supplier',          '{$this_month}-12'),
        ('Rent',        15000.00, 'Monthly shop rent – Cuddalore market',         '{$last_month}-01'),
        ('Electricity',  3200.00, 'TANGEDCO electricity bill – monthly',          '{$last_month}-05'),
        ('Salary',     100000.00, 'Staff salaries for the month (5 employees)',   '{$last_month}-01'),
        ('Maintenance',  4500.00, 'Emergency pump and filter replacement',        '{$last_month}-18'),
        ('Supplies',     6000.00, 'Cleaning supplies, packing materials',         '{$last_month}-12'),
        ('Other',        2000.00, 'Hoardings and social media promotion',         '{$last_month}-20')
    ");

    // =====================================================================
    // 17. MAINTENANCE LOGS
    // =====================================================================
    $next_week     = date('Y-m-d', strtotime('+7 days'));
    $next_two_weeks= date('Y-m-d', strtotime('+14 days'));
    $conn->query("INSERT INTO maintenance_log (tank_id, performed_by, activity_type, description, maintenance_date, next_scheduled_date) VALUES
        (1,  1, 'Water Change',    '30% water change completed, gravel vacuumed',             '$yesterday 07:00:00',  '$next_week'),
        (2,  1, 'Filter Cleaning', 'Cleaned filter media, replaced cartridge',                '$yesterday 07:30:00',  '$next_two_weeks'),
        (3,  2, 'Full Service',    'Complete tank service – water, glass, filter',            '$two_days 08:00:00',   '$next_week'),
        (8,  2, 'Glass Cleaning',  'Algae removal from glass panels',                         '$three_days 09:00:00', '$next_week'),
        (9,  1, 'Equipment Check', 'Checked pump, aeration and filter functionality',         '$week_ago 08:00:00',   '$next_two_weeks'),
        (10, 2, 'Water Change',    '25% water change, CO2 refill done',                      '$four_days 09:00:00',  '$next_week'),
        (4,  1, 'Full Service',    'Salt level adjusted, glass cleaned, protein skimmer serviced', '$five_days 08:00:00', '$next_two_weeks')
    ");

    // =====================================================================
    // 18. NOTIFICATIONS
    // =====================================================================
    $conn->query("INSERT INTO notifications (type, message, is_read, created_at) VALUES
        ('low_stock',   'Low stock alert: Golden Arowana – only 3 remaining',           0, '$today 08:00:00'),
        ('low_stock',   'Low stock alert: Dragon Flowerhorn – only 5 remaining',        0, '$today 08:00:00'),
        ('low_stock',   'Low stock alert: Discus Fish – only 10 remaining',             0, '$today 08:00:00'),
        ('maintenance', 'Maintenance due: Freshwater Display A – Water Change',         0, '$yesterday 07:00:00'),
        ('maintenance', 'Maintenance due: Saltwater Reef Tank – Full Service',          0, '$two_days 08:00:00'),
        ('feeding',     'Evening feeding completed for all active tanks',               1, '$yesterday 17:00:00'),
        ('other',       'New fish shipment from Cuddalore Aqua Traders arriving tomorrow', 0, '$yesterday 10:00:00'),
        ('low_stock',   'Low stock alert: Silver Arowana – only 8 remaining',           1, '$three_days 08:00:00'),
        ('maintenance', 'Maintenance completed: Koi Pond Display – Equipment Check',    1, '$week_ago 09:30:00'),
        ('other',       'Water quality parameters within normal range for all tanks',   1, '$two_days 10:00:00')
    ");
}
?>
