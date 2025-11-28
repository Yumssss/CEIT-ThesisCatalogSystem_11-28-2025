<?php
include "../PHP/db_connect.php";
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['status'])) {
    $id = intval($_POST['id']);
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE tbl_librarians SET status = ? WHERE librarian_id = ?");
    $stmt->bind_param("si", $status, $id);

    if ($stmt->execute()) {
        echo "✅ Librarian status updated to '$status'.";
    } else {
        echo "❌ Failed to update librarian status.";
    }
} else {
    echo "Invalid request.";
}
