<?php
include "../PHP/db_connect.php";
session_start();

if (isset($_POST['request_id']) && isset($_POST['new_status'])) {
    $id = intval($_POST['request_id']);
    $status = trim($_POST['new_status']);

    // Allowed statuses based on NEW workflow
    $allowed = ['Pending', 'For Claiming', 'Claimed', 'Returned', 'Rejected'];

    if (!in_array($status, $allowed)) {
        echo "Invalid status.";
        exit();
    }

    // Get librarian/admin name
    $librarian_name = "Librarian";
    if (isset($_SESSION['fullname'])) {
        $librarian_name = $_SESSION['fullname'];
    }

    // Save librarian name when approving (moving to For Claiming)
    if ($status === 'For Claiming') {
        $update_stmt = $conn->prepare("
            UPDATE tbl_borrow_requests
            SET status = ?, librarian_name = ?
            WHERE request_id = ?
        ");
        $update_stmt->bind_param("ssi", $status, $librarian_name, $id);
    } else {
        // Other statuses don't modify librarian_name
        $update_stmt = $conn->prepare("
            UPDATE tbl_borrow_requests
            SET status = ?
            WHERE request_id = ?
        ");
        $update_stmt->bind_param("si", $status, $id);
    }

    if ($update_stmt->execute()) {
        echo "Status updated to $status.";
    } else {
        echo "Error updating status: " . $conn->error;
    }

    $update_stmt->close();
} else {
    echo "Missing POST data.";
}

$conn->close();
