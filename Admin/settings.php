<?php
include "../PHP/db_connect.php";
session_start();
date_default_timezone_set('Asia/Manila');

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
    <title>Settings</title>
    <link rel="icon" type="image/png" href="pictures/Logo.png">
    <link rel="stylesheet" href="style.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: "Poppins", sans-serif;
            background: #f4f7fb;
            margin: 0;
            color: #222;
        }

        main.settings-page {
            padding: 25px 35px;
        }

        .welcome-section h2 {
            font-size: 22px;
            color: #0a3d91;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .welcome-section .date {
            font-size: 14px;
            color: #666;
        }

        .divider {
            border: none;
            height: 2px;
            background: #e0e0e0;
            margin: 15px 0 25px;
        }

        /* 🧩 SETTINGS CATEGORIES */
        .settings-category {
            background: #ffffff;
            border-radius: 12px;
            padding: 25px 30px;
            margin-bottom: 20px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .settings-category:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
        }

        .settings-category h3 {
            font-size: 18px;
            color: #0a3d91;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .settings-category ul {
            list-style: none;
            padding-left: 15px;
            margin: 0;
        }

        .settings-category ul li {
            margin: 8px 0;
        }

        .settings-category ul li a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: 0.2s;
            display: inline-block;
        }

        .settings-category ul li a:hover {
            color: #0a3d91;
            text-decoration: underline;
        }

        /* 📱 RESPONSIVE */
        @media (max-width: 768px) {
            main.settings-page {
                padding: 15px 20px;
            }

            .settings-category {
                padding: 20px;
            }
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
            <h2>Settings</h2>
            <div class="header-logo">
                <img src="pictures/Logo.png" alt="CEIT Logo" width="90" height="60">
            </div>
        </div>
    </header>

    <div class="container">
        <?php include 'sidebar.php'; ?>

        <main>
            <section class="welcome-section-settings">
                <h2 class="labelOnLeft">Settings</h2>
                <p class="date"><?php echo strtoupper(date('M d, Y | l, h:i A')); ?></p>
                <hr class="divider" />
            </section>

            <?php if ($role === 'admin'): ?>
                <!-- 🔹 SUPER ADMIN SETTINGS -->
                <section class="settings-category">
                    <h3>👤 Profile Settings</h3>
                    <ul>
                        <li><a href="update-profile.php">Edit Profile</a></li>
                    </ul>
                </section>

                <section class="settings-category">
                    <h3>🛠️ System Management</h3>
                    <ul>
                        <li><a href="system-management.php">Change Library Open Hours</a></li>
                        <!-- <li><a href="#">Backup Database (Manual or One-Click)</a></li>
                        <li><a href="#">Reset All Borrowing Requests</a></li> -->
                    </ul>
                </section>

                <!-- 🔒 SECURITY SETTINGS (temporarily hidden)
                <section class="settings-category">
                    <h3>🔒 Security</h3>
                    <ul>
                        <li><a href="#">Set Password Policy</a></li>
                        <li><a href="#">View Login Activity Logs</a></li>
                    </ul>
                </section>
                -->

            <?php else: ?>
                <!-- 🔹 LIBRARIAN SETTINGS -->
                <section class="settings-category">
                    <h3>👤 Profile Settings</h3>
                    <ul>
                        <li><a href="update-profile.php">Edit Profile</a></li>
                    </ul>
                </section>

                <!-- <section class="settings-category">
                    <h3>🔔 Notification Settings</h3>
                    <ul>
                        <li><a href="#">Toggle Sound or Pop-up Notifications</a></li>
                    </ul>
                </section>

                <section class="settings-category">
                    <h3>📘 Borrowing Rules Overview</h3>
                    <ul>
                        <li><a href="#">View Library Hours</a></li>
                        <li><a href="#">View Current Borrowing Policy</a></li>
                    </ul>
                </section> -->
            <?php endif; ?>

            <!-- 📰 ANNOUNCEMENTS MANAGEMENT -->
            <section class="settings-category">
                <h3>📰 Manage Announcements</h3>
                <ul>
                    <li><a href="manage-announcements.php">Add Announcement</a></li>
                </ul>
            </section>


        </main>
    </div>

    <script src="script.js"></script>
</body>

</html>