<?php
include "../PHP/db_connect.php";
if (isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $current = $conn->query("SELECT archive_status FROM tbl_thesis WHERE thesis_id=$id")->fetch_assoc()['archive_status'];
    $new = ($current === 'Active') ? 'Archived' : 'Active';
    $sql = "UPDATE tbl_thesis SET archive_status='$new' WHERE thesis_id=$id";
    echo ($conn->query($sql)) ? "🗂️ Thesis has been set to $new." : "❌ Error: " . $conn->error;
}
$conn->close();
