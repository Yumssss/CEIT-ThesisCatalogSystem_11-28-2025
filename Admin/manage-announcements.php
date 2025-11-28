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
$message = "";

// 🧠 Handle Add
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['new_announcement']) && !empty(trim($_POST['new_announcement']))) {
        $message = trim($_POST['new_announcement']);
        $count = $conn->query("SELECT COUNT(*) as total FROM tbl_announcements")->fetch_assoc()['total'];

        if ($count < 5) {
            $stmt = $conn->prepare("INSERT INTO tbl_announcements (message) VALUES (?)");
            $stmt->bind_param("s", $message);
            $stmt->execute();
            $_SESSION['flash_message'] = "✅ Announcement added successfully!";
        } else {
            $_SESSION['flash_message'] = "❌ Maximum of 5 announcements reached. Please delete one first.";
        }
    }

    if (isset($_POST['delete_id'])) {
        $id = (int)$_POST['delete_id'];
        $conn->query("DELETE FROM tbl_announcements WHERE id = $id");
        $_SESSION['flash_message'] = "🗑 Announcement deleted.";
    }

    // ✅ Redirect to prevent form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}


// 📰 Fetch all
$announcements = $conn->query("SELECT * FROM tbl_announcements ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Manage Announcements | CEIT Thesis Hub</title>
    <link rel="icon" type="image/png" href="pictures/Logo.png" />
    <link rel="stylesheet" href="style.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: "Poppins", sans-serif;
            background: #f4f7fb;
            color: #333;
            margin: 0;
        }

        main {
            padding: 40px 60px;
        }

        /* 🔙 Back Button */
        .back-btn {
            display: inline-block;
            background: #0A3D91;
            color: #fff;
            padding: 10px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            margin-bottom: 20px;
            transition: 0.25s ease;
        }

        .back-btn:hover {
            background: #062e72;
            transform: translateY(-2px);
        }

        /* 📰 Announcement Container */
        .announcement-container {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
            padding: 30px 40px;
        }

        .announcement-container h2 {
            color: #0A3D91;
            font-size: 1.6rem;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .add-btn {
            background: #0A3D91;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 10px 16px;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            transition: 0.25s;
            margin-bottom: 18px;
        }

        .add-btn:hover {
            background: #083377;
            transform: translateY(-1px);
        }

        /* Form */
        .add-form {
            display: none;
            margin-bottom: 20px;
            animation: fadeIn 0.3s ease;
        }

        .add-form.visible {
            display: block;
        }

        .add-form {
            display: none;
            margin-bottom: 20px;
            animation: fadeIn 0.3s ease;
            width: 100%;
            /* ensures full form width */
        }

        .add-form.visible {
            display: block;
        }

        .add-form textarea {
            width: 100%;
            /* ✨ full width inside container */
            height: 120px;
            border: 1px solid #ccd6eb;
            border-radius: 8px;
            padding: 12px 14px;
            resize: none;
            font-size: 0.95rem;
            background: #f9fbff;
            outline: none;
            transition: 0.25s;
            box-sizing: border-box;
            /* prevent overflow */
        }

        .add-form textarea:focus {
            border-color: #0A3D91;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(10, 61, 145, 0.12);
        }


        .add-form textarea:focus {
            border-color: #0A3D91;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(10, 61, 145, 0.1);
        }

        .submit-btn {
            background: #0A3D91;
            color: #fff;
            border: none;
            padding: 10px 18px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: 0.25s;
            margin-top: 10px;
        }

        .submit-btn:hover {
            background: #083377;
        }

        /* Announcement List */
        .announcement-list {
            margin-top: 20px;
        }

        /* Each announcement item */
        .announcement-item {
            background: #f9fbff;
            border: 1px solid #e0e7ff;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            /* Add a small space between text and button */
        }

        /* Prevent form from stretching full width */
        .announcement-item form {
            flex-shrink: 0;
            margin: 0;
            display: inline-block;
        }

        /* Fix delete button width */
        .delete-btn {
            background: #ef4444;
            color: #fff;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            font-size: 0.85rem;
            width: auto;
            /* ✨ prevent full width */
            white-space: nowrap;
            /* ✨ prevent text wrapping */
            transition: background 0.25s, transform 0.2s;
        }

        .delete-btn:hover {
            background: #dc2626;
            transform: translateY(-1px);
        }


        .message {
            background: #eaf1ff;
            color: #0A3D91;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-weight: 500;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(5px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Table Styling for Announcements */
        .announcement-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
        }

        .announcement-table th,
        .announcement-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #f0f2f8;
            font-size: 0.95rem;
            text-align: left;
        }

        .announcement-table th {
            background: #0A3D91;
            color: #fff;
            font-weight: 600;
        }

        .announcement-table tr:hover td {
            background: #f9fbff;
        }

        .delete-btn {
            background: #ef4444;
            color: #fff;
            border: none;
            padding: 6px 14px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 500;
            transition: background 0.25s ease, transform 0.2s ease;
        }

        .delete-btn:hover {
            background: #dc2626;
            transform: translateY(-1px);
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
            <h2>Manage Announcements</h2>
            <div class="header-logo">
                <img src="pictures/Logo.png" alt="CEIT Logo" width="90" height="60">
            </div>
        </div>
    </header>

    <div class="container">
        <?php include 'sidebar.php'; ?>
        <main>
            <a href="settings.php" class="back-btn">← Back</a>

            <div class="announcement-container">
                <h2>Announcements</h2>
                <?php if (isset($_SESSION['flash_message'])): ?>
                    <p style="background:#e9f9ef; color:#155724; padding:10px 15px; border-radius:8px; border-left:4px solid #28a745; font-weight:500;">
                        <?= $_SESSION['flash_message']; ?>
                    </p>
                    <?php unset($_SESSION['flash_message']); ?>
                <?php endif; ?>

                <br>
                <?php if (!empty($message)): ?>
                    <p class="message"><?= $message ?></p>
                <?php endif; ?>

                <button class="add-btn" id="toggleAddForm">➕ Add Announcement</button>

                <!-- Add Form -->
                <form method="POST" class="add-form" id="addForm">
                    <textarea name="new_announcement" maxlength="255" placeholder="Enter new announcement..." required></textarea>
                    <br>
                    <button type="submit" class="submit-btn">Save</button>
                </form>

                <div class="announcement-list">
                    <?php if ($announcements->num_rows > 0): ?>
                        <table class="announcement-table">
                            <thead>
                                <tr>
                                    <th>Announcement</th>
                                    <th style="width: 120px; text-align:center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $announcements->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['message']); ?></td>
                                        <td style="text-align:center;">
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="delete_id" value="<?= $row['id']; ?>">
                                                <button type="submit" class="delete-btn">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p style="color:#888;">No announcements available.</p>
                    <?php endif; ?>
                </div>

            </div>
        </main>
    </div>

    <script>
        const toggleBtn = document.getElementById("toggleAddForm");
        const addForm = document.getElementById("addForm");

        toggleBtn.addEventListener("click", () => {
            addForm.classList.toggle("visible");
            toggleBtn.textContent = addForm.classList.contains("visible") ?
                "✖ Cancel" :
                "➕ Add Announcement";
        });
    </script>
</body>

</html>