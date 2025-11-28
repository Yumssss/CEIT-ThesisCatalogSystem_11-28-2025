<?php
include "../PHP/db_connect.php";
header("Content-Type: application/json");

$action = $_POST['action'] ?? '';
$ids = $_POST['ids'] ?? [];

if (!$action || empty($ids)) {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
    exit;
}

$ids = array_map('intval', $ids);
$in = implode(',', $ids);

if ($action === "delete") {
    $sql = "DELETE FROM tbl_thesis WHERE thesis_id IN ($in)";
} elseif ($action === "available") {
    $sql = "UPDATE tbl_thesis SET availability='Available' WHERE thesis_id IN ($in)";
} elseif ($action === "unavailable") {
    $sql = "UPDATE tbl_thesis SET availability='Unavailable' WHERE thesis_id IN ($in)";
}

if ($conn->query($sql)) {
    echo json_encode(["success" => true, "message" => "Bulk action completed!"]);
} else {
    echo json_encode(["success" => false, "message" => "DB Error: " . $conn->error]);
}
