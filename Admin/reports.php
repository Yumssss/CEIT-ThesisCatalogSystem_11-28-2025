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
        /* ============================
   Admin Report — Polished Styles
   Paste at end of style.css
   ============================ */

        /* ------- Page layout tweaks ------- */
        main {
            padding: 32px 40px;
            /* slightly tighter than default */
            max-width: 1200px;
            margin: 0 auto;
        }

        /* ------- Summary cards ------- */
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 14px;
            margin-bottom: 24px;
        }

        .summary-cards .card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 88px;
            padding: 14px;
            border-radius: 12px;
            background: #ffffff;
            color: var(--primary-blue, #0a3d91);
            font-weight: 600;
            box-shadow: 0 6px 18px rgba(10, 61, 145, 0.06);
            transition: transform 180ms ease, box-shadow 180ms ease;
        }

        .summary-cards .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 26px rgba(10, 61, 145, 0.08);
        }

        .summary-cards .card h3 {
            font-size: 0.88rem;
            font-weight: 700;
            margin-bottom: 6px;
            color: #14346f;
        }

        .summary-cards .card p {
            font-size: 1.45rem;
            margin: 0;
            color: #0a2a6a;
        }

        /* ------- Table wrapper: rounded, no-gap ------- */
        .request-table,
        .request-table .table-wrapper {
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
            border: 1px solid #e6ecfb;
            box-shadow: 0 6px 16px rgba(10, 61, 145, 0.04);
        }

        /* Put table inside wrapper visually without HTML change:
   target immediate table inside .request-table */
        .request-table table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            background: transparent;
        }

        /* Header cells */
        .request-table th {
            padding: 12px 18px;
            background: linear-gradient(180deg, var(--primary-blue, #0a3d91), #153fa3);
            color: #fff;
            font-weight: 700;
            font-size: 0.92rem;
            text-align: left;
        }

        /* Data cells (polished spacing & color) */
        .request-table td {
            padding: 14px 18px;
            font-size: 0.95rem;
            border-bottom: 1px solid #f1f3f8;
            color: #1a1a1a;
            transition: background 220ms ease;
            vertical-align: middle;
        }

        /* Remove right-edge seam that creates gap */
        .request-table th,
        .request-table td {
            border-right: 1px solid transparent;
            background-clip: padding-box;
        }

        /* Avoid seam on last column */
        .request-table th:last-child,
        .request-table td:last-child {
            border-right: none;
        }

        /* Hover row */
        .request-table tr:hover td {
            background: #fbfdff;
        }

        /* Center columns: for Top tables: 2nd (times), 2nd (total) */
        .request-table td:nth-child(2),
        .request-table th:nth-child(2) {
            text-align: center;
            white-space: nowrap;
        }

        /* Title truncation */
        .request-table td:first-child {
            max-width: 560px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            cursor: default;
            text-align: start;
        }

        .request-table td[first-child]:hover::after,
        .request-table td.thesis-title-cell:hover::after {
            content: attr(data-full);
            position: absolute;
            top: -40px;
            left: 0;
            background: #0a3d91;
            color: #fff;
            padding: 6px 10px;
            border-radius: 6px;
            white-space: normal;
            max-width: 320px;
            z-index: 999;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.25);
            font-size: 0.85rem;
        }

        /* When table appears in narrow view, allow title wrap */
        @media (max-width: 700px) {
            .request-table td:first-child {
                white-space: normal;
                word-break: break-word;
            }
        }

        /* Buttons inside tables */
        .request-table .view-btn,
        .request-table .action-btn {
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 0.88rem;
            font-weight: 600;
            min-width: 88px;
            box-shadow: none;
        }

        /* Clean availability pills that match UI */
        .request-table .availability-badge,
        .request-table .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: capitalize;
            min-width: 88px;
            text-align: center;
        }

        /* Soft color variants (text + faint bg) */
        .request-table .avail-available {
            background: rgba(40, 167, 69, 0.10);
            color: #2e7d32;
            border: 1px solid rgba(40, 167, 69, 0.08);
        }

        .request-table .avail-unavailable {
            background: rgba(220, 53, 69, 0.10);
            color: #b91c1c;
            border: 1px solid rgba(220, 53, 69, 0.08);
        }

        /* ------- Chart containers ------- */
        .request-table .chart-holder {
            max-width: 720px;
            margin: 16px auto 0;
            padding: 12px;
            border-radius: 10px;
            background: linear-gradient(180deg, rgba(10, 61, 145, 0.02), rgba(10, 61, 145, 0.01));
            border: 1px solid rgba(10, 61, 145, 0.04);
        }

        /* Ensure canvases scale nicely; use explicit height for consistent rendering */
        .request-table canvas {
            width: 100% !important;
            height: 320px !important;
        }

        /* Smaller screens: less chart height */
        @media (max-width: 600px) {
            .request-table canvas {
                height: 240px !important;
            }
        }

        /* Minor utility */
        .labelOnLeft {
            margin-bottom: 10px;
            color: var(--primary-blue);
            font-weight: 700;
            font-size: 1.05rem;
        }

        /* === Show full title (wrap lines) while keeping column widths === */
        /* Apply fixed layout so column widths are honored and wrapping is stable */
        .request-table table,
        .request-table table thead,
        .request-table table tbody {
            table-layout: fixed;
        }

        /* Allow title cell (first column) to wrap and grow row height */
        .request-table td:first-child,
        .request-table th:first-child {
            white-space: normal !important;
            /* allow wrapping */
            word-break: break-word;
            /* break long words */
            overflow: visible !important;
            /* ensure content is visible */
            text-overflow: clip !important;
            /* disable ellipsis */
            vertical-align: top;
            /* top-align so multi-line looks neat */
        }

        /* Keep header width declared inline (70% / 30%) — table-layout: fixed uses those widths */
        /* Reduce cell padding slightly on very long titles to keep UX neat */
        .request-table td {
            padding: 12px 16px;
            line-height: 1.38;
        }

        /* Optional: set a soft maximum height per row to avoid extremely tall rows (uncomment if wanted)
.request-table td:first-child {
    max-height: 10em;
    overflow-y: auto;
}
*/

        /* Ensure no overlap with availability pill or action column */
        .request-table td:last-child {
            white-space: nowrap;
            /* keep action button on a single line */
        }
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
                <h2 class="labelOnLeft">Summary</h2>
            </section>
            <div class="summary-cards">
                <div class="card">
                    <h3>Total Requests</h3>
                    <p><?= $status_counts['total_requests'] ?></p>
                </div>
                <div class="card">
                    <h3>Pending</h3>
                    <p><?= $status_counts['pending'] ?></p>
                </div>
                <div class="card">
                    <h3>Approved</h3>
                    <p><?= $status_counts['approved'] ?></p>
                </div>
                <div class="card">
                    <h3>Returned</h3>
                    <p><?= $status_counts['returned'] ?></p>
                </div>
                <div class="card">
                    <h3>Rejected</h3>
                    <p><?= $status_counts['rejected'] ?></p>
                </div>
                <div class="card">
                    <h3>Complete</h3>
                    <p><?= $status_counts['complete'] ?></p>
                </div>
            </div>
            <br>
            <!-- Top Borrowed Thesis -->
            <section class="request-header">
                <h2>Top 5 Most Borrowed Thesis</h2>
            </section>
            <section class="request-table">
                <table>
                    <thead>
                        <tr>
                            <th style="width:70%">Thesis Title</th>
                            <th style="width:30%">Times Borrowed</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($top_items as $item): ?>
                            <tr>
                                <td title="<?= htmlspecialchars($item['title']) ?>">
                                    <?= htmlspecialchars($item['title']) ?>
                                </td>
                                <td><?= $item['times_borrowed'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
            <br><br>
            <!-- Top Borrowing Departments -->
            <section class="request-header">
                <h2>Top Borrowing Departments</h2>
            </section>
            <section class="request-table">
                <table>
                    <thead>
                        <tr>
                            <th style="width:70%">Department</th>
                            <th style="width:30%">Total Borrows</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($top_departments as $dept): ?>
                            <tr>
                                <td><?= htmlspecialchars($dept['department']) ?></td>
                                <td><?= $dept['total_borrowed'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div style="max-width:500px; margin:20px auto;">
                    <canvas id="departmentsChart"></canvas>
                </div>
            </section>
            <br><br>
            <!-- Borrowing Trends Chart -->
            <section class="request-header">
                <h2>Borrowing Trends by Month</h2>
            </section>
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
            type: 'line',
            data: {
                labels: <?= json_encode(array_column($trends, 'month')) ?>,
                datasets: [{
                    label: 'Requests per Month',
                    data: <?= json_encode(array_column($trends, 'total')) ?>,
                    fill: true,
                    backgroundColor: 'rgba(75,192,192,0.2)',
                    borderColor: 'rgba(75,192,192,1)',
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        const deptCtx = document.getElementById('departmentsChart').getContext('2d');
        const deptChart = new Chart(deptCtx, {
            type: 'pie',
            data: {
                labels: <?= json_encode(array_column($top_departments, 'department')) ?>,
                datasets: [{
                    data: <?= json_encode(array_column($top_departments, 'total_borrowed')) ?>,
                    backgroundColor: [
                        'rgba(255,99,132,0.6)',
                        'rgba(54,162,235,0.6)',
                        'rgba(255,206,86,0.6)',
                        'rgba(75,192,192,0.6)',
                        'rgba(153,102,255,0.6)'
                    ],
                    borderColor: [
                        'rgba(255,99,132,1)',
                        'rgba(54,162,235,1)',
                        'rgba(255,206,86,1)',
                        'rgba(75,192,192,1)',
                        'rgba(153,102,255,1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Sidebar toggle
        const menuIcon = document.querySelector(".menu-icon");
        const sidebar = document.querySelector(".sidebar");
        const container = document.querySelector(".container");
        menuIcon.addEventListener("click", () => {
            sidebar.classList.toggle("hidden");
            container.classList.toggle("full");
            menuIcon.classList.toggle("active");
        });
    </script>
</body>

</html>