<?php
/**
 * ============================================================================
 *  SAFE DATABASE SEEDER — config/seed.php
 * ============================================================================
 *
 *  Purpose : Inserts demo/mock data ONLY when the database tables are empty.
 *            Safe to call on every page load — never creates duplicate rows.
 *
 *  How it works:
 *    1. Checks the 'users' table row count (the first table seeded).
 *    2. If zero rows → calls seed_dummy_data() from seed_data.php.
 *    3. Always ensures the demo customer account exists for login.
 *
 *  Why is this safe?
 *    - The row-count check makes this function "idempotent":
 *      calling it 1 time or 100 times produces the same database state.
 *    - seed_dummy_data() uses INSERT (not INSERT IGNORE), so we must only
 *      call it when the table is truly empty.
 *
 *  College Viva Keywords:
 *    Idempotent seeding · guard clause · password_hash · prepared statements
 * ============================================================================
 */

function run_safe_seed($conn)
{
    // ── Guard: check if data already exists ──────────────────────────────
    // We use the 'users' table as the indicator because it is the very
    // first table populated by seed_dummy_data().
    $result = $conn->query("SELECT COUNT(*) AS cnt FROM users");
    if (!$result) return;                       // table might not exist yet

    $count = (int) $result->fetch_assoc()['cnt'];

    if ($count === 0) {
        // ── Tables are empty — run the full seeder ───────────────────────
        require_once __DIR__ . '/seed_data.php';
        seed_dummy_data($conn);
    }

    // ── Ensure the demo customer account always exists ───────────────────
    // This user is needed so evaluators can log in with demo / demo@123
    // even if someone deleted the account during a demo session.
    $demo_check = $conn->query("SELECT id FROM users WHERE username = 'demo'");
    if ($demo_check && $demo_check->num_rows === 0) {
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
    }
}
