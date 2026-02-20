# Harini Aquarium & Fish Shop
### Aquarium Management and Fish Selling System
**Final Year College Project** — St. Joseph's College of Arts and Science, Cuddalore

---

## Project Overview

**Harini Aquarium & Fish Shop** is a comprehensive web-based aquarium management and fish selling system developed as a Final Year College Project. The system represents a realistic aquarium business located in **Manjakuppam, Cuddalore, Tamil Nadu, India**.

The application supports two distinct user roles:
- **Admin Panel** — Full business management for the shop owner (Harini)
- **Customer Panel** — Online fish browsing, cart, and purchasing for customers

All prices are in **Indian Rupees (₹)**, and the system reflects real-world Indian aquarium market practices.

---

## Features

### Admin Panel Features
- **Dashboard** — Real-time KPIs: total fish stock, active tanks, today's sales (₹), unread alerts
- **Fish Management** — Add, edit, delete fish with image upload; species, pricing, stock tracking
- **Tank Management** — Manage freshwater, saltwater, and brackish tanks; edit capacity, water type, status
- **Fish Health** — Log diseases, symptoms, treatments; track recovery status per fish
- **Water Quality** — Record pH, temperature, ammonia, nitrate, nitrite for each tank
- **Feeding** — Log feeding sessions; manage feeding schedules per tank
- **Maintenance** — Log tank maintenance activities with next-scheduled-date reminders
- **Sales** — Create new sales, view history, print professional tax invoices in ₹
- **Purchases** — Record fish and supply purchases from suppliers; auto-update stock
- **Customers** — Manage customer database (22+ Tamil Nadu customers pre-seeded)
- **Suppliers** — Manage Tamil Nadu-based fish suppliers
- **Employees** — Track staff, roles, and salaries in ₹
- **Expenses** — Record business expenses (Rent, Electricity, Salary, etc.) in ₹
- **Reports** — Monthly Revenue vs Expenses, Net Profit, Low Stock alerts, 30-day sales trend
- **Notifications** — Auto-generated alerts for low stock, maintenance due, feeding schedules

### Customer Panel Features
- **Shop** — Browse 32+ Indian fish varieties with images, pricing in ₹, stock status
- **Search & Filter** — Search by name/species/color; filter by water type; sort by price or stock
- **Cart** — Add fish to cart, update quantities, remove items
- **Checkout** — Enter delivery details, choose payment method (Cash/UPI/Card/Online), place order
- **My Orders** — View full order history with itemized invoice details
- **My Profile** — Update name, phone, delivery address; change password

---

## Technology Stack

| Technology | Version | Purpose |
|---|---|---|
| PHP | 7.4+ / 8.x | Server-side scripting |
| MySQL | 5.7+ / 8.x | Database |
| XAMPP | Latest | Local development server (Apache + MySQL + PHP) |
| Bootstrap | 5.3 | Responsive UI framework |
| Font Awesome | 6.0 | Icons |
| jQuery | 3.6 | JavaScript utilities |

---

## Installation Steps (Step-by-Step for Beginners)

### Prerequisites
- **XAMPP** installed (download from https://www.apachefriends.org)
- A modern web browser (Chrome, Firefox, Edge)

### Step 1 — Install and Start XAMPP
1. Download and install XAMPP for Windows
2. Open the **XAMPP Control Panel**
3. Start **Apache** (click Start)
4. Start **MySQL** (click Start)
5. Both status lights should turn green

### Step 2 — Copy Project Files
1. Open Windows Explorer
2. Navigate to: `C:\xampp\htdocs\`
3. Create a new folder: `aquarium`
4. Copy all project files into `C:\xampp\htdocs\aquarium\`

   Your folder structure should look like:
   ```
   C:\xampp\htdocs\aquarium\
   ├── index.php
   ├── dashboard.php
   ├── shop.php
   ├── config/
   │   ├── db_connect.php
   │   └── seed_data.php
   ├── includes/
   │   ├── header.php
   │   ├── footer.php
   │   ├── sidebar.php
   │   └── customer_header.php
   ├── assets/
   │   └── css/style.css
   └── uploads/
   ```

### Step 3 — First Run (Automatic Database Setup)
1. Open your web browser
2. Go to: **http://localhost/aquarium/**
3. The system will **automatically**:
   - Create the database (`aquarium_db`)
   - Create all required tables
   - Seed 300+ rows of realistic Indian dummy data
   - Create default admin and customer accounts
4. You will see the **Login Page** — the database is ready!

> **Note:** No manual SQL import or phpMyAdmin setup is required.

### Step 4 — Login and Use
- Use the credentials shown on the login page (see below)
- Admin redirects to the **Admin Dashboard**
- Customer redirects to the **Customer Shop**

---

## First-Run Automatic Setup

On the very first visit, `config/db_connect.php` checks if the `users` table exists. If not, it:

1. Creates the MySQL database `aquarium_db`
2. Creates **19 tables**: users, tanks, suppliers, fish, customers, employees, purchases, purchase_items, sales, sale_items, water_quality, feeding_schedule, feeding_logs, fish_health, inventory, expenses, maintenance_log, notifications, cart
3. Runs `config/seed_data.php` which inserts:
   - 4 users (1 admin + 2 staff + 1 demo customer)
   - 10 tanks (Freshwater, Saltwater, Brackish, Koi Pond, Planted Aquarium)
   - 5 Tamil Nadu suppliers
   - **32 Indian fish** with realistic ₹ prices
   - **22 Tamil Nadu customers**
   - 5 employees with Indian salaries
   - 6 purchase records with 21 purchase items
   - **22 sales records** with dynamic dates (today, yesterday, etc.)
   - 63 sale items with INR amounts
   - 10 water quality records
   - 13 feeding schedules + 13 feeding logs
   - 5 fish health records
   - 16 inventory items
   - 12 expense records in ₹
   - 7 maintenance logs
   - 10 notifications

This ensures the **Dashboard always shows live data** regardless of when the project is run.

---

## Default Login Credentials

### Admin Account (Owner)

| Field | Value |
|---|---|
| **Name** | Harini |
| **Role** | Admin |
| **Username** | `harini` |
| **Password** | `harini@123` |
| **Redirects to** | Admin Dashboard |

### Customer Account (Demo)

| Field | Value |
|---|---|
| **Name** | Demo Customer |
| **Role** | Customer |
| **Username** | `demo` |
| **Password** | `demo@123` |
| **Redirects to** | Customer Shop |

> **Security Note:** All passwords are stored using PHP's `password_hash()` with bcrypt — never in plain text.

---

## How to Use the Admin Panel

### Login
1. Go to `http://localhost/aquarium/`
2. Enter Username: `harini`, Password: `harini@123`
3. Click **Login** — you are redirected to the Admin Dashboard

### Dashboard
- View **Total Fish Stock**, **Active Tanks**, **Today's Sales (₹)**, **Alerts**
- Use **Quick Actions**: New Sale, New Purchase, Log Feeding

### Managing Fish
- Click **Fish Stock** in the sidebar
- Click **Add New Fish** to add a fish with image, species, tank, price, and stock
- Click **Edit** to modify any fish
- Click **Delete** to remove a fish

### Creating a New Sale
- Click **Sales** → **New Sale**
- Select a customer (or leave as Walk-in)
- Add fish items with quantity
- The total auto-calculates in ₹
- Select payment method (Cash / UPI / Card / Online)
- Click **Complete Sale**
- Click **Invoice** to print a professional tax invoice

### Viewing Reports
- Click **Reports** in the sidebar
- View Monthly Revenue, Expenses, and Net Profit in ₹
- Check Low Stock alerts (fish with ≤ 5 units)
- See last 30 days of daily sales

### Managing Tanks
- Click **Tanks** → **Add Tank** to add freshwater/saltwater/brackish tanks
- Click **Edit** to update tank parameters

---

## How to Use the Customer Panel

### Login
1. Go to `http://localhost/aquarium/`
2. Enter Username: `demo`, Password: `demo@123`
3. Click **Login** — you are redirected to the Customer Shop

### Browsing Fish
- The shop shows all 32+ available fish with images and ₹ prices
- Use the **Search bar** to find fish by name, species, or color
- Filter by **Water Type** (Freshwater/Saltwater/Brackish)
- Sort by **Name**, **Price**, or **Stock availability**

### Adding to Cart
- Click **Add** on any fish card
- Adjust the quantity before adding
- Cart count updates in the navigation bar

### Placing an Order
1. Click the **Cart** icon in the top navigation
2. Review items, update quantities, or remove items
3. Click **Proceed to Checkout**
4. Enter delivery name, phone, and address
5. Choose payment method
6. Click **Place Order** — order is confirmed!

### Viewing Orders
- Click **My Orders** in the navigation
- Click **View** on any order to see the full itemized details

### Managing Profile
- Click **Profile** in the navigation
- Update name, phone number, and delivery address
- Change your password securely

---

## Project Structure

```
aquarium_management/
├── index.php                  ← Login page (entry point)
├── auth_login.php             ← Login form handler
├── logout.php                 ← Session destroy + redirect
├── dashboard.php              ← Admin dashboard
├── shop.php                   ← Customer fish catalog
├── cart.php                   ← Customer shopping cart
├── cart_action.php            ← Cart CRUD handler
├── checkout.php               ← Order placement
├── my_orders.php              ← Customer order history
├── my_profile.php             ← Customer profile management
├── tanks.php / tanks_edit.php ← Tank management
├── tanks_action.php
├── fish.php / fish_edit.php   ← Fish inventory management
├── fish_action.php
├── fish_health.php            ← Fish health tracking
├── health_action.php
├── water_quality.php          ← Water quality monitoring
├── water_quality_action.php
├── feeding.php                ← Feeding logs & schedules
├── feeding_action.php
├── maintenance.php            ← Maintenance logging
├── maintenance_action.php
├── sales.php / sales_new.php  ← Sales management
├── sales_action.php
├── invoice_print.php          ← Printable tax invoice
├── purchases.php / purchases_new.php  ← Purchase management
├── purchases_action.php
├── customers.php              ← Customer database
├── customers_action.php
├── suppliers.php              ← Supplier management
├── suppliers_action.php
├── employees.php              ← Employee management
├── employees_action.php
├── expenses.php               ← Business expenses
├── expenses_action.php
├── reports.php                ← Analytics & reports
├── notifications.php          ← System notifications
├── config/
│   ├── db_connect.php         ← DB connection + auto-schema
│   └── seed_data.php          ← Dummy data seeder
├── includes/
│   ├── header.php             ← Admin HTML head
│   ├── footer.php             ← Admin JS includes
│   ├── sidebar.php            ← Admin sidebar navigation
│   └── customer_header.php   ← Customer navbar
├── assets/
│   └── css/style.css         ← Custom styles
└── uploads/                   ← Fish images (auto-created)
```

---

## Sample Data Included

### Fish Varieties (32 Indian species)
Arowana (Silver & Golden), Flowerhorn Cichlid (Regular & Dragon), Koi Carp (Standard & Premium), Goldfish (Common & Fancy Oranda), Guppy (Male & Fancy Tail), Molly (Black & Dalmatian), Betta Fish (Male & Crown Tail), Oscar Fish, Angelfish, Neon Tetra, Cardinal Tetra, Tiger Barb, Cherry Barb, Swordtail, Platy, Corydoras Catfish, Discus Fish, Peacock Cichlid, Pearl Gourami, Blue Gourami, Clown Loach, Rainbow Fish, Zebra Danio, Suckermouth Catfish, Red Tail Black Shark

### Tamil Nadu Customers (22)
Arjun Kumar, Karthik Rajan, Priya Sundaram, Harish Babu, Nivetha Krishnan, Saravanan Murugan, Meena Rajesh, Vignesh Anand, Lakshmi Devi, Rajkumar Selvaraj, Divya Mohan, Senthil Kumar, Anitha Suresh, Balamurugan Ravi, Kavitha Chandran, Murugesan Pillai, Sangeetha Natarajan, Dhanushkodi Raj, Pavithra Shankar, Gopalan Subramanian, Ramya Venkatesh, Suresh Pandian

### Tamil Nadu Suppliers (5)
Cuddalore Aqua Traders, Chennai Marine Life, Pondicherry Fish World, Villupuram Aqua Farm, Neyveli Aquarium Supplies

---

## Screenshots

> *(Add screenshots here for your project report)*

| Screenshot | Description |
|---|---|
| `screenshots/login.png` | Login page with credentials |
| `screenshots/dashboard.png` | Admin dashboard with KPIs |
| `screenshots/fish_stock.png` | Fish management grid |
| `screenshots/shop.png` | Customer fish catalog |
| `screenshots/cart.png` | Customer shopping cart |
| `screenshots/checkout.png` | Order placement form |
| `screenshots/my_orders.png` | Customer order history |
| `screenshots/reports.png` | Business analytics |
| `screenshots/invoice.png` | Printable tax invoice |

---

## Troubleshooting

**Problem:** Login page shows but login fails
**Solution:** Make sure MySQL is running in XAMPP Control Panel

**Problem:** Blank page or PHP errors
**Solution:** Enable error display in XAMPP's php.ini: `display_errors = On`

**Problem:** Fish images not showing
**Solution:** Check that the `uploads/` folder exists and has write permissions

**Problem:** "Access denied for user 'root'"
**Solution:** Open `config/db_connect.php` and update the `$pass` variable if your MySQL root has a password

**Problem:** Data does not appear (empty tables)
**Solution:** The database may already exist from a previous run. Open phpMyAdmin (`http://localhost/phpmyadmin`), drop the `aquarium_db` database, then reload the project

---

## Project Information

| Field | Detail |
|---|---|
| **Project Title** | Aquarium Management and Fish Selling System |
| **Business Name** | Harini Aquarium & Fish Shop |
| **Business Location** | St. Joseph's College of Arts and Science, Manjakuppam, Cuddalore – 607001, Tamil Nadu, India |
| **Currency** | Indian Rupees (₹) |
| **Academic Level** | Final Year B.Sc. / BCA / B.Tech College Project |
| **Technology Stack** | PHP + MySQL + Bootstrap 5 + XAMPP |

---

*Developed as a Final Year College Project — St. Joseph's College of Arts and Science, Cuddalore, Tamil Nadu.*
