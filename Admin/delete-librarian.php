<?php
include "../PHP/db_connect.php";
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo "Unauthorized.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);

    $stmt = $conn->prepare("DELETE FROM tbl_librarians WHERE librarian_id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "✅ Librarian account deleted successfully.";
    } else {
        echo "❌ Failed to delete librarian.";
    }
} else {
    echo "Invalid request.";
}
