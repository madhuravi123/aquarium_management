<?php
session_start();
require_once 'config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        header("Location: admin_login.php?error=" . urlencode("All fields are required."));
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

            // If there is a guest cart, transfer it to DB cart on customer login
            if ($row['role'] === 'customer' && !empty($_SESSION['guest_cart'])) {
                foreach ($_SESSION['guest_cart'] as $fish_id => $qty) {
                    $fish_id = (int)$fish_id;
                    $qty     = max(1, (int)$qty);
                    // Upsert into DB cart
                    $upsert = $conn->prepare(
                        "INSERT INTO cart (user_id, fish_id, quantity)
                         VALUES (?, ?, ?)
                         ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)"
                    );
                    $upsert->bind_param("iii", $row['id'], $fish_id, $qty);
                    $upsert->execute();
                    $upsert->close();
                }
                unset($_SESSION['guest_cart']); // clear guest cart after transfer
            }

            // Role-based redirect
            if ($row['role'] === 'customer') {
                header("Location: shop.php");
            } else {
                header("Location: dashboard.php");
            }
            exit();
        } else {
            header("Location: admin_login.php?error=" . urlencode("Invalid password. Please try again."));
            exit();
        }
    } else {
        header("Location: admin_login.php?error=" . urlencode("Username not found. Please check your credentials."));
        exit();
    }
}
?>
