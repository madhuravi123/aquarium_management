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
    $name = trim($_POST['name']);
    $role = trim($_POST['role'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $salary = floatval($_POST['salary']);
    $hire_date = $_POST['hire_date'];

    $stmt = $conn->prepare("INSERT INTO employees (name, role, phone, email, salary, hire_date) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssds", $name, $role, $phone, $email, $salary, $hire_date);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Employee added!";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['message'] = "Error adding employee.";
        $_SESSION['msg_type'] = "danger";
    }
    $stmt->close();
    header("Location: employees.php");
    exit();
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM employees WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    $_SESSION['message'] = "Employee deleted!";
    $_SESSION['msg_type'] = "warning";
    header("Location: employees.php");
    exit();
}
?>
