<?php
session_start();
require_once 'config/db_connect.php';

if (isset($_POST['save'])) {
    $name = $_POST['name'];
    $species = $_POST['species'];
    $water_type = $_POST['water_type'];
    $tank_id = !empty($_POST['tank_id']) ? $_POST['tank_id'] : NULL;
    $p_price = $_POST['purchase_price'];
    $s_price = $_POST['selling_price'];
    $stock = $_POST['stock'];

    // Image Upload
    $image = "";
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "uploads/";
        $image = time() . "_" . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $image);
    }

    $stmt = $conn->prepare("INSERT INTO fish (name, species, water_type, tank_id, purchase_price, selling_price, stock_quantity, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssiddis", $name, $species, $water_type, $tank_id, $p_price, $s_price, $stock, $image);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Fish added successfully!";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['message'] = "Error: " . $conn->error;
        $_SESSION['msg_type'] = "danger";
    }
    header("Location: fish.php");
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    // Delete image file if exists
    $res = $conn->query("SELECT image FROM fish WHERE id=$id");
    $row = $res->fetch_assoc();
    if ($row['image'] && file_exists("uploads/" . $row['image'])) {
        unlink("uploads/" . $row['image']);
    }

    $conn->query("DELETE FROM fish WHERE id=$id");
    $_SESSION['message'] = "Fish deleted!";
    $_SESSION['msg_type'] = "danger";
    header("Location: fish.php");
}

if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $species = $_POST['species'];
    $tank_id = !empty($_POST['tank_id']) ? $_POST['tank_id'] : NULL;
    $s_price = $_POST['selling_price'];

    // Check if new image uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "uploads/";
        $image = time() . "_" . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $image);

        $stmt = $conn->prepare("UPDATE fish SET name=?, species=?, tank_id=?, selling_price=?, image=? WHERE id=?");
        $stmt->bind_param("ssidsi", $name, $species, $tank_id, $s_price, $image, $id);
    } else {
        $stmt = $conn->prepare("UPDATE fish SET name=?, species=?, tank_id=?, selling_price=? WHERE id=?");
        $stmt->bind_param("ssidi", $name, $species, $tank_id, $s_price, $id);
    }

    if ($stmt->execute()) {
        $_SESSION['message'] = "Fish updated successfully!";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['message'] = "Error: " . $conn->error;
        $_SESSION['msg_type'] = "danger";
    }
    header("Location: fish.php");
}
?>