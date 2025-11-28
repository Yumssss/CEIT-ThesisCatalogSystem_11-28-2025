<?php
include "../PHP/db_connect.php";

// Validate Thesis ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('No thesis selected!'); window.location='catalog.php';</script>";
    exit();
}

$id = intval($_GET['id']);
$query = "SELECT * FROM tbl_thesis WHERE thesis_id = $id";
$result = $conn->query($query);

if ($result->num_rows == 0) {
    echo "<script>alert('Thesis not found!'); window.location='catalog.php';</script>";
    exit();
}

$thesis = $result->fetch_assoc();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CEIT Thesis Hub | Request Form</title>

    <link rel="icon" type="image/png" href="user-pictures/logo.png">
    <link rel="stylesheet" href="user-style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">


</head>

<body>
    <nav>
        <div class="logo-section">
            <img src="user-pictures/logo.png" class="logo-circle">
            <div class="title">CEIT Thesis Hub</div>
        </div>
        <div class="nav-links">
            <a href="../home.php">HOME</a>
            <a href="catalog.php" class="active">CATALOG</a>
            <a href="../Admin/index.php" target="_blank">ADMIN</a>
        </div>
    </nav>

    <main class="request-container">
        <h2 class="request-title">REQUEST FORM</h2>

        <form id="requestForm" action="save-request.php" method="POST" class="request-form-box">

            <!-- Hidden Thesis ID -->
            <input type="hidden" name="thesis_id" value="<?php echo $thesis['thesis_id']; ?>">

            <!-- Thesis Info -->
            <div class="thesis-info-box">
                <h3>Thesis Information</h3>

                <p><strong>Title</strong>
                    <span><?php echo htmlspecialchars($thesis['title']); ?></span>
                </p><br>

                <p><strong>Author(s)</strong>
                    <span><?php echo htmlspecialchars($thesis['author']); ?></span>
                </p><br>

                <p><strong>Department</strong>
                    <span><?php echo htmlspecialchars($thesis['department']); ?></span>
                </p><br>

                <p><strong>Year</strong>
                    <span><?php echo htmlspecialchars($thesis['year']); ?></span>
                </p>
            </div>

            <hr>

            <!-- STUDENT NAME -->
            <!-- Validation: Instant removal of numbers using oninput -->
            <label for="student_name">Student Name</label>
            <input
                type="text"
                id="student_name"
                name="student_name"
                placeholder="ex. Juan A. Dela Cruz"
                pattern="[A-Za-zÑñ\s.]+"
                title="Letters, spaces, and dots only (no numbers)"
                oninput="this.value = this.value.replace(/[^A-Za-zÑñ\s.]/g, '')"
                required>

            <!-- STUDENT NUMBER -->
            <!-- Validation: Instant removal of letters using oninput -->
            <label for="student_no">Student Number</label>
            <input
                type="text"
                id="student_no"
                name="student_no"
                placeholder="ex. 2023-10050"
                pattern="[0-9\-]+"
                title="Numbers and hyphens only"
                oninput="this.value = this.value.replace(/[^0-9\-]/g, '')"
                required>

            <!-- COURSE & SECTION -->
            <label for="course_section">Course & Section</label>
            <input
                type="text"
                id="course_section"
                name="course_section"
                placeholder="ex. BSIT 4-A"
                pattern="[A-Za-z0-9\s\-]+"
                title="Letters, numbers, spaces, and hyphens only"
                required>

            <!-- TERMS AGREEMENT -->
            <div class="agreement">
                <input type="checkbox" id="agreeCheckbox" onchange="toggleSubmit()">
                <label for="agreeCheckbox">I agree to the borrowing terms and conditions of the CEIT Thesis Hub.</label>
            </div>

            <div class="form-buttons">
                <button type="submit" id="submitBtn" class="btn-submit disabled-btn" disabled>SUBMIT REQUEST</button>
                <a href="catalog.php" class="btn-cancel">CANCEL</a>
            </div>

        </form>
    </main>

    <footer>
        <img src="user-pictures/logo.png" class="footer-logo">
        <h3>PLV CEIT THESIS CATALOG</h3>

        <div class="footer-info">
            <p><img src="user-pictures/location.png" class="footer-info-logo">3rd Floor, CEIT Building, Main PLV Campus, Tongco St., Maysan, Valenzuela City</p>
            <p><img src="user-pictures/email.png" class="footer-info-logo"> loremipsum@plv.edu.ph</p>
            <p><img src="user-pictures/world-wide-web.png" class="footer-info-logo"> plv.edu.ph</p>
        </div>

        <div class="copyright">Copyright © 2025</div>
    </footer>

    <script>
        function toggleSubmit() {
            const checkbox = document.getElementById('agreeCheckbox');
            const btn = document.getElementById('submitBtn');

            btn.disabled = !checkbox.checked;

            if (checkbox.checked) {
                btn.classList.remove('disabled-btn');
            } else {
                btn.classList.add('disabled-btn');
            }
        }
    </script>

</body>

</html>