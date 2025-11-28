<?php
include "../PHP/db_connect.php";
session_start();
date_default_timezone_set('Asia/Manila');

// 🔒 Redirect if not logged in or not admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$role = $_SESSION['role'];
$displayName = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : $_SESSION['admin'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Librarians</title>
    <link rel="icon" type="image/png" href="pictures/Logo.png">
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: "Poppins", sans-serif;
            background: #f6f8fa;
            margin: 0;
            padding: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table th,
        table td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: center;
        }

        table th {
            background: #2f3640;
            color: #fff;
            text-transform: uppercase;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .action-btn {
            padding: 6px 12px;
            margin: 2px;
            border: none;
            border-radius: 6px;
            color: #fff;
            cursor: pointer;
            transition: 0.2s;
            font-size: 13px;
        }

        .view {
            background-color: #3498db;
        }

        .view:hover {
            background-color: #2e86c1;
        }

        .divider {
            border: 0;
            height: 2px;
            background: #ddd;
            margin: 15px 0;
        }

        .filter-bar {
            margin-bottom: 15px;
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .filter-bar input,
        .filter-bar select {
            padding: 7px 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 14px;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 10px;
            color: #fff;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status-approved {
            background-color: #4caf50;
        }

        .status-pending {
            background-color: #ff9800;
        }

        .status-inactive {
            background-color: #9e9e9e;
        }

        .status-rejected {
            background-color: #f44336;
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
            <h2>Manage Librarians</h2>
            <div class="header-logo"><img src="pictures/Logo.png" width="90" height="60" alt="CEIT Logo"></div>
        </div>
    </header>

    <div class="container">
        <?php include 'sidebar.php'; ?>

        <main>
            <section class="welcome-section">
                <h2>Librarian Accounts</h2>
                <p class="date"><?php echo strtoupper(date('M d, Y | l, h:i A')); ?></p>
                <hr class="divider" />
            </section>

            <!-- 🔍 Filter + Sort Section -->
            <div class="filter-bar">
                <input type="text" id="searchInput" placeholder="Search name or email...">
                <select id="statusFilter">
                    <option value="">All Status</option>
                    <option value="approved">Approved</option>
                    <option value="pending">Pending</option>
                    <option value="inactive">Inactive</option>
                    <option value="rejected">Rejected</option>
                </select>
                <select id="sortBy">
                    <option value="id">Sort by ID</option>
                    <option value="name">Sort by Name</option>
                    <option value="status">Sort by Status</option>
                </select>
            </div>

            <!-- 📋 Librarians Table -->
            <section>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Section</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn->query("SELECT * FROM tbl_librarians ORDER BY librarian_id DESC");
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $statusClass = "status-" . strtolower($row['status']);

                                echo "
                                    <tr>
                                        <td>{$row['librarian_id']}</td>
                                        <td>{$row['fullname']}</td>
                                        <td>{$row['email']}</td>
                                        <td>{$row['section']}</td>
                                        <td><span class='status-badge $statusClass'>{$row['status']}</span></td>
                                        <td>" . ($row['last_login'] ?? 'N/A') . "</td>
                                        <td>
                                            <button class='action-btn view' onclick=\"window.location.href='view-librarian.php?id={$row['librarian_id']}'\">View</button>
                                        </td>
                                    </tr>
                                ";
                            }
                        } else {
                            echo "<tr><td colspan='7'>No librarian records found.</td></tr>";
                        }
                        $conn->close();
                        ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <script>
        (function() {
            // Safe DOM getters
            const menuIcon = document.querySelector(".menu-icon");
            const sidebar = document.querySelector(".sidebar");
            const container = document.querySelector(".container");

            // Toggle sidebar safely
            if (menuIcon) {
                menuIcon.addEventListener("click", () => {
                    if (sidebar) sidebar.classList.toggle("hidden");
                    if (container) container.classList.toggle("full");
                    menuIcon.classList.toggle("active");
                    menuIcon.textContent = menuIcon.textContent.trim() === "☰" ? "✖" : "☰";
                });
            }

            // Controls
            const searchInput = document.getElementById("searchInput");
            const statusFilter = document.getElementById("statusFilter");
            const sortBy = document.getElementById("sortBy");
            const tableBody = document.querySelector("main table tbody"); // restrict to this table
            if (!tableBody) return; // nothing to do

            // Make an array of original rows (clones) so we can re-filter reliably
            const originalRows = Array.from(tableBody.querySelectorAll("tr")).map(r => r.cloneNode(true));

            // Helper: normalize status text
            function getStatusTextFromRow(row) {
                const statusCell = row.querySelectorAll("td")[4];
                if (!statusCell) return "";
                return (statusCell.textContent || "").toLowerCase().trim();
            }

            // Helper: show "no records" message
            function showNoRecords() {
                tableBody.innerHTML = "";
                const tr = document.createElement("tr");
                const td = document.createElement("td");
                td.colSpan = 7;
                td.style.textAlign = "center";
                td.style.color = "gray";
                td.textContent = "No librarian records found.";
                tr.appendChild(td);
                tableBody.appendChild(tr);
            }

            // Debounce helper for search
            function debounce(fn, delay = 300) {
                let t;
                return (...args) => {
                    clearTimeout(t);
                    t = setTimeout(() => fn(...args), delay);
                };
            }

            // Main filter + sort
            function filterAndSort() {
                const searchTerm = (searchInput?.value || "").toLowerCase().trim();
                const selectedStatus = (statusFilter?.value || "").toLowerCase();
                const sortKey = (sortBy?.value || "id").toLowerCase();

                // Filter
                let filtered = originalRows.filter(row => {
                    const cells = row.querySelectorAll("td");
                    const name = (cells[1]?.textContent || "").toLowerCase();
                    const email = (cells[2]?.textContent || "").toLowerCase();
                    const status = getStatusTextFromRow(row); // already lowercased
                    const matchesSearch = !searchTerm || name.includes(searchTerm) || email.includes(searchTerm);
                    const matchesStatus = !selectedStatus || status.includes(selectedStatus);
                    return matchesSearch && matchesStatus;
                });

                // Sort
                filtered.sort((a, b) => {
                    const aCells = a.querySelectorAll("td");
                    const bCells = b.querySelectorAll("td");

                    if (sortKey === "name") {
                        return (aCells[1]?.textContent || "").localeCompare(bCells[1]?.textContent || "");
                    }
                    if (sortKey === "status") {
                        return (aCells[4]?.textContent || "").localeCompare(bCells[4]?.textContent || "");
                    }
                    // default: id (descending)
                    const aId = parseInt(aCells[0]?.textContent || "0", 10) || 0;
                    const bId = parseInt(bCells[0]?.textContent || "0", 10) || 0;
                    return bId - aId;
                });

                // Render
                tableBody.innerHTML = "";
                if (filtered.length === 0) {
                    showNoRecords();
                    return;
                }
                filtered.forEach(r => tableBody.appendChild(r));
            }

            // Wire controls safely
            if (searchInput) {
                searchInput.addEventListener("input", debounce(() => {
                    filterAndSort();
                }, 300));
            }

            if (statusFilter) {
                statusFilter.addEventListener("change", () => {
                    filterAndSort();
                });
            }

            if (sortBy) {
                sortBy.addEventListener("change", () => {
                    filterAndSort();
                });
            }

            // Keyboard: allow Escape to clear search
            document.addEventListener("keydown", (e) => {
                if (e.key === "Escape" && searchInput && searchInput.value) {
                    searchInput.value = "";
                    filterAndSort();
                }
            });

            // Run initial filter/sort once DOM is ready
            filterAndSort();

            // Expose function for debugging (optional)
            window.__librarianFilter = {
                run: filterAndSort,
                originalCount: originalRows.length
            };
        })();
    </script>

</body>

</html>