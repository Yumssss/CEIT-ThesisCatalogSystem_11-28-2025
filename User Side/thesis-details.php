<?php
include "../PHP/db_connect.php";

// Check if ID is provided
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
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">
    <title>CEIT Thesis Hub | Thesis Details</title>
    <link rel="icon" type="image/png" href="user-pictures/logo.png">
    <link rel="stylesheet" href="user-style.css">
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

    <main class="thesis-details-container">
        <a href="catalog.php" class="back-btn-icon">
            <i class="fas fa-arrow-left"></i>
            <span>Back</span>
        </a>

        <h1 id="thesis-title" class="details-title">
            <?php echo htmlspecialchars($thesis['title']); ?>
        </h1>

        <!-- <nav>
            <div class="logo-section">
                <img
                    src="user-pictures/logo.png"
                    class="logo-circle" />
                <div class="title">CEIT Thesis Hub</div>
            </div>
            <div class="nav-links">
                <a href="home.php" class="active">HOME</a>
                <a href="catalog.php">CATALOG</a>
                <a href="Admin/index.php">ON DUTY?</a>
            </div>
        </nav> -->

        <section class="thesis-meta">
            <div class="meta-box">
                <strong>Author(s)</strong>
                <p id="thesis-authors"><?php echo htmlspecialchars($thesis['author']); ?></p>
            </div>
            <div class="meta-box">
                <strong>Year</strong>
                <p id="thesis-year"><?php echo htmlspecialchars($thesis['year']); ?></p>
            </div>
            <div class="meta-box">
                <strong>Department</strong>
                <p id="thesis-dept"><?php echo htmlspecialchars($thesis['department']); ?></p>
            </div>
            <div class="meta-box availability-box 
            <?php echo ($thesis['availability'] === 'Available') ? 'available' : 'unavailable'; ?>">
                <p id="thesis-availability">
                    <?php if ($thesis['availability'] === "Available"): ?>

                        <i class="fas fa-check-circle"></i> Available

                    <?php else: ?>

                        <i class="fas fa-times-circle"></i> Unavailable

                    <?php endif; ?>
                </p>
            </div>

        </section>

        <!-- <section class="thesis-abstract">
            <h3>Abstract</h3>
            <p id="thesis-abstract-text">
                <?php echo nl2br(htmlspecialchars($thesis['abstract'])); ?>
            </p>
        </section> -->

        <div class="button-group">
            <?php if ($thesis['availability'] === "Available"): ?>
                <a href="request-form.php?id=<?php echo $thesis['thesis_id']; ?>" class="borrow-btn">Request Thesis</a>
            <?php else: ?>
                <a class="borrow-btn disabled">Unavailable</a>
            <?php endif; ?>
            <!-- <a href="catalog.php" class="back-btn">Back to Catalog</a> -->
        </div>

    </main>
    <footer>
        <img src="user-pictures/logo.png" class="footer-logo" />
        <h3>PLV CEIT THESIS CATALOG</h3>
        <div class="footer-info">
            <p>
                <img
                    src="user-pictures/location.png"
                    class="footer-info-logo" />
                3rd Floor, CEIT Building, Main PLV Campus, Tongco St.,
                Maysan, Valenzuela City
            </p>
            <p>
                <img
                    src="user-pictures/email.png"
                    class="footer-info-logo" />
                loremipsum@plv.edu.ph
            </p>
            <p>
                <img
                    src="user-pictures/world-wide-web.png"
                    class="footer-info-logo" />
                plv.edu.ph
            </p>
        </div>
        <div class="copyright">Copyright © 2025</div>
    </footer>
    <script>
        const items = document.querySelectorAll('.meta-box, .thesis-abstract, .button-group');
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animation = 'fadeUp 0.6s ease forwards';
                    observer.unobserve(entry.target);
                }
            });
        });
        items.forEach(i => observer.observe(i));
    </script>
</body>

</html>