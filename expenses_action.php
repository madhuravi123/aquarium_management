<?php
session_start();
require_once 'config/db_connect.php';

if (isset($_POST['save'])) {
    $category = $_POST['category'];
    $amount = $_POST['amount'];
    $date = $_POST['expense_date'];
    $desc = $_POST['description'];

    $stmt = $conn->prepare("INSERT INTO expenses (category, amount, expense_date, description) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sdss", $category, $amount, $date, $desc);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Expense added!";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['message'] = "Error: " . $conn->error;
        $_SESSION['msg_type'] = "danger";
    }
    header("Location: expenses.php");
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM expenses WHERE id=$id");
    $_SESSION['message'] = "Expense deleted!";
    $_SESSION['msg_type'] = "danger";
    header("Location: expenses.php");
}
?>