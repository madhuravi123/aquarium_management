<?php
session_start();
require_once 'config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        header("Location: index.php?error=All fields are required");
        exit();
    }

    $stmt = $conn->prepare("SELECT id, username, full_name, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id']   = $row['id'];
            $_SESSION['username']  = $row['username'];
            $_SESSION['full_name'] = $row['full_name'] ?? $row['username'];
            $_SESSION['role']      = $row['role'];

            // Role-based redirect
            if ($row['role'] === 'customer') {
                header("Location: shop.php");
            } else {
                header("Location: dashboard.php");
            }
            exit();
        } else {
            header("Location: index.php?error=Invalid password. Please try again.");
            exit();
        }
    } else {
        header("Location: index.php?error=Username not found. Please check your credentials.");
        exit();
    }
    $stmt->close();
}
$conn->close();
?>
