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

if (isset($_POST['save'])) {
    $category = $_POST['category'];
    $amount = floatval($_POST['amount']);
    $date = $_POST['expense_date'];
    $desc = trim($_POST['description'] ?? '');

    $stmt = $conn->prepare("INSERT INTO expenses (category, amount, expense_date, description) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sdss", $category, $amount, $date, $desc);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Expense added!";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['message'] = "Error adding expense.";
        $_SESSION['msg_type'] = "danger";
    }
    $stmt->close();
    header("Location: expenses.php");
    exit();
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM expenses WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    $_SESSION['message'] = "Expense deleted!";
    $_SESSION['msg_type'] = "warning";
    header("Location: expenses.php");
    exit();
}
?>
