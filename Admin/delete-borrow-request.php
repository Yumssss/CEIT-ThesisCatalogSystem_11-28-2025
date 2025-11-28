<?php
include "../PHP/db_connect.php";

$data = json_decode(file_get_contents("php://input"), true);
$ids = $data["ids"] ?? [];

if (empty($ids)) {
    echo "No IDs received.";
    exit;
}

$id_list = implode(",", array_map("intval", $ids));

$sql = "DELETE FROM tbl_borrow_requests WHERE request_id IN ($id_list)";
if ($conn->query($sql)) {
    echo "Selected requests deleted successfully.";
} else {
    echo "Error deleting requests.";
}

$conn->close();
