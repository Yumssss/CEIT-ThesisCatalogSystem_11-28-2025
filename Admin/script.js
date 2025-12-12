// script.js (merged)
// - Keeps original manage-thesis functionality
// - Adds: search/filter, bulk-mode toggle + bulk actions (real DB ops),
//         duplicate detection on add, toast notifications, and confirmations.

document.addEventListener("DOMContentLoaded", () => {
    // ----------------------------
    // Helper: safe element getter
    // ----------------------------
    const $ = (sel) => document.querySelector(sel);
    const $$ = (sel) => Array.from(document.querySelectorAll(sel));

    // ----------------------------
    // Toast helper
    // ----------------------------
    function showToast(message, type = "success", timeout = 3000) {
        const container =
            document.getElementById("toastContainer") ||
            (() => {
                const c = document.createElement("div");
                c.id = "toastContainer";
                c.className = "toast-container";
                document.body.appendChild(c);
                return c;
            })();

        const toast = document.createElement("div");
        toast.className = `toast ${type}`;
        toast.textContent = message;
        container.appendChild(toast);
        setTimeout(() => {
            toast.style.transition = "opacity .25s";
            toast.style.opacity = "0";
            setTimeout(() => toast.remove(), 250);
        }, timeout);
    }

    // ----------------------------
    // 1) ADD / CANCEL FORM TOGGLE (Manage Thesis)
    // ----------------------------
    const addThesisBtn = $("#addThesisBtn");
    const cancelBtn = $("#cancelFormBtn");
    const thesisOverview = $("#thesisOverview");
    const addThesisFormSection = $("#addThesisFormSection");

    if (addThesisBtn && cancelBtn && thesisOverview && addThesisFormSection) {
        addThesisBtn.addEventListener("click", () => {
            thesisOverview.classList.add("fade-out");
            setTimeout(() => {
                thesisOverview.classList.add("hidden");
                addThesisFormSection.classList.remove("hidden", "fade-out");
                addThesisFormSection.classList.add("fade-in");
            }, 300);
        });

        cancelBtn.addEventListener("click", () => {
            addThesisFormSection.classList.add("fade-out");
            setTimeout(() => {
                addThesisFormSection.classList.add("hidden");
                thesisOverview.classList.remove("hidden", "fade-out");
                thesisOverview.classList.add("fade-in");
            }, 300);
        });
    }

    // ----------------------------
    // 2) DROPDOWN MENU LOGIC (if present)
    // ----------------------------
    const dropdownBtn = $("#filterDropdownBtn");
    const dropdownMenu = $("#filterMenu");

    if (dropdownBtn && dropdownMenu) {
        dropdownBtn.addEventListener("click", () => {
            dropdownMenu.classList.toggle("hidden");
        });

        dropdownMenu.querySelectorAll("li").forEach((item) => {
            item.addEventListener("click", () => {
                dropdownBtn.innerHTML = `${item.textContent} <span class="arrow">▼</span>`;
                dropdownMenu.classList.add("hidden");
            });
        });

        document.addEventListener("click", (e) => {
            if (
                !dropdownBtn.contains(e.target) &&
                !dropdownMenu.contains(e.target)
            ) {
                dropdownMenu.classList.add("hidden");
            }
        });
    }

    // ----------------------------
    // 3) SIDEBAR ACTIVE PAGE HIGHLIGHT
    // ----------------------------
    const currentPage = window.location.pathname.split("/").pop();
    const navLinks = document.querySelectorAll(".sidebar nav a");
    navLinks.forEach((link) => {
        const linkPage = link.getAttribute("href");
        if (linkPage === currentPage) link.classList.add("active");
        else link.classList.remove("active");
    });

    // ----------------------------
    // 4) MANAGE THESIS LOGIC (Edit/Delete Modal) - existing behavior preserved
    // ----------------------------
    const editModal = $("#editModal");
    const editForm = $("#editForm");
    const deleteBtn = $("#deleteBtn");
    const lastUpdatedText = $("#lastUpdatedText");
    const editModalCloseBtn = $("#editModalCloseBtn");
    let currentId = null;

    // openEditModal exposed globally by PHP onclick; populate fields and open modal
    window.openEditModal = function (data) {
        if (!editModal || !editForm) return;
        currentId = data.thesis_id ?? data.id ?? null;
        if (currentId === null) {
            // try to read from data-id on row as fallback
            console.warn("openEditModal: no id in data, check row data");
        }

        const setIf = (selector, value) => {
            const el = editForm.querySelector(selector);
            if (el) el.value = value ?? "";
        };

        setIf("#edit_thesis_id", data.thesis_id ?? "");
        setIf("#edit_title", data.title ?? "");
        setIf("#edit_author", data.author ?? "");
        setIf("#edit_year", data.year ?? "");
        setIf("#edit_department", data.department ?? "");
        setIf("#edit_availability", data.availability ?? "");
        setIf("#edit_abstract", data.abstract ?? "");
        if (lastUpdatedText)
            lastUpdatedText.textContent =
                "Last Updated: " + (data.last_updated ?? "—");
        editModal.style.display = "flex";
    };

    function closeEditModal() {
        if (editModal) editModal.style.display = "none";
    }
    if (editModalCloseBtn)
        editModalCloseBtn.addEventListener("click", closeEditModal);

    // handle editForm submit -> uses your existing update_thesis.php (returns text)
    if (editForm) {
        editForm.addEventListener("submit", (e) => {
            e.preventDefault();
            // show saving toast, send form via fetch, then reload to reflect DB
            fetch("update_thesis.php", {
                method: "POST",
                body: new FormData(editForm),
            })
                .then((res) => res.text())
                .then((msg) => {
                    // your update endpoint returns text (alerts). We show toast and reload.
                    showToast("Saved — reloading...", "success");
                    closeEditModal();
                    setTimeout(() => location.reload(), 700);
                })
                .catch((err) => {
                    console.error(err);
                    showToast("Error updating thesis", "error");
                });
        });
    }

    // single delete (edit modal) - confirm then use delete_thesis.php (expects POST id param 'id')
    if (deleteBtn) {
        deleteBtn.addEventListener("click", async (e) => {
            e.preventDefault();
            if (!currentId) {
                showToast("Missing thesis id", "error");
                return;
            }
            if (!(await confirmPopup('Are you sure you want to delete this thesis?', {title: 'Confirm Delete', confirmText: 'Delete'}))) return;
            fetch("delete_thesis.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: "id=" + encodeURIComponent(currentId),
            })
                .then((r) => r.text())
                .then((msg) => {
                    showToast("Thesis deleted", "success");
                    closeEditModal();
                    setTimeout(() => location.reload(), 700);
                })
                .catch((err) => {
                    console.error(err);
                    showToast("Error deleting thesis", "error");
                });
        });
    }

    // close edit modal if clicked outside
    window.addEventListener("click", (event) => {
        if (editModal && event.target === editModal) closeEditModal();
    });

    // ----------------------------
    // 5) SEARCH + FILTER (client-side)
    // ----------------------------
    const searchInput = $("#searchInput");
    const deptFilterSelect = $("#filterDepartment");
    const availFilterSelect = $("#filterAvailability");
    const deptCheckboxes = document.querySelectorAll(".filter-dept");
    const availCheckboxes = document.querySelectorAll(".filter-availability");

    function getHeaderIndexes() {
        const ths = Array.from(document.querySelectorAll("table thead tr th")).map(th => th.textContent.trim().toLowerCase());
        return {
            title: ths.findIndex(t => t.includes("title")),
            author: ths.findIndex(t => t.includes("author")),
            department: ths.findIndex(t => t.includes("department")),
            availability: ths.findIndex(t => t.includes("availability")),
        };
    }

    function filterTable() {
        if (!searchInput && !deptFilterSelect && !deptCheckboxes.length && !availFilterSelect && !availCheckboxes.length) return;
        const q = (searchInput?.value || "").trim().toLowerCase();

        // collect selected department(s)
        let selectedDepts = [];
        if (deptFilterSelect) {
            if (deptFilterSelect.value) selectedDepts = [deptFilterSelect.value];
        } else if (deptCheckboxes && deptCheckboxes.length) {
            selectedDepts = Array.from(deptCheckboxes).filter(cb => cb.checked).map(cb => cb.value);
        }

        // collect selected availability(s)
        let selectedAvails = [];
        if (availFilterSelect) {
            if (availFilterSelect.value) selectedAvails = [availFilterSelect.value];
        } else if (availCheckboxes && availCheckboxes.length) {
            selectedAvails = Array.from(availCheckboxes).filter(cb => cb.checked).map(cb => cb.value);
        }

        // table rows
        const tbody = document.querySelector("tbody");
        if (!tbody) return;

        const idx = getHeaderIndexes();
        Array.from(tbody.rows).forEach((row) => {
            const cols = Array.from(row.querySelectorAll("td"));
            const titleCell = cols[idx.title] || cols[0];
            const authorCell = cols[idx.author] || cols[1];
            const deptCell = cols[idx.department] || cols[3] || null;
            const availCell = cols[idx.availability] || cols[4] || null;

            const title = (titleCell?.textContent || "").toLowerCase();
            const author = (authorCell?.textContent || "").toLowerCase();
            const department = (deptCell?.textContent || "").trim();
            const availability = (availCell?.textContent || "").trim();

            const matchesQuery = q === "" || title.includes(q) || author.includes(q);
            const matchesDept = selectedDepts.length === 0 || selectedDepts.includes(department);
            const matchesAvail = selectedAvails.length === 0 || selectedAvails.includes(availability);

            row.style.display = matchesQuery && matchesDept && matchesAvail ? "" : "none";
        });
    }

    // wire up events: search input + selects or checkboxes
    if (searchInput) {
        searchInput.addEventListener("input", filterTable);
        searchInput.addEventListener("change", filterTable);
    }
    if (deptFilterSelect) {
        deptFilterSelect.addEventListener("input", filterTable);
        deptFilterSelect.addEventListener("change", filterTable);
    }
    if (availFilterSelect) {
        availFilterSelect.addEventListener("input", filterTable);
        availFilterSelect.addEventListener("change", filterTable);
    }
    if (deptCheckboxes && deptCheckboxes.length) {
        deptCheckboxes.forEach(cb => cb.addEventListener('change', filterTable));
    }
    if (availCheckboxes && availCheckboxes.length) {
        availCheckboxes.forEach(cb => cb.addEventListener('change', filterTable));
    }

    // ----------------------------
    // 6) BULK MODE TOGGLE + BULK ACTIONS (real DB operations)
    // ----------------------------
    const bulkToggleBtn = $("#bulkToggleBtn");
    const bulkActions = $("#bulkActions");
    const bulkDeleteBtn = $("#bulkDeleteBtn");
    const bulkAvailableBtn = $("#bulkAvailableBtn");
    const bulkUnavailableBtn = $("#bulkUnavailableBtn");
    const exitBulkBtn = $("#exitBulkBtn");
    const tbody = document.querySelector("tbody");

    // guard
    if (bulkToggleBtn && tbody) {
        let bulkMode = false;

        function enableBulkMode() {
            if (bulkMode) return;
            bulkMode = true;
            document.body.classList.add("bulk-mode");
            bulkActions.classList.remove("hidden");
            bulkToggleBtn.style.display = "none";

            // add header checkbox (select all) if not present
            const theadRow = document.querySelector("table thead tr");
            if (theadRow && !theadRow.querySelector(".bulk-header-cell")) {
                const th = document.createElement("th");
                th.className = "bulk-header-cell";
                th.innerHTML = `<input id="bulkSelectAll" type="checkbox">`;
                theadRow.insertAdjacentElement("afterbegin", th);
            }

            // insert checkboxes as first cell in each visible row
            Array.from(tbody.rows).forEach((row) => {
                if (row.querySelector(".bulk-checkbox")) return; // already added
                const td = document.createElement("td");
                td.className = "bulk-cell";
                td.style.textAlign = "center";
                const cb = document.createElement("input");
                cb.type = "checkbox";
                cb.className = "bulk-checkbox";
                td.appendChild(cb);
                row.insertAdjacentElement("afterbegin", td);
            });

            // wire select all
            const bulkSelectAll = $("#bulkSelectAll");
            if (bulkSelectAll) {
                bulkSelectAll.addEventListener("change", (e) => {
                    const checked = e.target.checked;
                    $$(".bulk-checkbox").forEach((c) => (c.checked = checked));
                });
            }
        }

        function disableBulkMode() {
            if (!bulkMode) return;
            bulkMode = false;
            document.body.classList.remove("bulk-mode");
            bulkActions.classList.add("hidden");
            bulkToggleBtn.style.display = "inline-block";
            bulkToggleBtn.textContent = "🗂 Select";
            // remove header bulk cell
            const theadRow = document.querySelector("table thead tr");
            const bulkHeader = theadRow?.querySelector(".bulk-header-cell");
            if (bulkHeader) bulkHeader.remove();
            // remove each row's first bulk cell
            $$(".bulk-cell").forEach((el) => el.remove());
        }

        bulkToggleBtn.addEventListener("click", () => {
            if (bulkMode) disableBulkMode();
            else enableBulkMode();
        });

        exitBulkBtn && exitBulkBtn.addEventListener("click", disableBulkMode);

        // helper: get selected IDs (reads data-id on <tr>)
        function getSelectedIds() {
            const ids = [];
            $$(".bulk-checkbox:checked").forEach((cb) => {
                const tr = cb.closest("tr");
                if (!tr) return;
                const id = tr.dataset.id ?? tr.getAttribute("data-id") ?? null;
                if (id) ids.push(id);
            });
            return ids;
        }

        // send bulk action to backend (bulk_action.php)
        async function sendBulk(action, ids) {
            if (!ids.length) {
                showToast("No items selected", "info");
                return;
            }
            const confirmMsg =
                action === "delete"
                    ? `Are you sure you want to delete ${ids.length} thesis(es)? This cannot be undone.`
                    : action === "available"
                    ? `Are you sure you want to set ${ids.length} thesis(es) as AVAILABLE?`
                    : `Are you sure you want to set ${ids.length} thesis(es) as UNAVAILABLE?`;
            if (!(await confirmPopup(confirmMsg, {title: 'Confirm Bulk Action', confirmText: 'Proceed'}))) return;

            // build form data
            const fd = new FormData();
            fd.append("action", action);
            ids.forEach((i) => fd.append("ids[]", i));

            try {
                const res = await fetch("bulk_action.php", {
                    method: "POST",
                    body: fd,
                });
                const data = await res.json();
                if (data.success) {
                    showToast(
                        data.message || "Bulk action completed",
                        "success"
                    );
                    // update UI: remove rows if delete, or change availability text
                    if (action === "delete") {
                        ids.forEach((id) => {
                            const row = document.querySelector(
                                `tr[data-id="${id}"]`
                            );
                            if (row) row.remove();
                        });
                    } else {
                        const newVal =
                            action === "available"
                                ? "Available"
                                : "Unavailable";
                        ids.forEach((id) => {
                            const row = document.querySelector(
                                `tr[data-id="${id}"]`
                            );
                            if (row) {
                                // availability cell was originally the 5th cell (but with bulk cell, index shifts)
                                // safest: find the cell that contains text matching Available/Unavailable by header index
                                // Here: after adding bulk checkbox, availability is at td:nth-child(6). We'll try both.
                                let availCell =
                                    row.querySelector("td:nth-child(6)") ||
                                    row.querySelector("td:nth-child(5)");
                                if (availCell) availCell.textContent = newVal;
                            }
                        });
                    }
                    // exit bulk mode after action
                    disableBulkMode();
                } else {
                    showToast(data.message || "Bulk action failed", "error");
                }
            } catch (err) {
                console.error(err);
                showToast("Network or server error", "error");
            }
        }

        // wire bulk buttons
        bulkDeleteBtn &&
            bulkDeleteBtn.addEventListener("click", () =>
                sendBulk("delete", getSelectedIds())
            );
        bulkAvailableBtn &&
            bulkAvailableBtn.addEventListener("click", () =>
                sendBulk("available", getSelectedIds())
            );
        bulkUnavailableBtn &&
            bulkUnavailableBtn.addEventListener("click", () =>
                sendBulk("unavailable", getSelectedIds())
            );
    } // end bulk guard

    // ----------------------------
    // 7) DUPLICATE CHECK (on add form) - uses check_duplicate.php
    // ----------------------------
    const addForm = $("#addThesisForm");
    if (addForm) {
        addForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            const titleEl = $("#title");
            const yearEl = $("#year");
            const title = titleEl?.value.trim() ?? "";
            const year = yearEl?.value.trim() ?? "";

            if (!title || !year) {
                showToast("Please fill title and year", "info");
                return;
            }

            try {
                const res = await fetch("check_duplicate.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                    },
                    body: `title=${encodeURIComponent(
                        title
                    )}&year=${encodeURIComponent(year)}`,
                });
                const data = await res.json();
                if (data.exists) {
                    showToast(
                        "Duplicate thesis detected (same title & year).",
                        "error"
                    );
                } else {
                    // submit form normally (server will redirect)
                    addForm.submit();
                }
            } catch (err) {
                console.error(err);
                showToast("Error checking duplicate", "error");
            }
        });
    }

    // ----------------------------
    // 8) CONFIRM DELETE (single) - alternative flow for edit form if you keep editForm submit to delete
    // ----------------------------
    // NOTE: We already wired deleteBtn earlier which calls delete_thesis.php via POST 'id'
    // For safety: intercept direct editForm delete if present elsewhere
    // (Handled above.)

    // ----------------------------
    // 9) Small safety: re-run filter after DOM changes (useful after deletion)
    // ----------------------------
    const observer = new MutationObserver(() => filterTable());
    const tableBody = document.querySelector("tbody");
    if (tableBody)
        observer.observe(tableBody, { childList: true, subtree: true });

    // initial filter run
    filterTable();
});

// document.addEventListener("DOMContentLoaded", () => {
//     const logoutBtn = document.getElementById("logoutBtn");

//     if (logoutBtn) {
//         logoutBtn.addEventListener("click", () => {
//             const confirmLogout = confirm("Are you sure you want to logout?");
//             if (confirmLogout) {
//                 window.location.href = "logout.php"; // adjust path as needed
//             }
//         });
//     }
// });

// Manange thesis
// const rowsPerPage = 30;
// let currentPage = 1;
// let allRows = [];

// document.addEventListener("DOMContentLoaded", () => {
//     const filterToggleBtn = document.getElementById("filterToggleBtn");
//     const filterMenu = document.getElementById("filterMenu");

//     // Toggle dropdown visibility
//     filterToggleBtn.addEventListener("click", () => {
//         filterMenu.classList.toggle("hidden");
//     });

//     // Hide dropdown when clicking outside
//     document.addEventListener("click", (e) => {
//         if (
//             !filterMenu.contains(e.target) &&
//             !filterToggleBtn.contains(e.target)
//         ) {
//             filterMenu.classList.add("hidden");
//         }
//     });

//     // Initialize table data
//     const tableBody = document.querySelector("table tbody");
//     allRows = Array.from(tableBody.querySelectorAll("tr"));

//     // Filter + Search Logic
//     function filterAndSearch() {
//         const searchTerm = document
//             .getElementById("searchInput")
//             .value.toLowerCase();
//         const selectedDepts = Array.from(
//             document.querySelectorAll(".filter-dept:checked")
//         ).map((c) => c.value);
//         const selectedAvail = Array.from(
//             document.querySelectorAll(".filter-availability:checked")
//         ).map((c) => c.value);

//         const filtered = allRows.filter((row) => {
//             const cols = row.querySelectorAll("td");
//             const title = cols[0]?.textContent.toLowerCase() || "";
//             const author = cols[1]?.textContent.toLowerCase() || "";
//             const dept = cols[3]?.textContent.trim() || "";
//             const avail = cols[4]?.textContent.trim() || "";

//             const matchesSearch =
//                 title.includes(searchTerm) || author.includes(searchTerm);
//             const matchesDept =
//                 selectedDepts.length === 0 || selectedDepts.includes(dept);
//             const matchesAvail =
//                 selectedAvail.length === 0 || selectedAvail.includes(avail);
//             return matchesSearch && matchesDept && matchesAvail;
//         });

//         renderTable(filtered);
//     }

//     // Pagination renderer
//     function renderTable(filteredRows) {
//         const totalPages = Math.ceil(filteredRows.length / rowsPerPage) || 1;
//         const start = (currentPage - 1) * rowsPerPage;
//         const end = start + rowsPerPage;
//         const pageRows = filteredRows.slice(start, end);

//         tableBody.innerHTML = "";
//         pageRows.forEach((row) => tableBody.appendChild(row));

//         document.getElementById("currentPage").textContent = currentPage;
//         document.getElementById("totalPages").textContent = totalPages;
//         document.getElementById("prevPage").disabled = currentPage === 1;
//         document.getElementById("nextPage").disabled =
//             currentPage === totalPages;
//     }

//     // Event listeners
//     document.getElementById("searchInput").addEventListener("input", () => {
//         currentPage = 1;
//         filterAndSearch();
//     });

//     document
//         .querySelectorAll(".filter-dept, .filter-availability")
//         .forEach((cb) => {
//             cb.addEventListener("change", () => {
//                 currentPage = 1;
//                 filterAndSearch();
//             });
//         });

//     document.getElementById("prevPage").addEventListener("click", () => {
//         if (currentPage > 1) {
//             currentPage--;
//             filterAndSearch();
//         }
//     });

//     document.getElementById("nextPage").addEventListener("click", () => {
//         const filtered = allRows.filter((row) => row.style.display !== "none");
//         const totalPages = Math.ceil(filtered.length / rowsPerPage) || 1;
//         if (currentPage < totalPages) {
//             currentPage++;
//             filterAndSearch();
//         }
//     });

//     // Initial render
//     filterAndSearch();
// });

// sidebar nigga

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

// ✅ Update Librarian Status
async function updateStatus(id, newStatus) {
    if (!(await confirmPopup(`Are you sure you want to set this account to '${newStatus}'?`, {title: 'Confirm Status Change'}))) return;

    fetch("update-librarian-status.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `id=${id}&status=${newStatus}`,
    })
        .then((res) => res.text())
        .then((msg) => {
            Swal.fire({ icon: 'success', title: msg }).then(() => location.reload());
        })
        .catch(() => Swal.fire({icon:'error', title: 'Error updating status.'}));
}

// ✅ Filter + Sort Functionality
document.addEventListener("DOMContentLoaded", () => {
    const searchInput = document.getElementById("searchInput");
    const statusFilter = document.getElementById("statusFilter");
    const sortBy = document.getElementById("sortBy");
    const tableBody = document.querySelector("tbody");

    function filterAndSort() {
        const rows = Array.from(tableBody.querySelectorAll("tr"));
        const search = searchInput.value.toLowerCase();
        const filter = statusFilter.value.toLowerCase();
        const sort = sortBy.value;

        let filtered = rows.filter((row) => {
            const name = row.children[1].textContent.toLowerCase();
            const email = row.children[2].textContent.toLowerCase();
            const status = row.children[4].textContent.toLowerCase();
            return (
                (name.includes(search) || email.includes(search)) &&
                (filter === "" || status.includes(filter))
            );
        });

        // Sorting Logic
        if (sort === "name") {
            filtered.sort((a, b) =>
                a.children[1].textContent.localeCompare(
                    b.children[1].textContent
                )
            );
        } else if (sort === "status") {
            filtered.sort((a, b) =>
                a.children[4].textContent.localeCompare(
                    b.children[4].textContent
                )
            );
        } else {
            filtered.sort(
                (a, b) => b.children[0].textContent - a.children[0].textContent
            );
        }

        tableBody.innerHTML = "";
        filtered.forEach((row) => tableBody.appendChild(row));
    }

    searchInput.addEventListener("input", filterAndSort);
    statusFilter.addEventListener("change", filterAndSort);
    sortBy.addEventListener("change", filterAndSort);
});
