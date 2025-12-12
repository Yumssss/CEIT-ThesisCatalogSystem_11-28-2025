<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet" />
    <title>CEIT Thesis Hub | Home Page</title>
    <link rel="icon" type="image/png" href="user-pictures/logo.png" />
    <link rel="stylesheet" href="User Side/user-style.css" />
</head>

<body>
    <!-- HOME PAGE -->
    <section class="hero-section">
        <img src="User Side/user-pictures/hero-pic.jpg" class="hero-pic" />
        <nav>
            <div class="logo-section">
                <img
                    src="User Side/user-pictures/logo.png"
                    class="logo-circle" />
                <div class="title">CEIT Thesis Hub</div>
            </div>
            <div class="nav-links">
                <a href="home.php" class="active">HOME</a>
                <a href="User Side/catalog.php">CATALOG</a>
                <a href="Admin/index.php" target="_blank">ADMIN</a>
            </div>
        </nav>

        <div class="hero">
            <div class="college-logos">
                <img
                    src="User Side/user-pictures/ceit_logo.png"
                    class="college-logo" />
                <img
                    src="User Side/user-pictures/plv_logo.png"
                    class="college-logo" />
            </div>
            <h1>PAMANTASAN NG LUNGSOD NG VALENZUELA</h1>
            <h2>College of Engineering and Information Technology</h2>
            <p>"Empowering Minds Through Research and Innovation"</p>
        </div>
    </section>

    <div class="content">
        <div class="welcome">
            <h2>Welcome to PLV CEIT Thesis Hub</h2>
            <p>
                Browse, request, and track academic theses in one platform
            </p>
        </div>

        <div class="announcements">
            <h3>Announcements</h3>
            <div class="announcement-box">
                <ul>
                    <?php
                    include "PHP/db_connect.php";
                    $query = $conn->query("SELECT message FROM tbl_announcements ORDER
                        BY created_at DESC LIMIT 5");
                    if ($query->num_rows > 0):
                        while ($row = $query->fetch_assoc()): echo "
                        <li>" . htmlspecialchars($row['message']) . "</li>
                        ";
                        endwhile;
                    else: echo "
                        <li>No announcements available.</li>
                        ";
                    endif; ?>
                </ul>
            </div>
        </div>

        <!-- <div class="quick-actions">
                <h3>Quick Actions</h3>
                <div class="action-buttons">
                    <button class="action-btn btn-primary">+ADD REQUEST</button>
                    <button class="action-btn btn-secondary">
                        VIEW REQUESTS
                    </button>
                    <button class="action-btn btn-tertiary">
                        HELP/GUIDELINES
                    </button>
                </div>
            </div> -->
    </div>

    <footer>
        <img src="User Side/user-pictures/logo.png" class="footer-logo" />
        <h3>PLV CEIT THESIS CATALOG</h3>
        <div class="footer-info">
            <p>
                <img
                    src="User Side/user-pictures/location.png"
                    class="footer-info-logo" />
                3rd Floor, CEIT Building, Main PLV Campus, Tongco St.,
                Maysan, Valenzuela City
            </p>
            <!-- <p>
                <img
                    src="User Side/user-pictures/email.png"
                    class="footer-info-logo" />
                loremipsum@plv.edu.ph
            </p> -->
            <p>
                <img
                    src="User Side/user-pictures/world-wide-web.png"
                    class="footer-info-logo" />
                <a href="https://plv.edu.ph/" target="_blank">
                    plv.edu.ph</a>
            </p>
        </div>
        <div class="copyright">Copyright © 2025</div>
    </footer>
</body>

</html>