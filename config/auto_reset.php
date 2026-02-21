<?php
/**
 * ============================================================================
 *  AUTOMATIC DEMO RESET SYSTEM — config/auto_reset.php
 * ============================================================================
 *
 *  Purpose : Resets the entire database to a fresh demo state every N minutes.
 *            Designed for shared-hosting deployment (InfinityFree, 000webhost)
 *            where cron jobs are unavailable.
 *
 *  How it works (lazy / request-triggered):
 *    1. A tiny table `demo_reset` stores the last-reset timestamp.
 *    2. On EVERY HTTP request (included via db_connect.php), we check:
 *         "Has DEMO_RESET_MINUTES elapsed since last reset?"
 *    3. If NO  → do nothing (costs only 1 lightweight SELECT per request).
 *    4. If YES →
 *         a. Disable FOREIGN_KEY_CHECKS  (lets us TRUNCATE in any order)
 *         b. TRUNCATE every data table    (instant, no row-by-row delete)
 *         c. Re-seed all demo data        (via seed_data.php)
 *         d. Re-create the demo customer  (demo / demo@123)
 *         e. Update the reset timestamp
 *         f. Re-enable FOREIGN_KEY_CHECKS
 *
 *  Why TRUNCATE instead of DELETE?
 *    - TRUNCATE resets AUTO_INCREMENT and is much faster.
 *    - With FK checks disabled, order does not matter.
 *
 *  How to disable this system:
 *    - Comment out ONE line at the bottom of config/db_connect.php:
 *        // require_once __DIR__ . '/auto_reset.php';
 *
 *  College Viva Keywords:
 *    Lazy evaluation · TRUNCATE vs DELETE · FOREIGN_KEY_CHECKS ·
 *    Self-healing database · Request-triggered maintenance · REPLACE INTO
 * ============================================================================
 */

// ── CONFIGURATION ────────────────────────────────────────────────────────────
// Change this value to adjust how often the database resets.
// Default: 10 minutes.  Set to a very large number to effectively disable.
if (!defined('DEMO_RESET_MINUTES')) {
    define('DEMO_RESET_MINUTES', 10);
}

// ── COMPLETE LIST OF TABLES TO TRUNCATE ──────────────────────────────────────
// Order does not matter because we disable FK checks before truncating.
// NOTE: 'demo_reset' is intentionally NOT in this list — it must survive resets.
if (!defined('DEMO_TABLES_DEFINED')) {
    define('DEMO_TABLES_DEFINED', true);
    $DEMO_TABLES = [
        // Child tables (have foreign keys pointing to other tables)
        'cart',
        'sale_items',
        'purchase_items',
        'feeding_logs',
        'feeding_schedule',
        'fish_health',
        'water_quality',
        'maintenance_log',
        'notifications',
        'expenses',
        // Parent-level tables
        'sales',
        'purchases',
        'inventory',
        'employees',
        'fish',
        'customers',
        'suppliers',
        'tanks',
        'users',
    ];
}

/**
 * Main function: checks timer and resets if interval has passed.
 *
 * @param mysqli $conn  Active database connection (from db_connect.php)
 */
function demo_auto_reset($conn)
{
    global $DEMO_TABLES;

    // ── Step 1: Create the tracking table if it does not exist ────────────
    // This table holds exactly ONE row with the last-reset timestamp.
    $conn->query("
        CREATE TABLE IF NOT EXISTS demo_reset (
            id                  INT         NOT NULL DEFAULT 1 PRIMARY KEY,
            last_reset          DATETIME    NOT NULL,
            reset_interval_min  INT         NOT NULL DEFAULT " . DEMO_RESET_MINUTES . "
        )
    ");

    // ── Step 2: Check if reset interval has passed (using MySQL time) ───
    // We do the time comparison entirely inside MySQL to avoid PHP/MySQL
    // timezone mismatches (common on shared hosting where PHP and MySQL
    // may use different timezone settings).
    $result = $conn->query("
        SELECT TIMESTAMPDIFF(MINUTE, last_reset, NOW()) AS elapsed,
               reset_interval_min
        FROM demo_reset WHERE id = 1
    ");

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $elapsed  = (int) $row['elapsed'];
        $interval = (int) $row['reset_interval_min'];

        if ($elapsed < $interval) {
            // Not time yet — exit early (fast path: 1 SELECT per request)
            return;
        }
    }
    // If no row exists, this is the very first load — seed and set timestamp.

    // ══════════════════════════════════════════════════════════════════════
    //  RESET IN PROGRESS
    // ══════════════════════════════════════════════════════════════════════

    // ── Step 4: Disable foreign key checks ───────────────────────────────
    // This lets us TRUNCATE tables in any order without FK errors.
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");

    // ── Step 5: Truncate every data table ────────────────────────────────
    foreach ($DEMO_TABLES as $table) {
        $conn->query("TRUNCATE TABLE `$table`");
    }

    // ── Step 6: Re-enable foreign key checks ─────────────────────────────
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");

    // ── Step 7: Re-insert all seed/demo data ─────────────────────────────
    require_once __DIR__ . '/seed_data.php';
    seed_dummy_data($conn);

    // ── Step 8: Create the demo customer login account ───────────────────
    // Credentials: username = demo, password = demo@123, role = customer
    $demo_pass = password_hash('demo@123', PASSWORD_DEFAULT);
    $stmt = $conn->prepare(
        "INSERT INTO users (username, full_name, password, role, phone, address)
         VALUES (?, ?, ?, 'customer', '9876500000', 'Demo Address, Cuddalore – 607001')"
    );
    $name = 'demo';
    $full = 'Demo Customer';
    $stmt->bind_param('sss', $name, $full, $demo_pass);
    $stmt->execute();
    $stmt->close();

    // ── Step 9: Update the reset timestamp ───────────────────────────────
    // REPLACE INTO = INSERT if missing, UPDATE if exists (key = id = 1).
    // We use MySQL's NOW() (not PHP's date()) to avoid timezone mismatches.
    $conn->query(
        "REPLACE INTO demo_reset (id, last_reset, reset_interval_min)
         VALUES (1, NOW(), " . DEMO_RESET_MINUTES . ")"
    );
}

// ── AUTO-EXECUTE on include ──────────────────────────────────────────────────
// This line makes the reset check happen automatically when db_connect.php
// includes this file.  Remove this line to make it manual-only.
demo_auto_reset($conn);
