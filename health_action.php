<?php
session_start();
require_once 'config/db_connect.php';

if (isset($_POST['save'])) {
    $fish_id = !empty($_POST['fish_id']) ? $_POST['fish_id'] : NULL;
    $tank_id = !empty($_POST['tank_id']) ? $_POST['tank_id'] : NULL;
    $disease = $_POST['disease_name'];
    $symptoms = $_POST['symptoms'];
    $treatment = $_POST['treatment'];

    $stmt = $conn->prepare("INSERT INTO fish_health (fish_id, tank_id, disease_name, symptoms, treatment) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $fish_id, $tank_id, $disease, $symptoms, $treatment);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Health issue recorded";
        $_SESSION['msg_type'] = "warning";
    } else {
        $_SESSION['message'] = "Error: " . $conn->error;
        $_SESSION['msg_type'] = "danger";
    }
    header("Location: fish_health.php");
}

if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $status = $_POST['status'];
    $treatment = $_POST['treatment'];

    $stmt = $conn->prepare("UPDATE fish_health SET status=?, treatment=? WHERE id=?");
    $stmt->bind_param("ssi", $status, $treatment, $id);
    $stmt->execute();

    $_SESSION['message'] = "Status updated";
    $_SESSION['msg_type'] = "success";
    header("Location: fish_health.php");
}
?>