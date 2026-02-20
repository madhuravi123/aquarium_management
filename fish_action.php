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

// Add new fish
if (isset($_POST['save'])) {
    $name = trim($_POST['name']);
    $species = trim($_POST['species'] ?? '');
    $water_type = $_POST['water_type'];
    $tank_id = !empty($_POST['tank_id']) ? intval($_POST['tank_id']) : NULL;
    $p_price = floatval($_POST['purchase_price']);
    $s_price = floatval($_POST['selling_price']);
    $stock = intval($_POST['stock']);

    $image = "";
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed) && $_FILES['image']['size'] <= 5242880) {
            $target_dir = "uploads/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
            $image = time() . "_" . preg_replace('/[^a-zA-Z0-9._-]/', '', basename($_FILES["image"]["name"]));
            move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $image);
        }
    }

    $stmt = $conn->prepare("INSERT INTO fish (name, species, water_type, tank_id, purchase_price, selling_price, stock_quantity, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssiddis", $name, $species, $water_type, $tank_id, $p_price, $s_price, $stock, $image);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Fish added successfully!";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['message'] = "Error adding fish.";
        $_SESSION['msg_type'] = "danger";
    }
    $stmt->close();
    header("Location: fish.php");
    exit();
}

// Delete fish
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("SELECT image FROM fish WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    if ($row && $row['image'] && file_exists("uploads/" . $row['image'])) {
        unlink("uploads/" . $row['image']);
    }
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM fish WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['message'] = "Fish deleted!";
    $_SESSION['msg_type'] = "warning";
    header("Location: fish.php");
    exit();
}

// Update fish
if (isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $species = trim($_POST['species'] ?? '');
    $tank_id = !empty($_POST['tank_id']) ? intval($_POST['tank_id']) : NULL;
    $s_price = floatval($_POST['selling_price']);

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed) && $_FILES['image']['size'] <= 5242880) {
            $target_dir = "uploads/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
            $image = time() . "_" . preg_replace('/[^a-zA-Z0-9._-]/', '', basename($_FILES["image"]["name"]));
            move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $image);

            $stmt = $conn->prepare("UPDATE fish SET name=?, species=?, tank_id=?, selling_price=?, image=? WHERE id=?");
            $stmt->bind_param("ssidsi", $name, $species, $tank_id, $s_price, $image, $id);
        } else {
            $stmt = $conn->prepare("UPDATE fish SET name=?, species=?, tank_id=?, selling_price=? WHERE id=?");
            $stmt->bind_param("ssidi", $name, $species, $tank_id, $s_price, $id);
        }
    } else {
        $stmt = $conn->prepare("UPDATE fish SET name=?, species=?, tank_id=?, selling_price=? WHERE id=?");
        $stmt->bind_param("ssidi", $name, $species, $tank_id, $s_price, $id);
    }

    if ($stmt->execute()) {
        $_SESSION['message'] = "Fish updated successfully!";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['message'] = "Error updating fish.";
        $_SESSION['msg_type'] = "danger";
    }
    $stmt->close();
    header("Location: fish.php");
    exit();
}
?>
