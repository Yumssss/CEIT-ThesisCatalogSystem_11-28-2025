<?php
include "../PHP/db_connect.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $year = $_POST['year'];
    $department = $_POST['department'];
    $abstract = $_POST['abstract'];
    $availability = $_POST['availability'];

    $stmt = $conn->prepare("INSERT INTO tbl_thesis (title, author, year, department, abstract, availability) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssisss", $title, $author, $year, $department, $abstract, $availability);

    if ($stmt->execute()) {
        echo "<script>alert('✅ Thesis added successfully!'); window.location='manage-thesis.php';</script>";
    } else {
        echo "<script>alert('❌ Error adding thesis: " . $conn->error . "'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
}
