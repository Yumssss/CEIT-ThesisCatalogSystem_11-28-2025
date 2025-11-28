<?php
include "../PHP/db_connect.php";
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "Unauthorized request.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = $_POST['thesis_id'];
    $title = $_POST['title'];
    $author = $_POST['author'];
    $year = $_POST['year'];
    $department = $_POST['department'];
    $availability = $_POST['availability'];
    $abstract = $_POST['abstract'];

    if (!$id) {
        echo "Missing thesis ID.";
        exit();
    }

    $stmt = $conn->prepare("
        UPDATE tbl_thesis 
        SET title=?, author=?, year=?, department=?, availability=?, abstract=?, last_updated=NOW()
        WHERE thesis_id=?
    ");

    $stmt->bind_param(
        "ssisssi",
        $title,
        $author,
        $year,
        $department,
        $availability,
        $abstract,
        $id
    );

    $isUpdated = $stmt->execute();

    echo $isUpdated
        ? "Thesis updated successfully!"
        : "Error updating thesis.";


    $stmt->close();
    $conn->close();
}
