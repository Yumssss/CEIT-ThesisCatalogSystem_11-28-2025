<?php
include "../PHP/db_connect.php";

if (isset($_POST['thesis_id'])) {
    $id = intval($_POST['thesis_id']);

    $sql = "DELETE FROM tbl_thesis WHERE thesis_id = $id";
    echo ($conn->query($sql)) 
        ? "🗑️ Thesis deleted successfully!" 
        : "❌ Error deleting: " . $conn->error;
}

$conn->close();
?>
