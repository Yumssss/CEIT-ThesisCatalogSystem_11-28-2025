<?php
include "../PHP/db_connect.php";

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$department = isset($_GET['department']) ? trim($_GET['department']) : '';

$query = "SELECT * FROM tbl_thesis WHERE archive_status = 'Active'";

if ($department !== '') {
    $department = $conn->real_escape_string($department);
    $query .= " AND department = '$department'";
}

if ($search !== '') {
    $search = $conn->real_escape_string($search);
    $query .= " AND (title LIKE '%$search%' OR author LIKE '%$search%' OR keywords LIKE '%$search%')";
}

$query .= " ORDER BY year DESC";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . htmlspecialchars($row['title']) . "</td>
                <td>" . htmlspecialchars($row['department']) . "</td>
                <td>" . htmlspecialchars($row['year']) . "</td>
                <td>" . htmlspecialchars($row['availability']) . "</td>
                <td><a href='thesis-details.php?id={$row['thesis_id']}' class='catalog-view-btn'>View Details</a></td>
              </tr>";
    }
} else {
    echo "<tr class='no-results'><td colspan='5'>No thesis records found.</td></tr>";
}

$conn->close();
