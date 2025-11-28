<?php
// Always include this at the top:
// session_start();
// $role = $_SESSION['role'];
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!isset($role)) {
    $role = isset($_SESSION['role']) ? $_SESSION['role'] : null;
}
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <nav>
        <a href="dashboard.php" class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
            <img src="pictures/dashboard.png" width="30" height="30"> Dashboard
        </a>
        <a href="borrowing-request.php" class="<?= $currentPage === 'borrowing-request.php' ? 'active' : '' ?>">
            <img src="pictures/request.png" width="30" height="30"> Requests
        </a>
        <?php if ($role === 'admin') { ?>
            <a href="manage-thesis.php" class="<?= $currentPage === 'manage-thesis.php' ? 'active' : '' ?>">
                <img src="pictures/thesis.png" width="30" height="30"> Manage Thesis
            </a>
            <a href="manage-librarians.php" class="<?= $currentPage === 'manage-librarians.php' ? 'active' : '' ?>">
                <img src="pictures/user.png" width="30" height="30"> Manage Librarians
            </a>
        <?php } ?>
        <a href="reports.php" class="<?= $currentPage === 'reports.php' ? 'active' : '' ?>">
            <img src="pictures/report.png" width="30" height="30"> Reports
        </a>
        <a href="settings.php" class="<?= $currentPage === 'settings.php' ? 'active' : '' ?>">
            <img src="pictures/setting.png" width="30" height="30"> Settings
        </a>
    </nav>
    <!-- Logout -->
    <form id="logoutForm" action="logout.php" method="post">
        <button type="button" id="logoutBtn" class="logout">Logout</button>
    </form>
</aside>
<!-- ✅ Scripts MUST be placed OUTSIDE the sidebar -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.getElementById("logoutBtn").addEventListener("click", function() {
        Swal.fire({
            title: "Are you sure?",
            text: "You will be logged out.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#0a3d91",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes",
            cancelButtonText: "Cancel"
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById("logoutForm").submit();
            }
        });
    });
</script>