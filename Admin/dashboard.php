<?php
include "../PHP/db_connect.php";
session_start();
date_default_timezone_set('Asia/Manila');

// Redirect if not logged in
if (!isset($_SESSION['role'])) {
    header("Location: index.php");
    exit();
}

$role = $_SESSION['role'];
$displayName = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : $_SESSION['admin'];

// Counters
$total_thesis = $conn->query("SELECT COUNT(*) AS total FROM tbl_thesis")->fetch_assoc()['total'];
$approved = $conn->query("SELECT COUNT(*) AS total FROM tbl_borrow_requests WHERE status = 'Approved'")->fetch_assoc()['total'];
$pending = $conn->query("SELECT COUNT(*) AS total FROM tbl_borrow_requests WHERE status = 'Pending'")->fetch_assoc()['total'];
$borrowed = $conn->query("SELECT COUNT(*) AS total FROM tbl_borrow_requests WHERE status = 'Returned' OR status = 'Approved'")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard</title>
    <link rel="icon" type="image/png" href="pictures/Logo.png">
    <link rel="stylesheet" href="style.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        /* Clickable counter cards */
        .dashboard-counters {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 20px;
        }

        .counter-card-link {
            text-decoration: none;
            color: inherit;
            display: block;
            flex: 1;
            min-width: 150px;
        }

        .counter-card-link:hover {
            transform: scale(1.02);
            transition: transform 0.2s;
        }

        .counter-card {
            display: flex;
            align-items: center;
            gap: 10px;
            background-color: #fff;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .counter-card .icon-box {
            flex-shrink: 0;
        }

        .counter-card .counter-details {
            text-align: left;
        }

        .counter-card .counter-title {
            font-weight: 500;
            font-size: 14px;
            display: block;
        }

        .counter-card .counter-value {
            font-size: 22px;
            font-weight: 600;
            margin-top: 5px;
        }

        /* Recent requests table */
        .recent-requests table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .recent-requests th,
        .recent-requests td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .recent-requests th {
            background-color: #f4f4f4;
        }
    </style>
</head>

<body>
    <!-- HEADER -->
    <header class="main-header">
        <div class="header-left">
            <span class="menu-icon">☰</span>
            <h1>CEIT Thesis Hub</h1>
        </div>
        <div class="header-right">
            <h2>Admin Dashboard</h2>
            <div class="header-logo"><img src="pictures/Logo.png" alt="CEIT Logo" width="90" height="60"></div>
        </div>
    </header>

    <div class="container">
        <?php include 'sidebar.php'; ?>

        <!-- MAIN CONTENT -->
        <main>
            <!-- Welcome Section -->
            <section class="welcome-section">
                <h2 class="labelOnLeft">Welcome! Admin</h2>
                <p class="date"><?= strtoupper(date('M d, Y | l, h:i A')) ?></p>
                <hr class="divider" />
            </section>

            <!-- Counters -->
            <section class="dashboard-counters">
                <a href="manage-thesis.php" class="counter-card-link">
                    <div class="counter-card">
                        <div class="icon-box"><img src="pictures/TOTAL.png" width="50" height="50"></div>
                        <div class="counter-details">
                            <span class="counter-title">Total Thesis</span>
                            <span class="counter-value"><?= $total_thesis ?></span>
                        </div>
                    </div>
                </a>

                <a href="borrowing-request.php" class="counter-card-link">
                    <div class="counter-card">
                        <div class="icon-box"><img src="pictures/APPROVED.png" width="50" height="50"></div>
                        <div class="counter-details">
                            <span class="counter-title">Approved</span>
                            <span class="counter-value"><?= $approved ?></span>
                        </div>
                    </div>
                </a>

                <a href="borrowing-request.php" class="counter-card-link">
                    <div class="counter-card">
                        <div class="icon-box"><img src="pictures/PENDING.png" width="50" height="50"></div>
                        <div class="counter-details">
                            <span class="counter-title">Pending Request</span>
                            <span class="counter-value"><?= $pending ?></span>
                        </div>
                    </div>
                </a>

                <a href="borrowing-request.php" class="counter-card-link">
                    <div class="counter-card">
                        <div class="icon-box"><img src="pictures/BORROWED.png" width="50" height="50"></div>
                        <div class="counter-details">
                            <span class="counter-title">Borrowed</span>
                            <span class="counter-value"><?= $borrowed ?></span>
                        </div>
                    </div>
                </a>
            </section>

            <!-- Recent Requests -->
            <h3 style="margin-top: 40px; color: #0A3D91;">Recent Requests</h3>
            <section class="recent-requests">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 10%;">Request #</th>
                            <th style="width: 15%;">Student Name</th>
                            <th style="width: 35%;">Thesis Title</th>
                            <th style="width: 15%;">Date Requested</th>
                            <th style="width: 15%;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $recent = $conn->query("
    SELECT r.*, t.title, t.department 
    FROM tbl_borrow_requests r
    JOIN tbl_thesis t ON r.thesis_id = t.thesis_id
    ORDER BY r.request_date DESC
    LIMIT 5
");

                        if ($recent && $recent->num_rows > 0) {
                            while ($row = $recent->fetch_assoc()) {

                                $json_data = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');

                                echo "
<tr class='dash-row' data-row=\"{$json_data}\">
    <td>{$row['request_number']}</td>


            <td>{$row['student_name']}</td>

            <td class='thesis-title-cell' 
                title='" . htmlspecialchars($row['title'], ENT_QUOTES) . "'>
                " . htmlspecialchars($row['title']) . "
            </td>

            <td>" . date('Y-m-d', strtotime($row['request_date'])) . "</td>

            <td>
                <span class='status-badge status-" . strtolower(str_replace(' ', '', $row['status'])) . "
'>
                    {$row['status']}
                </span>
            </td>
        </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' style='text-align:center; color:gray;'>No recent requests found.</td></tr>";
                        }

                        $conn->close();
                        ?>
                    </tbody>

                </table>
            </section>
        </main>
    </div>

    <!-- VIEW MODAL -->
    <div id="viewModal" class="modal-1" style="display:none;">
        <div class="modal-content-1">
            <span class="modal-close-1" onclick="closeModal()">&times;</span>

            <div class="modal-section">

                <!-- REQUEST INFORMATION -->
                <div class="section-title">REQUEST INFORMATION</div>
                <div class="section-divider"></div>

                <div class="info-grid">
                    <div class="label">Request Number :</div>
                    <div class="value" id="m-request"></div>

                    <div class="label">Date Requested :</div>
                    <div class="value" id="m-date"></div>

                    <div class="label">Status :</div>
                    <div class="value" id="m-status"></div>

                    <div class="label">Librarian :</div>
                    <div class="value" id="m-librarian"></div>
                </div>
                <br>
                <!-- STUDENT INFORMATION -->
                <div class="section-title">STUDENT INFORMATION</div>
                <div class="section-divider"></div>

                <div class="info-grid">
                    <div class="label">Student Name :</div>
                    <div class="value" id="m-student"></div>

                    <div class="label">Student No. :</div>
                    <div class="value" id="m-studentno"></div>

                    <div class="label">Course & Section :</div>
                    <div class="value" id="m-course"></div>
                </div>
                <br>
                <!-- THESIS INFORMATION -->
                <div class="section-title">THESIS INFORMATION</div>
                <div class="section-divider"></div>

                <div class="thesis-info-block">
                    <div class="multi-block">
                        <div class="label">Thesis Title</div>
                        <div class="value" id="m-title"></div>
                    </div>
                    <br>
                    <div class="multi-block">
                        <div class="label">Author(s)</div>
                        <div class="value" id="m-author"></div>
                    </div><br>
                </div>

                <div class="info-grid">
                    <div class="label">Department :</div>
                    <div class="value" id="m-dept"></div>

                    <div class="label">Year :</div>
                    <div class="value" id="m-year"></div>
                </div>
                <br>
            </div>

            <div class="actions" id="modal-actions"></div>
        </div>
    </div>

    <script>
        /* ============================================================
           SIDEBAR TOGGLE
        ============================================================ */
        const menuIcon = document.querySelector(".menu-icon");
        const sidebar = document.querySelector(".sidebar");
        const container = document.querySelector(".container");

        menuIcon.addEventListener("click", () => {
            sidebar.classList.toggle("hidden");
            container.classList.toggle("full");
            menuIcon.classList.toggle("active");

            menuIcon.textContent = menuIcon.textContent === "☰" ? "✖" : "☰";
        });


        /* ============================================================
           MODAL VIEW & ACTION LOGIC
        ============================================================ */
        window.openModal = function(button) {
            const data = JSON.parse(button.getAttribute("data-row"));

            // Fill in aligned fields
            document.getElementById("m-request").textContent = data.request_number;
            document.getElementById("m-date").textContent = data.request_date;
            const statusElement = document.getElementById("m-status");
            const status = data.status.trim();

            // Map status → class
            const statusClass = {
                "Pending": "status-pending",
                "For Claiming": "status-forclaiming",
                "Claimed": "status-claimed",
                "Returned": "status-returned",
                "Rejected": "status-rejected"
            };

            // Apply badge
            statusElement.innerHTML = `<span class="status-badge ${statusClass[status]}">${status}</span>`;


            if (data.librarian_name && data.librarian_name !== "null") {
                document.getElementById("m-librarian").textContent = data.librarian_name;
            } else {
                document.getElementById("m-librarian").textContent = "---";
            }

            // Student
            document.getElementById("m-student").textContent = data.student_name;
            document.getElementById("m-studentno").textContent = data.student_no;
            document.getElementById("m-course").textContent = data.course_section;

            // Thesis
            document.getElementById("m-title").textContent = data.title;
            document.getElementById("m-author").textContent = data.author;
            document.getElementById("m-dept").textContent = data.department;
            document.getElementById("m-year").textContent = data.year;

            // Build actions
            let actionsHTML = "";
            // const status = data.status.trim();

            if (status === "Pending") {
                actionsHTML = `
            <button class="status-btn approve" onclick="updateStatus(${data.request_id}, 'For Claiming')">Approve</button>
            <button class="status-btn reject" onclick="updateStatus(${data.request_id}, 'Rejected')">Reject</button>`;
            } else if (status === "For Claiming") {
                actionsHTML = `<button class="status-btn claim" onclick="updateStatus(${data.request_id}, 'Claimed')">Mark as Claimed</button>`;
            } else if (status === "Claimed") {
                actionsHTML = `<button class="status-btn return" onclick="updateStatus(${data.request_id}, 'Returned')">Mark as Returned</button>`;
            } else {
                actionsHTML = `<p style="color:gray;text-align:center;">No further actions available.</p>`;
            }

            document.getElementById("modal-actions").innerHTML = actionsHTML;

            // Show modal
            document.getElementById("viewModal").style.display = "flex";
        };

        function closeModal() {
            document.getElementById("viewModal").style.display = "none";
        }

        window.onclick = function(event) {
            const modal = document.getElementById("viewModal");
            if (event.target === modal) closeModal();
        };

        document.querySelectorAll(".dash-row").forEach(row => {
            row.style.cursor = "pointer";
            row.addEventListener("click", () => {
                openModal({
                    getAttribute: () => row.getAttribute("data-row")
                });
            });
        });

        /* ============================================================
           STATUS UPDATE (AJAX)
        ============================================================ */
        function updateStatus(requestId, newStatus) {
            if (!confirm("Are you sure you want to mark this as " + newStatus + "?")) return;

            fetch("update-request-status.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: "request_id=" + requestId + "&new_status=" + newStatus
                })
                .then(res => res.text())
                .then(msg => {
                    alert(msg);
                    localStorage.setItem("lastTab", newStatus);
                    location.reload();
                })
                .catch(() => alert("Error updating status."));
        }
    </script>

</body>

</html>