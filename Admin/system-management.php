<?php
include "../PHP/db_connect.php";
session_start();
date_default_timezone_set('Asia/Manila');

// Security check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Fetch existing open hours if table exists
$currentHours = "Not set";
$tableExists = $conn->query("SHOW TABLES LIKE 'tbl_system_settings'");
if ($tableExists && $tableExists->num_rows > 0) {
    $res = $conn->query("SELECT setting_value FROM tbl_system_settings WHERE setting_key = 'library_hours'");
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $currentHours = $row['setting_value'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Management</title>
    <link rel="icon" type="image/png" href="pictures/Logo.png">
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        .back-btn {
            display: inline-block;
            background: #0A3D91;
            color: #fff;
            padding: 10px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            margin-bottom: 25px;
            transition: 0.25s ease;
            box-shadow: 0 3px 8px rgba(10, 61, 145, 0.2);
        }

        .back-btn:hover {
            background: #062e72;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(10, 61, 145, 0.3);
        }

        /* 🌐 Base Layout */
        body {
            font-family: "Poppins", sans-serif;
            background: #f4f7fb;
            color: #333;
            margin: 0;
            padding: 0;
        }

        /* 🧭 Main Section */
        .settings-page {
            padding: 40px 60px;
            min-height: 100vh;
            background: #f4f7fb;
        }

        .welcome-section h2 {
            font-size: 26px;
            color: #0A3D91;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .date {
            color: #666;
            font-size: 14px;
        }

        .divider {
            border: none;
            height: 2px;
            background: #e0e0e0;
            margin: 15px 0 30px;
        }

        /* 🧱 Settings Category Cards */
        .settings-category {
            background: #ffffff;
            padding: 30px 35px;
            border-radius: 14px;
            margin-bottom: 25px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            transition: all 0.25s ease;
        }

        .settings-category:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
        }

        .settings-category h3 {
            color: #0A3D91;
            font-size: 20px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* ⏰ Time Input Form */
        .settings-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .time-inputs {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .time-inputs input[type="time"] {
            padding: 10px 14px;
            font-size: 15px;
            border-radius: 8px;
            border: 1px solid #cfd8e3;
            background: #f9fbff;
            color: #333;
            transition: 0.3s ease;
        }

        .time-inputs input[type="time"]:focus {
            border-color: #0A3D91;
            box-shadow: 0 0 0 3px rgba(10, 61, 145, 0.15);
            background: #fff;
        }

        .time-separator {
            font-weight: 600;
            color: #0A3D91;
            font-size: 15px;
        }

        /* 🧭 Buttons */
        .settings-form button,
        .settings-category form button {
            background: #0A3D91;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: 0.25s ease;
            font-size: 15px;
            align-self: flex-start;
            box-shadow: 0 3px 10px rgba(10, 61, 145, 0.2);
        }

        .settings-form button:hover,
        .settings-category form button:hover {
            background: #083377;
            transform: translateY(-2px);
        }

        /* ⚠️ Reset Button */
        .settings-category form[action="reset-borrowing.php"] button {
            background: #e74c3c;
            box-shadow: 0 3px 10px rgba(231, 76, 60, 0.25);
        }

        .settings-category form[action="reset-borrowing.php"] button:hover {
            background: #c0392b;
        }

        /* 💾 Backup Button */
        .settings-category form[action="backup-database.php"] button {
            background: #1e88e5;
            box-shadow: 0 3px 10px rgba(30, 136, 229, 0.25);
        }

        .settings-category form[action="backup-database.php"] button:hover {
            background: #1565c0;
        }

        /* 🔔 Alert Box */
        .alert-box {
            background: #e3f2fd;
            border-left: 5px solid #0A3D91;
            padding: 12px 18px;
            margin-bottom: 25px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 500;
            color: #0A3D91;
        }

        /* 📱 Responsive */
        @media (max-width: 768px) {
            .settings-page {
                padding: 25px;
            }

            .settings-category {
                padding: 20px;
            }

            .time-inputs {
                flex-direction: column;
                gap: 10px;
            }

            .settings-category h3 {
                font-size: 18px;
            }

            .settings-form button,
            .settings-category form button {
                width: 100%;
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
            <h2>System Management</h2>
            <div class="header-logo">
                <img src="pictures/Logo.png" alt="CEIT Logo" width="90" height="60">
            </div>
        </div>
    </header>

    <div class="container">
        <?php include 'sidebar.php'; ?>

        <main class="settings-page">
            <a href="settings.php" class="back-btn">← Back</a>

            <section class="welcome-section">

                <h2>System Management</h2>
                <p class="date"><?php echo strtoupper(date('M d, Y | l, h:i A')); ?></p>
                <hr class="divider" />
            </section>

            <!-- Alert -->
            <?php if (isset($_SESSION['msg'])): ?>
                <div class="alert-box"><?= htmlspecialchars($_SESSION['msg']); ?></div>
                <?php unset($_SESSION['msg']); ?>
            <?php endif; ?>

            <!-- Change Library Hours -->
            <!-- Change Library Hours -->
            <section class="settings-category">
                <h3>🕓 Change Library Open Hours</h3>

                <form action="update-hours.php" method="POST" class="settings-form">
                    <div class="time-inputs">

                        <?php
                        // Fetch open & close times from database if they exist
                        $open_time = "";
                        $close_time = "";

                        $tableExists = $conn->query("SHOW TABLES LIKE 'tbl_system_settings'");
                        if ($tableExists && $tableExists->num_rows > 0) {
                            $res = $conn->query("SELECT setting_value FROM tbl_system_settings WHERE setting_key = 'library_open_time'");
                            if ($res && $res->num_rows > 0) {
                                $open_time = $res->fetch_assoc()['setting_value'];
                            }
                            $res = $conn->query("SELECT setting_value FROM tbl_system_settings WHERE setting_key = 'library_close_time'");
                            if ($res && $res->num_rows > 0) {
                                $close_time = $res->fetch_assoc()['setting_value'];
                            }
                        }
                        ?>

                        <input type="time" name="open_time" value="<?= htmlspecialchars($open_time) ?>" required>
                        <span class="time-separator">to</span>
                        <input type="time" name="close_time" value="<?= htmlspecialchars($close_time) ?>" required>
                    </div>

                    <button type="submit">💾 Save Changes</button>
                </form>
            </section>



            <!-- <section class="settings-category">
                <h3>💾 Backup Database</h3>
                <form action="backup-database.php" method="POST">
                    <button type="submit">⬇️ Download Backup</button>
                </form>
            </section>

            
            <section class="settings-category">
                <h3>🧹 Reset All Borrowing Requests</h3>
                <form action="reset-borrowing.php" method="POST" onsubmit="return confirm('Are you sure? This cannot be undone.')">
                    <button type="submit">⚠️ Reset Requests</button>
                </form>
            </section> -->
        </main>
    </div>
</body>

</html>