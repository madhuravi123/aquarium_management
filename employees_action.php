<?php
session_start();
require_once 'config/db_connect.php';

if (isset($_POST['save'])) {
    $name = $_POST['name'];
    $role = $_POST['role'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $salary = $_POST['salary'];
    $hire_date = $_POST['hire_date'];

    $stmt = $conn->prepare("INSERT INTO employees (name, role, phone, email, salary, hire_date) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $name, $role, $phone, $email, $salary, $hire_date);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Employee added!";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['message'] = "Error: " . $conn->error;
        $_SESSION['msg_type'] = "danger";
    }
    header("Location: employees.php");
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM employees WHERE id=$id");
    $_SESSION['message'] = "Employee deleted!";
    $_SESSION['msg_type'] = "danger";
    header("Location: employees.php");
}
?>