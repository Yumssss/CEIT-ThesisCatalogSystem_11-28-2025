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
    <title>Manage Thesis</title>
    <link rel="icon" type="image/png" href="pictures/Logo.png" />
    <link rel="stylesheet" href="style.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet" />
</head>

<body>
    <!-- HEADER -->
    <header class="main-header">
        <div class="header-left">
            <span class="menu-icon">☰</span>
            <h1>CEIT Thesis Hub</h1>
        </div>
        <div class="header-right">
            <h2>Manage Thesis</h2>
            <div class="header-logo">
                <img src="pictures/Logo.png" alt="CEIT Logo" width="90" height="60" />
            </div>
        </div>
    </header>

    <div class="container">
        <?php include 'sidebar.php'; ?>

        <!-- MAIN CONTENT -->
        <main>
            <!-- MANAGE THESIS CONTENT -->
            <div id="thesisContainer">
                <h2 class="section-title">Manage Thesis</h2>

                <div class="add-thesis-center">
                    <button id="addThesisBtn" class="add-btn-header">📄 Add New Thesis</button>
                </div>

                <!-- Control Bar -->
                <div class="table-controls-wrapper">
                    <div class="controls-left">
                        <input type="text" id="searchInput" placeholder="Search by title or author...">
                        <div class="filter-dropdown">
                            <button type="button" id="filterToggleBtn">Filter</button>

                            <div id="thesisFilterMenu" class="thesis-filter-menu" aria-hidden="true">
                                <div class="filter-group">
                                    <p class="filter-label">Department</p>
                                    <label><input type="checkbox" name="filter-dept" class="filter-dept" value="Information Technology"> Information Technology</label>
                                    <label><input type="checkbox" name="filter-dept" class="filter-dept" value="Civil Engineering"> Civil Engineering</label>
                                    <label><input type="checkbox" name="filter-dept" class="filter-dept" value="Electrical Engineering"> Electrical Engineering</label>
                                </div>

                                <hr>

                                <div class="filter-group">
                                    <p class="filter-label">Availability</p>
                                    <label><input type="checkbox" name="filter-availability" class="filter-availability" value="Available"> Available</label>
                                    <label><input type="checkbox" name="filter-availability" class="filter-availability" value="Unavailable"> Unavailable</label>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="controls-right">
                        <div class="pagination">
                            <button id="prevPage">&lt;</button>
                            <span>Page <span id="currentPage">1</span> of <span id="totalPages">1</span></span>
                            <button id="nextPage">&gt;</button>
                        </div>
                    </div>
                </div>

                <!-- Thesis Table -->
                <table>
                    <thead>
                        <tr>
                            <th style="width: 40%;">Title</th>
                            <th style="width: 25%;">Author(s)</th>
                            <th style="width: 10%;">Year</th>
                            <th style="width: 10%;">Department</th>
                            <th style="width: 15%;">Availability</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT * FROM tbl_thesis ORDER BY year DESC";
                        $result = $conn->query($query);

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $jsonData = json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG);
                        ?>
                                <tr data-id="<?= htmlspecialchars($row['thesis_id']) ?>">
                                    <td><?= htmlspecialchars($row['title']) ?></td>
                                    <td><?= htmlspecialchars($row['author']) ?></td>
                                    <td><?= htmlspecialchars($row['year']) ?></td>
                                    <td><?= htmlspecialchars($row['department']) ?></td>
                                    <td style="text-align: center;">
                                        <?php
                                        $avail = strtolower($row['availability']);
                                        $class = $avail === "available" ? "avail-available" : "avail-unavailable";
                                        ?>
                                        <span class="availability-badge <?= $class ?>">
                                            <?= htmlspecialchars($row['availability']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="action-btn edit-btn" onclick='openEditModal(<?= $jsonData ?>)'>
                                            Edit
                                        </button>
                                    </td>
                                </tr>
                        <?php
                            }
                        } else {
                            echo "<tr><td colspan='6'>No thesis records found.</td></tr>";
                        }
                        $conn->close();
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- ADD THESIS PAGE (Hidden by default) -->
            <section id="addThesisPage" class="add-thesis-page hidden fade-out">
                <div class="add-header">
                    <button id="backToThesisBtn" class="back-btn">← Back</button>
                    <br><br>
                    <h2>Add New Thesis</h2>
                </div>

                <form id="addThesisForm" action="add_thesis.php" method="POST" class="add-thesis-form">
                    <div class="form-group">
                        <label for="title">Thesis Title</label>
                        <input type="text" id="title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="author">Author(s)</label>
                        <input type="text" id="author" name="author" required>
                    </div>
                    <div class="form-group">
                        <label for="year">Year</label>
                        <input type="number" id="year" name="year" placeholder="YYYY" required>
                    </div>
                    <div class="form-group">
                        <label for="department">Department</label>
                        <select id="department" name="department" required>
                            <option value="">Select Department</option>
                            <option value="Information Technology">Information Technology</option>
                            <option value="Civil Engineering">Civil Engineering</option>
                            <option value="Electrical Engineering">Electrical Engineering</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="abstract">Abstract</label>
                        <textarea id="abstract" name="abstract" rows="5" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="availability">Availability</label>
                        <select id="availability" name="availability" required>
                            <option value="">Select...</option>
                            <option value="Available">Available</option>
                            <option value="Unavailable">Unavailable</option>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="add-btn">Add Thesis</button>
                    </div>
                </form>
            </section>
        </main>

        <!-- FILTER DROPDOWN MENU -->
        <div id="thesisFilterMenu" class="thesis-filter-menu" aria-hidden="true">
            <div class="filter-group">
                <p class="filter-label">Department</p>
                <label><input type="checkbox" name="filter-dept" class="filter-dept" value="Information Technology"> Information Technology</label>
                <label><input type="checkbox" name="filter-dept" class="filter-dept" value="Civil Engineering"> Civil Engineering</label>
                <label><input type="checkbox" name="filter-dept" class="filter-dept" value="Electrical Engineering"> Electrical Engineering</label>
            </div>

            <hr>

            <div class="filter-group">
                <p class="filter-label">Availability</p>
                <label><input type="checkbox" name="filter-availability" class="filter-availability" value="Available"> Available</label>
                <label><input type="checkbox" name="filter-availability" class="filter-availability" value="Unavailable"> Unavailable</label>
            </div>
        </div>


        <!-- EDIT MODAL -->
        <div class="modal" id="editModal" style="display: none;">
            <div class="modal-content">
                <span class="close-modal" id="editModalCloseBtn">&times;</span>
                <h3>Edit Thesis</h3>
                <form id="editForm">
                    <input type="hidden" id="edit_thesis_id" name="thesis_id">
                    <div class="form-group"><label>Title</label><input type="text" id="edit_title" name="title" required></div>
                    <div class="form-group"><label>Author(s)</label><input type="text" id="edit_author" name="author" required></div>
                    <div class="form-group"><label>Year</label><input type="number" id="edit_year" name="year" required></div>
                    <div class="form-group"><label>Department</label>
                        <select id="edit_department" name="department" required>
                            <option value="Information Technology">Information Technology</option>
                            <option value="Civil Engineering">Civil Engineering</option>
                            <option value="Electrical Engineering">Electrical Engineering</option>
                        </select>
                    </div>
                    <div class="form-group"><label>Availability</label>
                        <select id="edit_availability" name="availability" required>
                            <option value="Available">Available</option>
                            <option value="Unavailable">Unavailable</option>
                        </select>
                    </div>
                    <div class="form-group"><label>Abstract</label><textarea id="edit_abstract" name="abstract" rows="3"></textarea></div>
                    <div class="modal-footer">
                        <span class="last-updated" id="lastUpdatedText">Last Updated: —</span>
                        <div>
                            <button type="submit" class="save-btn">💾 Save</button>
                            <button type="button" class="delete-btn" id="deleteBtn">🗑 Delete</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div id="toastContainer" class="toast-container"></div>
    </div>

    <!-- ✅ FULL FIXED SCRIPT -->
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const menuIcon = document.querySelector(".menu-icon");
            const sidebar = document.querySelector(".sidebar");
            const container = document.querySelector(".container");

            const addThesisBtn = document.getElementById("addThesisBtn");
            const addThesisPage = document.getElementById("addThesisPage");
            const backToThesisBtn = document.getElementById("backToThesisBtn");
            const thesisContainer = document.getElementById("thesisContainer");

            const searchInput = document.getElementById("searchInput");
            const tableBody = document.querySelector("main table tbody");
            const allRows = Array.from(tableBody ? tableBody.querySelectorAll("tr") : []).map(r => r.cloneNode(true));

            const filterToggleBtn = document.getElementById("filterToggleBtn");
            const filterMenu = document.getElementById("thesisFilterMenu");
            const filterDept = Array.from(document.querySelectorAll(".filter-dept"));
            const filterAvail = Array.from(document.querySelectorAll(".filter-availability"));
            const prevPage = document.getElementById("prevPage");
            const nextPage = document.getElementById("nextPage");
            const currentPageDisplay = document.getElementById("currentPage");
            const totalPagesDisplay = document.getElementById("totalPages");

            let currentPage = 1;
            const rowsPerPage = 10;

            /* Sidebar toggle */
            // if (menuIcon) {
            //     menuIcon.addEventListener("click", () => {
            //         sidebar.classList.toggle("hidden");
            //         container.classList.toggle("full");
            //         menuIcon.textContent = menuIcon.textContent.trim() === "☰" ? "✖" : "☰";
            //     });
            // }

            /* Add Thesis toggle */
            if (addThesisBtn && addThesisPage && thesisContainer) {
                addThesisBtn.addEventListener("click", () => {
                    thesisContainer.classList.add("hidden");
                    addThesisPage.classList.remove("hidden");
                    addThesisPage.classList.remove("fade-out");
                    addThesisPage.classList.add("fade-in");
                    hideFilterMenu();
                    addThesisPage.scrollIntoView({
                        behavior: "smooth"
                    });
                });
            }

            if (backToThesisBtn && thesisContainer && addThesisPage) {
                backToThesisBtn.addEventListener("click", () => {
                    addThesisPage.classList.add("fade-out");
                    setTimeout(() => {
                        addThesisPage.classList.add("hidden");
                        thesisContainer.classList.remove("hidden");
                        thesisContainer.classList.add("fade-in");
                        thesisContainer.scrollIntoView({
                            behavior: "smooth"
                        });
                    }, 220);
                });
            }

            /* Filter menu logic — anchored dropdown version */
            if (filterToggleBtn && filterMenu) {
                // Toggle visibility when clicking the Filter button
                filterToggleBtn.addEventListener("click", (e) => {
                    e.stopPropagation();
                    filterMenu.classList.toggle("visible");
                    filterToggleBtn.classList.toggle("active");
                });

                // Close dropdown when clicking outside
                document.addEventListener("click", (ev) => {
                    if (!filterToggleBtn.contains(ev.target) && !filterMenu.contains(ev.target)) {
                        filterMenu.classList.remove("visible");
                        filterToggleBtn.classList.remove("active");
                    }
                });
            }


            /* Search + Filter + Pagination */
            function getSelectedValues(nodeList) {
                return nodeList.filter(chk => chk.checked).map(chk => chk.value);
            }

            function applySearchAndFilters() {
                currentPage = 1;
                renderFiltered();
            }

            function renderFiltered() {
                const searchTerm = (searchInput?.value || "").toLowerCase().trim();
                const selectedDepts = getSelectedValues(filterDept);
                const selectedAvail = getSelectedValues(filterAvail);

                const filtered = allRows.filter(row => {
                    const cells = row.querySelectorAll("td");
                    if (!cells || cells.length === 0) return false;
                    const title = (cells[0].textContent || "").toLowerCase();
                    const author = (cells[1].textContent || "").toLowerCase();
                    const dept = (cells[3].textContent || "").trim();
                    const availability = (cells[4].textContent || "").trim();

                    const matchesSearch = !searchTerm || title.includes(searchTerm) || author.includes(searchTerm);
                    const matchesDept = selectedDepts.length === 0 || selectedDepts.includes(dept);
                    const matchesAvail = selectedAvail.length === 0 || selectedAvail.includes(availability);
                    return matchesSearch && matchesDept && matchesAvail;
                });

                renderPage(filtered);
            }

            function renderPage(filteredRows) {
                const totalPages = Math.max(1, Math.ceil(filteredRows.length / rowsPerPage));
                if (currentPage > totalPages) currentPage = totalPages;
                if (currentPage < 1) currentPage = 1;

                const start = (currentPage - 1) * rowsPerPage;
                const end = start + rowsPerPage;
                const pageRows = filteredRows.slice(start, end);

                tableBody.innerHTML = "";
                if (pageRows.length === 0) {
                    const tr = document.createElement("tr");
                    const td = document.createElement("td");
                    td.colSpan = 6;
                    td.style.textAlign = "center";
                    td.style.color = "gray";
                    td.textContent = "No thesis records found.";
                    tr.appendChild(td);
                    tableBody.appendChild(tr);
                } else {
                    pageRows.forEach(row => tableBody.appendChild(row.cloneNode(true)));
                }

                currentPageDisplay.textContent = currentPage;
                totalPagesDisplay.textContent = totalPages;
                prevPage.disabled = currentPage === 1;
                nextPage.disabled = currentPage === totalPages;
            }

            // 🔹 NEW: Single-selection filter setup
            function setupSingleSelectFilters() {
                const deptFilters = document.querySelectorAll('.filter-dept');
                const availFilters = document.querySelectorAll('.filter-availability');

                deptFilters.forEach(chk => {
                    chk.addEventListener('change', () => {
                        if (chk.checked) deptFilters.forEach(other => {
                            if (other !== chk) other.checked = false;
                        });
                        applySearchAndFilters();
                    });
                });

                availFilters.forEach(chk => {
                    chk.addEventListener('change', () => {
                        if (chk.checked) availFilters.forEach(other => {
                            if (other !== chk) other.checked = false;
                        });
                        applySearchAndFilters();
                    });
                });
            }

            if (searchInput) searchInput.addEventListener("input", applySearchAndFilters);
            setupSingleSelectFilters();

            if (prevPage) prevPage.addEventListener("click", () => {
                if (currentPage > 1) {
                    currentPage--;
                    renderFiltered();
                }
            });
            if (nextPage) nextPage.addEventListener("click", () => {
                currentPage++;
                renderFiltered();
            });
            renderFiltered();


            /* Modal */
            const editModal = document.getElementById("editModal");
            const editModalCloseBtn = document.getElementById("editModalCloseBtn");
            const editForm = document.getElementById("editForm");
            const deleteBtn = document.getElementById("deleteBtn");
            const toastContainer = document.getElementById("toastContainer");

            window.openEditModal = function(data) {
                try {
                    if (typeof data === "string") data = JSON.parse(data);
                } catch {}
                document.getElementById("edit_thesis_id").value = data.thesis_id || "";
                document.getElementById("edit_title").value = data.title || "";
                document.getElementById("edit_author").value = data.author || "";
                document.getElementById("edit_year").value = data.year || "";
                document.getElementById("edit_department").value = data.department || "";
                document.getElementById("edit_availability").value = data.availability || "";
                document.getElementById("edit_abstract").value = data.abstract || "";
                document.getElementById("lastUpdatedText").textContent = "Last Updated: " + (data.last_updated || "—");
                editModal.style.display = "flex";
            };

            function closeEditModal() {
                if (editModal) editModal.style.display = "none";
            }
            if (editModalCloseBtn) editModalCloseBtn.addEventListener("click", closeEditModal);
            window.addEventListener("click", ev => {
                if (ev.target === editModal) closeEditModal();
            });

            if (editForm) {
                editForm.addEventListener("submit", ev => {
                    ev.preventDefault();
                    const formData = new FormData(editForm);
                    fetch("update_thesis.php", {
                            method: "POST",
                            body: formData
                        })
                        .then(r => r.text())
                        .then(msg => {
                            showToast(msg || "Updated successfully", "success");
                            closeEditModal();
                            setTimeout(() => location.reload(), 700);
                        })
                        .catch(() => showToast("Error updating thesis.", "error"));
                });
            }

            if (deleteBtn) {
                deleteBtn.addEventListener("click", () => {
                    const thesisId = document.getElementById("edit_thesis_id").value;
                    if (!thesisId) return showToast("Missing thesis id", "error");
                    if (!confirm("Are you sure you want to delete this thesis?")) return;
                    fetch("delete_thesis.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: `thesis_id=${encodeURIComponent(thesisId)}`
                        }).then(r => r.text())
                        .then(msg => {
                            showToast(msg || "Deleted", "info");
                            closeEditModal();
                            setTimeout(() => location.reload(), 700);
                        })
                        .catch(() => showToast("Error deleting thesis.", "error"));
                });
            }

            function showToast(message, type = "info", duration = 2500) {
                const toast = document.createElement("div");
                toast.className = `toast ${type}`;
                toast.textContent = message;
                toastContainer.appendChild(toast);
                setTimeout(() => {
                    toast.style.opacity = "0";
                    toast.style.transform = "translateX(10px)";
                    setTimeout(() => toast.remove(), 250);
                }, duration);
            }
        });

        const menuIcon = document.querySelector(".menu-icon");
        const sidebar = document.querySelector(".sidebar");
        const container = document.querySelector(".container");

        menuIcon.addEventListener("click", () => {
            sidebar.classList.toggle("hidden");
            container.classList.toggle("full");
            menuIcon.classList.toggle("active");

            // Optional: change icon to "X"
            if (menuIcon.textContent === "☰") {
                menuIcon.textContent = "✖";
            } else {
                menuIcon.textContent = "☰";
            }
        });
    </script>
</body>

</html>