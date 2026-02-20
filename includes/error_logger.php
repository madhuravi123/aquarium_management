<?php
/**
 * Structured Error Logger — Harini Aquarium Management System
 *
 * THREE LEVELS of error information:
 *  1. User-level  → Friendly flash message (set by caller in $_SESSION)
 *  2. Admin-level → Returned as string from log_error() for display
 *  3. Developer   → Written to logs/error.log with full context
 *
 * Usage:
 *   require_once 'includes/error_logger.php';
 *   log_error("FK constraint on customers.id", __FILE__, __LINE__);
 */
function log_error(string $message, string $file = '', int $line = 0, string $user_role = null): void
{
    // Resolve role from session if not provided
    if ($user_role === null) {
        $user_role = $_SESSION['role'] ?? 'guest';
    }

    $log_dir = __DIR__ . '/../logs';
    if (!is_dir($log_dir)) {
        @mkdir($log_dir, 0755, true);
    }

    $timestamp  = date('Y-m-d H:i:s');
    $user_id    = $_SESSION['user_id'] ?? 'N/A';
    $username   = $_SESSION['username'] ?? 'guest';
    $file_name  = $file ? basename($file) : 'unknown';

    // Developer / Support log entry
    $entry = sprintf(
        "[%s] ROLE:%-8s USER:%-15s(ID:%-4s) %s:%d  →  %s\n",
        $timestamp,
        $user_role,
        $username,
        $user_id,
        $file_name,
        $line,
        $message
    );

    @file_put_contents($log_dir . '/error.log', $entry, FILE_APPEND | LOCK_EX);
}

/**
 * Returns a structured admin-level explanation for common DB errors.
 *
 * @param  string $mysql_error  Raw mysqli error string
 * @param  string $context      Short description of what was being done
 * @return string               Admin-readable explanation
 */
function explain_db_error(string $mysql_error, string $context = ''): string
{
    $msg = $context ? "[$context] " : '';

    if (strpos($mysql_error, 'foreign key constraint fails') !== false) {
        $msg .= 'A foreign key constraint prevented this operation. '
              . 'The record is referenced by related tables (e.g., sales, orders). '
              . 'Delete or reassign dependent records first.';
    } elseif (strpos($mysql_error, 'Duplicate entry') !== false) {
        $msg .= 'A duplicate record already exists. '
              . 'Ensure unique values for fields like username, email, or phone.';
    } elseif (strpos($mysql_error, 'Data too long') !== false) {
        $msg .= 'Input data exceeds the allowed field length. Shorten the value and retry.';
    } else {
        $msg .= 'Database error: ' . $mysql_error;
    }

    return $msg;
}

// ─── CSRF HELPERS ─────────────────────────────────────────────────────────────

/**
 * Returns the session CSRF token, generating one if it does not yet exist.
 */
function csrf_token(): string
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Renders a hidden <input> carrying the CSRF token — embed inside every form.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="'
         . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Validates the submitted CSRF token against the session token.
 * Terminates with HTTP 403 and a friendly page on failure.
 */
function csrf_check(): void
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    $submitted = $_POST['csrf_token'] ?? '';
    if (
        empty($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $submitted)
    ) {
        http_response_code(403);
        die('<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Security Error</title>
<style>body{font-family:sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;background:#0a3d62;color:#fff;}
.box{text-align:center;padding:2rem;max-width:480px;}h2{color:#f39c12;}a{color:#14d2c8;}</style></head>
<body><div class="box"><h2>&#128274; Security Check Failed</h2>
<p>Your session may have expired or the request was invalid.<br>
<a href="javascript:history.back()">&#8592; Go back and try again</a></p></div></body></html>');
    }
}
?>
