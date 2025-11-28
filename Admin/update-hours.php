<?php
include "../PHP/db_connect.php";
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Get values
$open_time = $_POST['open_time'] ?? '';
$close_time = $_POST['close_time'] ?? '';

if (empty($open_time) || empty($close_time)) {
    $_SESSION['msg'] = "❌ Please select both open and close times.";
    header("Location: system-management.php");
    exit();
}

// Ensure table exists
$conn->query("CREATE TABLE IF NOT EXISTS tbl_system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE,
    setting_value VARCHAR(255)
)");

// Update or insert open time
$stmt = $conn->prepare("INSERT INTO tbl_system_settings (setting_key, setting_value)
    VALUES ('library_open_time', ?)
    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
$stmt->bind_param("s", $open_time);
$stmt->execute();

// Update or insert close time
$stmt = $conn->prepare("INSERT INTO tbl_system_settings (setting_key, setting_value)
    VALUES ('library_close_time', ?)
    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
$stmt->bind_param("s", $close_time);
$stmt->execute();

$_SESSION['msg'] = "✅ Library hours updated successfully!";
header("Location: system-management.php");
exit();
