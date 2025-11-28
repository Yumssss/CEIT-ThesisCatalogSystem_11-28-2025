<?php
include "../PHP/db_connect.php";
session_start();
date_default_timezone_set('Asia/Manila');

if (!isset($_SESSION['role'])) {
    header("Location: index.php");
    exit();
}

// Total requests by status
$status_result = $conn->query("
    SELECT 
        COUNT(*) as total_requests,
        SUM(CASE WHEN status='Pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status='Approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status='Returned' THEN 1 ELSE 0 END) as returned,
        SUM(CASE WHEN status='Rejected' THEN 1 ELSE 0 END) as rejected,
        SUM(CASE WHEN status='Complete' THEN 1 ELSE 0 END) as complete
    FROM tbl_borrow_requests
");
$status_counts = $status_result->fetch_assoc();

// Top 5 borrowed thesis
$top_items_result = $conn->query("
    SELECT t.title, COUNT(r.request_id) AS times_borrowed
    FROM tbl_borrow_requests r
    JOIN tbl_thesis t ON r.thesis_id = t.thesis_id
    GROUP BY r.thesis_id
    ORDER BY times_borrowed DESC
    LIMIT 5
");
$top_items = [];
while ($row = $top_items_result->fetch_assoc()) $top_items[] = $row;

// Borrowing trends by month
$trends_result = $conn->query("
    SELECT DATE_FORMAT(request_date, '%Y-%m') as month, COUNT(*) as total
    FROM tbl_borrow_requests
    GROUP BY month
    ORDER BY month ASC
");
$trends = [];
while ($row = $trends_result->fetch_assoc()) $trends[] = $row;

// Top borrowing departments
$top_departments_result = $conn->query("
    SELECT t.department, COUNT(r.request_id) AS total_borrowed
    FROM tbl_borrow_requests r
    JOIN tbl_thesis t ON r.thesis_id = t.thesis_id
    GROUP BY t.department
    ORDER BY total_borrowed DESC
    LIMIT 5
");
$top_departments = [];
while ($row = $top_departments_result->fetch_assoc()) $top_departments[] = $row;

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Admin Report</title>
<link rel="icon" type="image/png" href="pictures/Logo.png" />
<link rel="stylesheet" href="style.css" />
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    /* Summary cards */
    .summary-cards { display:flex; flex-wrap:wrap; gap:15px; margin-bottom:20px; }
    .card { flex:1; min-width:120px; padding:15px; border-radius:10px; background-color:#fff; color:#1E3A8A; text-align:center; font-weight:500; box-shadow:0 2px 5px rgba(0,0,0,0.1); }
    .card h3 { margin:0; font-size:16px; font-weight:500; }
    .card p { margin:5px 0 0; font-size:22px; font-weight:600; }
    /* Table and canvas spacing */
    section.request-table { margin-bottom: 30px; }
</style>
</head>

<body>
<header class="main-header">
    <div class="header-left">
        <span class="menu-icon">☰</span>
        <h1>CEIT Thesis Hub</h1>
    </div>
    <div class="header-right">
        <h2>Admin Report</h2>
        <div class="header-logo">
            <img src="pictures/Logo.png" alt="CEIT Logo" width="90" height="60" />
        </div>
    </div>
</header>

<div class="container">
    <?php include 'sidebar.php'; ?>

    <main>
        <!-- Summary Cards -->
        <section class="request-header">
            <h2>Summary</h2>
        </section>
        <div class="summary-cards">
            <div class="card"><h3>Total Requests</h3><p><?= $status_counts['total_requests'] ?></p></div>
            <div class="card"><h3>Pending</h3><p><?= $status_counts['pending'] ?></p></div>
            <div class="card"><h3>Approved</h3><p><?= $status_counts['approved'] ?></p></div>
            <div class="card"><h3>Returned</h3><p><?= $status_counts['returned'] ?></p></div>
            <div class="card"><h3>Rejected</h3><p><?= $status_counts['rejected'] ?></p></div>
            <div class="card"><h3>Complete</h3><p><?= $status_counts['complete'] ?></p></div>
        </div>

        <!-- Top Borrowed Thesis -->
        <section class="request-header"><h2>Top 5 Most Borrowed Thesis</h2></section>
        <section class="request-table">
            <table>
                <thead>
                    <tr><th style="width:70%">Thesis Title</th><th style="width:30%">Times Borrowed</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($top_items as $item): ?>
                        <tr><td><?= htmlspecialchars($item['title']) ?></td><td><?= $item['times_borrowed'] ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <!-- Top Borrowing Departments -->
        <section class="request-header"><h2>Top Borrowing Departments</h2></section>
        <section class="request-table">
            <table>
                <thead><tr><th style="width:70%">Department</th><th style="width:30%">Total Borrows</th></tr></thead>
                <tbody>
                    <?php foreach ($top_departments as $dept): ?>
                        <tr><td><?= htmlspecialchars($dept['department']) ?></td><td><?= $dept['total_borrowed'] ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div style="max-width:500px; margin:20px auto;">
                <canvas id="departmentsChart"></canvas>
            </div>
        </section>

        <!-- Borrowing Trends Chart -->
        <section class="request-header"><h2>Borrowing Trends by Month</h2></section>
        <section class="request-table">
            <div style="max-width:650px; margin:20px auto;">
                <canvas id="trendsChart"></canvas>
            </div>
        </section>
    </main>
</div>

<script>
const trendsCtx = document.getElementById('trendsChart').getContext('2d');
const trendsChart = new Chart(trendsCtx, {
    type:'line',
    data:{
        labels: <?= json_encode(array_column($trends,'month')) ?>,
        datasets:[{
            label:'Requests per Month',
            data: <?= json_encode(array_column($trends,'total')) ?>,
            fill:true,
            backgroundColor:'rgba(75,192,192,0.2)',
            borderColor:'rgba(75,192,192,1)',
            tension:0.3
        }]
    },
    options:{ responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}} }
});

const deptCtx = document.getElementById('departmentsChart').getContext('2d');
const deptChart = new Chart(deptCtx,{
    type:'pie',
    data:{
        labels: <?= json_encode(array_column($top_departments,'department')) ?>,
        datasets:[{
            data: <?= json_encode(array_column($top_departments,'total_borrowed')) ?>,
            backgroundColor:[
                'rgba(255,99,132,0.6)',
                'rgba(54,162,235,0.6)',
                'rgba(255,206,86,0.6)',
                'rgba(75,192,192,0.6)',
                'rgba(153,102,255,0.6)'
            ],
            borderColor:[
                'rgba(255,99,132,1)',
                'rgba(54,162,235,1)',
                'rgba(255,206,86,1)',
                'rgba(75,192,192,1)',
                'rgba(153,102,255,1)'
            ],
            borderWidth:1
        }]
    },
    options:{ responsive:true, maintainAspectRatio:false }
});

// Sidebar toggle
const menuIcon=document.querySelector(".menu-icon");
const sidebar=document.querySelector(".sidebar");
const container=document.querySelector(".container");
menuIcon.addEventListener("click",()=>{ sidebar.classList.toggle("hidden"); container.classList.toggle("full"); menuIcon.classList.toggle("active"); });
</script>
</body>
</html>
