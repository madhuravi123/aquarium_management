<?php
session_start();
require_once 'config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin_login.php?tab=register");
    exit();
}

$full_name = trim($_POST['full_name'] ?? '');
if (empty($full_name)) {
    header("Location: admin_login.php?tab=register&error=" . urlencode("Please enter your name."));
    exit();
}

$username = strtolower(preg_replace('/\s+/', '', $full_name));
$password = $username . '@123';
$hashed   = password_hash($password, PASSWORD_DEFAULT);

// Check if username already exists
$check = $conn->prepare("SELECT id FROM users WHERE username = ?");
$check->bind_param("s", $username);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    $check->close();
    header("Location: admin_login.php?tab=register&error=" . urlencode("Username '$username' is already taken. Please use a different name."));
    exit();
}
$check->close();

// Create customer account
$stmt = $conn->prepare("INSERT INTO users (username, full_name, password, role) VALUES (?, ?, ?, 'customer')");
$stmt->bind_param("sss", $username, $full_name, $hashed);

if ($stmt->execute()) {
    $new_id = $conn->insert_id;
    $_SESSION['user_id']   = $new_id;
    $_SESSION['username']  = $username;
    $_SESSION['full_name'] = $full_name;
    $_SESSION['role']      = 'customer';
    $stmt->close();
    header("Location: shop.php?welcome=1");
    exit();
} else {
    $stmt->close();
    header("Location: admin_login.php?tab=register&error=" . urlencode("Registration failed. Please try again."));
    exit();
}
?>
