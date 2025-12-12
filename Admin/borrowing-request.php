<?php
include "../PHP/db_connect.php";
session_start();
date_default_timezone_set('Asia/Manila');

// 🔒 Redirect if not logged in
if (!isset($_SESSION['role'])) {
    header("Location: index.php");
    exit();
}

$role = $_SESSION['role'];
$displayName = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : $_SESSION['admin'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Borrowing Requests</title>
    <link rel="icon" type="image/png" href="pictures/Logo.png" />
    <link rel="stylesheet" href="style.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet" />

</head>

<body>
    <header class="main-header">
        <div class="header-left">
            <span class="menu-icon">☰</span>
            <h1>CEIT Thesis Hub</h1>
        </div>
        <div class="header-right">
            <h2>Request</h2>
            <div class="header-logo">
                <img src="pictures/Logo.png" alt="CEIT Logo" width="90" height="60" />
            </div>
        </div>
    </header>

    <div class="container">
        <?php include 'sidebar.php'; ?>

        <main>
            <section class="request-header">
                <h2 class="labelOnLeft">Borrowing Requests</h2>
            </section>

            <section class="request-controls">
                <div class="controls-left">
                    <div class="search-box">
                        <input type="text" id="searchInput" placeholder="Search student or thesis title..." />
                    </div>

                </div>

                <div class="controls-right">
                    <div class="pagination">
                        <button id="prevPage">&lt;</button>
                        <span>Page <span id="currentPage">1</span> of <span id="totalPages">1</span></span>
                        <button id="nextPage">&gt;</button>
                    </div>
                </div>
            </section>

            <div class="status-tabs">
                <button class="tab-btn active" data-status="all">
                    All <span class="count" id="count-all">0</span>
                </button>

                <button class="tab-btn" data-status="Pending">
                    Pending <span class="count" id="count-pending">0</span>
                </button>

                <button class="tab-btn" data-status="For Claiming">
                    For Claiming <span class="count" id="count-forclaiming">0</span>
                </button>

                <button class="tab-btn" data-status="Claimed">
                    Claimed <span class="count" id="count-claimed">0</span>
                </button>

                <button class="tab-btn" data-status="Returned">
                    Returned <span class="count" id="count-returned">0</span>
                </button>

                <button class="tab-btn" data-status="Rejected">
                    Rejected <span class="count" id="count-rejected">0</span>
                </button>
            </div>

            <section class="request-table">
                <div class="bulk-actions">
                    <div class="bulk-left">
                        <label><input type="checkbox" id="selectAllRequests"> <span style="margin-left:6px">Select All</span></label>
                        <span id="bulkCountRequests" style="margin-left:12px;color:gray;"></span>
                    </div>
                    <div class="bulk-right">
                        <button id="deleteSelectedRequests" class="action-btn delete" style="background:#e74c3c">Delete Selected</button>
                    </div>
                </div>

                <table id="requestTable">
                    <thead>
                        <tr>
                            <th style="width:40px"></th>
                            <th style="width: 10%;">Request No.</th>
                            <th style="width: 15%;">Student Name</th>
                            <th style="width: 35%;">Thesis Title</th>
                            <th style="width: 15%;">Department</th>
                            <th style="width: 10%;">Date Requested</th>
                            <th style="width: 15%;">Status</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <?php
                        $sql = "SELECT r.*, t.title, t.author, t.department, t.year, r.librarian_name 
        FROM tbl_borrow_requests r 
        JOIN tbl_thesis t ON r.thesis_id = t.thesis_id 
        ORDER BY r.request_date DESC";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {

                                // Normalize complete statuses if your DB still has old ones
                                $status_display = $row['status'];
                                if (stripos($status_display, 'complete') !== false) {
                                    $status_display = 'Returned';
                                }

                                // JSON encode row for modal
                                $json_data = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');

                                echo "
<tr class='click-row' data-row=\"{$json_data}\"> 

    <td><input type='checkbox' class='bulk-check-request' value='" . htmlspecialchars($row['request_id']) . "'></td>

    <td>{$row['request_number']}</td>

    <td>{$row['student_name']}</td>

    <td class='thesis-title-cell'
        title='" . htmlspecialchars($row['title'], ENT_QUOTES) . "'>
        " . htmlspecialchars($row['title']) . "
    </td>

    <td>{$row['department']}</td>

    <td>" . date('Y-m-d', strtotime($row['request_date'])) . "</td>

    <td>
        <span class='status-badge status-" . strtolower(str_replace(' ', '', $status_display)) . "'>
            {$status_display}
        </span>
    </td>

</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7'>No borrowing requests yet.</td></tr>";
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
        let tabStatus = "all";

        // Pagination + table references
        let currentPage = 1;
        const rowsPerPage = 10;
        const tableBody = document.getElementById("tableBody");
        const allRows = Array.from(tableBody.querySelectorAll("tr"));
        const searchInput = document.getElementById("searchInput");
        const prevPage = document.getElementById("prevPage");
        const nextPage = document.getElementById("nextPage");

        /* ============================================================
           STATUS COUNTS FOR TABS
        ============================================================ */
        function updateStatusCounts() {
            let counts = {
                all: allRows.length,
                pending: 0,
                forclaiming: 0,
                claimed: 0,
                returned: 0,
                rejected: 0
            };

            allRows.forEach(row => {
                const status = row.querySelector("td:nth-child(6)")?.innerText.trim();

                if (status === "Pending") counts.pending++;
                if (status === "For Claiming") counts.forclaiming++;
                if (status === "Claimed") counts.claimed++;
                if (status === "Returned") counts.returned++;
                if (status === "Rejected") counts.rejected++;
            });

            document.getElementById("count-all").textContent = counts.all;
            document.getElementById("count-pending").textContent = counts.pending;
            document.getElementById("count-forclaiming").textContent = counts.forclaiming;
            document.getElementById("count-claimed").textContent = counts.claimed;
            document.getElementById("count-returned").textContent = counts.returned;
            document.getElementById("count-rejected").textContent = counts.rejected;
        }

        /* ============================================================
           SHOPEE-LIKE TABS
        ============================================================ */
        document.querySelectorAll(".tab-btn").forEach(btn => {
            btn.addEventListener("click", () => {
                document.querySelectorAll(".tab-btn").forEach(b => b.classList.remove("active"));
                btn.classList.add("active");

                tabStatus = btn.getAttribute("data-status");

                currentPage = 1;
                filterAndRender();
            });
        });

        /* ============================================================
           REMEMBER LAST TAB AFTER UPDATE
        ============================================================ */
        window.addEventListener("load", () => {
            const lastTab = localStorage.getItem("lastTab");

            if (lastTab) {
                const tab = document.querySelector(`.tab-btn[data-status="${lastTab}"]`);
                if (tab) tab.click();
                localStorage.removeItem("lastTab");
            }
        });

        /* ============================================================
           MAIN FILTERING + PAGINATION
        ============================================================ */
        function filterAndRender() {
            const searchTerm = searchInput.value.toLowerCase();

            const filtered = allRows.filter(row => {
                const cols = row.querySelectorAll("td");
                const name = cols[1]?.textContent.toLowerCase() || "";
                const title = cols[2]?.textContent.toLowerCase() || "";
                const status = cols[5]?.textContent.trim() || "";

                const matchesSearch = name.includes(searchTerm) || title.includes(searchTerm);
                const matchesTab = tabStatus === "all" || status === tabStatus;

                return matchesSearch && matchesTab;
            });

            const totalPages = Math.ceil(filtered.length / rowsPerPage) || 1;
            const start = (currentPage - 1) * rowsPerPage;
            const end = start + rowsPerPage;
            const pageRows = filtered.slice(start, end);

            tableBody.innerHTML = "";
            pageRows.forEach(originalRow => {
                const row = originalRow.cloneNode(true); // make a fresh copy
                tableBody.appendChild(row);
            });

            document.getElementById("currentPage").textContent = currentPage;
            document.getElementById("totalPages").textContent = totalPages;

            prevPage.disabled = currentPage === 1;
            nextPage.disabled = currentPage === totalPages;

            attachRowEvents();
        }

        // Search + pagination listeners
        searchInput.addEventListener("input", () => {
            currentPage = 1;
            filterAndRender();
        });

        prevPage.addEventListener("click", () => {
            if (currentPage > 1) {
                currentPage--;
                filterAndRender();
            }
        });

        nextPage.addEventListener("click", () => {
            currentPage++;
            filterAndRender();
        });

        // INITIAL RENDER
        updateStatusCounts();
        filterAndRender();

        function attachRowEvents() {
            document.querySelectorAll(".click-row").forEach(row => {
                row.style.cursor = "pointer";

                // prevent checkbox clicks from opening modal
                const cb = row.querySelector('.bulk-check-request');
                if (cb) cb.addEventListener('click', (ev) => ev.stopPropagation());

                row.addEventListener("click", () => {
                    const data = row.getAttribute("data-row");
                    openModal({
                        getAttribute: () => data
                    });
                });
            });
        }

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
                "For Claiming": "status-for-claiming",
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


        /* ============================================================
           STATUS UPDATE (AJAX)
        ============================================================ */
        async function updateStatus(requestId, newStatus) {
            if (!(await confirmPopup('Are you sure you want to mark this as ' + newStatus + '?', {title: 'Confirm Status Change'}))) return;

            fetch("update-request-status.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: "request_id=" + requestId + "&new_status=" + newStatus
                })
                .then(res => res.text())
                .then(msg => {
                    Swal.fire({ icon: 'success', title: msg }).then(() => {
                        localStorage.setItem("lastTab", newStatus);
                        location.reload();
                    });
                })
                .catch(() => Swal.fire({ icon: 'error', title: 'Error updating status.' }));
        }

        /* ============================================================
           MODAL CONTROL
        ============================================================ */
        function closeModal() {
            document.getElementById("viewModal").style.display = "none";
        }

        // window.onclick = function(event) {
        //     const modal = document.getElementById("viewModal");
        //     if (event.target === modal) closeModal();
        // };

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

        // Bulk actions for requests
        (function() {
            const selectAll = document.getElementById('selectAllRequests');
            const deleteBtn = document.getElementById('deleteSelectedRequests');
            const bulkCount = document.getElementById('bulkCountRequests');

            function updateBulkCount() {
                const checked = document.querySelectorAll('.bulk-check-request:checked').length;
                if (bulkCount) bulkCount.textContent = checked ? `${checked} selected` : '';
            }

            document.addEventListener('change', (e) => {
                if (e.target && e.target.classList && e.target.classList.contains('bulk-check-request')) {
                    updateBulkCount();
                }
            });

            if (selectAll) selectAll.addEventListener('change', () => {
                const checks = document.querySelectorAll('.bulk-check-request');
                checks.forEach(c => c.checked = selectAll.checked);
                updateBulkCount();
            });

            if (deleteBtn) deleteBtn.addEventListener('click', async () => {
                const ids = Array.from(document.querySelectorAll('.bulk-check-request:checked')).map(i => i.value);
                if (!ids.length) return Swal.fire({icon:'info', title: 'Please select at least one request to delete.'});
                if (!(await confirmPopup('Are you sure you want to delete selected requests?', {title: 'Confirm Delete', confirmText: 'Delete'}))) return;

                const form = new FormData();
                form.append('action', 'delete');
                form.append('type', 'borrow_requests');
                ids.forEach(id => form.append('ids[]', id));

                fetch('bulk_action.php', { method: 'POST', body: form })
                    .then(r => r.json())
                    .then(j => {
                        if (j.success) location.reload(); else Swal.fire({icon:'error', title: j.message || 'Error'});
                    })
                    .catch(() => Swal.fire({icon:'error', title: 'Network or server error'}));
            });
        })();
    </script>

</body>

</html>