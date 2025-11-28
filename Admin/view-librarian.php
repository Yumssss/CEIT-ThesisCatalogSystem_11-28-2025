<?php
include "../PHP/db_connect.php";
session_start();
date_default_timezone_set('Asia/Manila');

// 🔒 Security check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("No librarian selected.");
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM tbl_librarians WHERE librarian_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$librarian = $result->fetch_assoc();

if (!$librarian) {
    die("Librarian not found.");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Librarian | CEIT Thesis Hub</title>
    <link rel="icon" type="image/png" href="pictures/Logo.png">
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: "Poppins", sans-serif;
            background: #f6f8fa;
        }

        .librarian-profile {
            background: #ffffff;
            padding: 40px;
            border-radius: 14px;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.08);
            margin: 40px auto;
            width: 85%;
            max-width: 900px;
            transition: 0.3s ease;
        }

        .librarian-profile:hover {
            transform: translateY(-3px);
        }

        .profile-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        .profile-header h2 {
            color: #0a3d91;
            font-size: 26px;
            font-weight: 600;
            margin: 0;
            letter-spacing: 0.3px;
        }

        .profile-info {
            margin-top: 25px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 18px;
        }

        .profile-field {
            background: linear-gradient(145deg, #f9fbff, #f4f7fc);
            padding: 16px 18px;
            border-radius: 10px;
            border: 1px solid #e1e8f0;
            box-shadow: 0 2px 6px rgba(10, 61, 145, 0.05);
            transition: all 0.25s ease;

            /* 🛠 Prevent long text overflow */
            word-wrap: break-word;
            /* Allows breaking long strings */
            overflow-wrap: break-word;
            /* Modern alternative */
            word-break: break-word;
            /* Ensures words wrap properly */
            white-space: normal;
            /* Allows multiline wrapping */
            min-width: 0;
            /* Fix flex/grid shrink issue */
        }

        /* Optional: make the email field truncate with "..." if preferred */
        .profile-field.email {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }


        .profile-field:hover {
            background: #eef4ff;
            border-color: #bcd1ff;
            transform: translateY(-2px);
        }

        .profile-field strong {
            color: #0a3d91;
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            font-size: 14px;
        }

        /* 🔹 STATUS BADGES */
        .status-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            color: #fff;
            font-weight: 600;
            text-transform: capitalize;
            font-size: 13px;
            letter-spacing: 0.3px;
        }

        .status-approved {
            background-color: #27ae60;
        }

        .status-pending {
            background-color: #f39c12;
        }

        .status-inactive {
            background-color: #9e9e9e;
        }

        .status-rejected {
            background-color: #e74c3c;
        }

        /* ⚙️ ACTIONS */
        .actions {
            margin-top: 35px;
            text-align: center;
        }

        .action-btn {
            padding: 10px 18px;
            margin: 5px;
            border: none;
            border-radius: 8px;
            color: #fff;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.25s ease;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.12);
        }

        .action-btn:hover {
            transform: translateY(-2px);
            opacity: 0.9;
        }

        .approve {
            background-color: #27ae60;
        }

        .reject {
            background-color: #e74c3c;
        }

        .deactivate {
            background-color: #9e9e9e;
        }

        .delete {
            background-color: #c0392b;
        }

        .delete:hover {
            background-color: #a93226;
        }

        /* ⬅️ BACK BUTTON */
        .back-btn {
            display: inline-block;
            background: #0a3d91;
            color: #fff;
            padding: 10px 22px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            letter-spacing: 0.4px;
            transition: all 0.3s ease;
            box-shadow: 0 3px 10px rgba(10, 61, 145, 0.2);
        }

        .back-btn:hover {
            background: #094083;
            transform: translateY(-2px);
        }

        /* 🔸 DIVIDER */
        .divider {
            border: 0;
            height: 2px;
            background: #e0e0e0;
            margin: 25px 0;
        }

        /* 📱 RESPONSIVE */
        @media (max-width: 768px) {
            .librarian-profile {
                padding: 25px;
                width: 92%;
            }

            .profile-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .profile-field {
                font-size: 14px;
            }

            .action-btn {
                padding: 8px 14px;
                font-size: 13px;
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
            <h2>View Librarian</h2>
            <div class="header-logo">
                <img src="pictures/Logo.png" alt="CEIT Logo" width="90" height="60">
            </div>
        </div>
    </header>

    <div class="container">
        <?php include 'sidebar.php'; ?>

        <main class="settings-page">
            <a href="manage-librarians.php" class="back-btn">← Back</a>
            <br><br>
            <section class="welcome-section">
                <h2>Librarian Profile</h2>
                <p class="date"><?php echo strtoupper(date('M d, Y | l, h:i A')); ?></p>
                <hr class="divider" />
            </section>

            <div class="librarian-profile">
                <div class="profile-header">
                    <h2><?= htmlspecialchars($librarian['fullname']); ?></h2>
                </div>

                <div class="profile-info">
                    <div class="profile-field email"><strong>Email:</strong> <?= htmlspecialchars($librarian['email']); ?></div>
                    <div class="profile-field"><strong>Section:</strong> <?= htmlspecialchars($librarian['section']); ?></div>
                    <div class="profile-field"><strong>Status:</strong>
                        <span class="status-badge status-<?= strtolower($librarian['status']); ?>">
                            <?= ucfirst($librarian['status']); ?>
                        </span>
                    </div>
                    <div class="profile-field"><strong>Date Created:</strong> <?= $librarian['date_created'] ?? 'N/A'; ?></div>
                    <div class="profile-field"><strong>Last Login:</strong> <?= $librarian['last_login'] ?? 'Never'; ?></div>
                </div>

                <div class="actions">
                    <?php
                    $id = $librarian['librarian_id'];
                    $status = strtolower(trim($librarian['status']));

                    if ($status === 'pending') {
                        echo "<button class='action-btn approve' onclick=\"updateStatus($id, 'approved')\">Approve</button>
                              <button class='action-btn reject' onclick=\"updateStatus($id, 'rejected')\">Reject</button>";
                    } elseif ($status === 'approved') {
                        echo "<button class='action-btn deactivate' onclick=\"updateStatus($id, 'inactive')\">Deactivate</button>";
                    } elseif ($status === 'inactive') {
                        echo "<button class='action-btn approve' onclick=\"updateStatus($id, 'approved')\">Reactivate</button>";
                    } elseif ($status === 'rejected') {
                        echo "<button class='action-btn approve' onclick=\"updateStatus($id, 'approved')\">Re-Approve</button>";
                    }

                    // 🗑️ Delete Button
                    echo "<button class='action-btn delete' onclick=\"deleteAccount($id)\">Delete Account</button>";
                    ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        function updateStatus(id, newStatus) {
            if (!confirm(`Are you sure you want to set this account to '${newStatus}'?`)) return;
            fetch('update-librarian-status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `id=${id}&status=${newStatus}`
                })
                .then(res => res.text())
                .then(msg => {
                    alert(msg);
                    location.reload();
                })
                .catch(() => alert('Error updating status.'));
        }

        function deleteAccount(id) {
            if (!confirm("⚠️ Are you sure you want to permanently delete this librarian account? This action cannot be undone.")) return;
            fetch('delete-librarian.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `id=${id}`
                })
                .then(res => res.text())
                .then(msg => {
                    alert(msg);
                    window.location.href = 'manage-librarians.php';
                })
                .catch(() => alert('Error deleting librarian.'));
        }
    </script>
</body>

</html>