<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
if ($_SESSION['role'] === 'customer') {
    header("Location: shop.php");
    exit();
}
require_once 'config/db_connect.php';
require_once 'includes/error_logger.php';

// ─── ADD CUSTOMER ─────────────────────────────────────────────────────────────
if (isset($_POST['save'])) {
    $name    = trim($_POST['name'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city    = trim($_POST['city'] ?? '');
    $pincode = trim($_POST['pincode'] ?? '');

    if (empty($name)) {
        $_SESSION['message']  = "Customer name is required.";
        $_SESSION['msg_type'] = "danger";
        header("Location: customers.php");
        exit();
    }

    $stmt = $conn->prepare(
        "INSERT INTO customers (name, phone, email, address, city, pincode) VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("ssssss", $name, $phone, $email, $address, $city, $pincode);

    if ($stmt->execute()) {
        $_SESSION['message']  = "Customer '{$name}' added successfully!";
        $_SESSION['msg_type'] = "success";
    } else {
        log_error("Add customer failed: " . $stmt->error, __FILE__, __LINE__);
        $_SESSION['message']  = "Error adding customer. Please try again.";
        $_SESSION['msg_type'] = "danger";
    }
    $stmt->close();
    header("Location: customers.php");
    exit();
}

// ─── DELETE CUSTOMER ──────────────────────────────────────────────────────────
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    if ($id <= 0) {
        $_SESSION['message']  = "Invalid customer ID.";
        $_SESSION['msg_type'] = "danger";
        header("Location: customers.php");
        exit();
    }

    // Count how many sales are linked to this customer
    $chk = $conn->prepare("SELECT COUNT(*) AS cnt FROM sales WHERE customer_id = ?");
    $chk->bind_param("i", $id);
    $chk->execute();
    $sales_count = (int) $chk->get_result()->fetch_assoc()['cnt'];
    $chk->close();

    $conn->begin_transaction();
    try {
        if ($sales_count > 0) {
            /*
             * WHY: sales.customer_id has a FK referencing customers.id.
             * RULE: MySQL prevents deleting a parent row that has child rows.
             * RESOLUTION: Delete child sale_items first → then sales → then customer.
             */

            // Step 1: Collect all sale IDs for this customer
            $sid_stmt = $conn->prepare("SELECT id FROM sales WHERE customer_id = ?");
            $sid_stmt->bind_param("i", $id);
            $sid_stmt->execute();
            $sid_res  = $sid_stmt->get_result();
            $sale_ids = [];
            while ($r = $sid_res->fetch_assoc()) {
                $sale_ids[] = (int) $r['id'];
            }
            $sid_stmt->close();

            // Step 2: Delete sale_items for those sales
            if (!empty($sale_ids)) {
                $placeholders = implode(',', array_fill(0, count($sale_ids), '?'));
                $types        = str_repeat('i', count($sale_ids));
                $di = $conn->prepare("DELETE FROM sale_items WHERE sale_id IN ($placeholders)");
                $di->bind_param($types, ...$sale_ids);
                $di->execute();
                $di->close();
            }

            // Step 3: Delete the sales
            $ds = $conn->prepare("DELETE FROM sales WHERE customer_id = ?");
            $ds->bind_param("i", $id);
            $ds->execute();
            $ds->close();
        }

        // Step 4: Delete the customer
        $dc = $conn->prepare("DELETE FROM customers WHERE id = ?");
        $dc->bind_param("i", $id);
        $dc->execute();
        $dc->close();

        $conn->commit();

        $_SESSION['message']  = $sales_count > 0
            ? "Customer and {$sales_count} linked sale(s) deleted successfully."
            : "Customer deleted successfully.";
        $_SESSION['msg_type'] = "warning";

    } catch (Exception $e) {
        $conn->rollback();
        log_error("Delete customer ID={$id} failed: " . $e->getMessage(), __FILE__, __LINE__);

        // User-level message (simple)
        $_SESSION['message']  = "Unable to delete this customer. Please contact support if the issue persists.";
        $_SESSION['msg_type'] = "danger";
    }

    header("Location: customers.php");
    exit();
}
?>
