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

// 🔹 Fetch user info
if ($role === 'admin') {
    $username = $_SESSION['admin'];
    $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
} else {
    $librarian_id = $_SESSION['librarian_id'];
    $stmt = $conn->prepare("SELECT * FROM tbl_librarians WHERE librarian_id = ?");
    $stmt->bind_param("i", $librarian_id);
}

$stmt->execute();
$result = $stmt->get_result();
$user = $result ? $result->fetch_assoc() : null;

if (!$user) {
    $user = [
        'username' => '',
        'fullname' => '',
        'section' => '',
        'email' => '',
        'profile_pic' => ''
    ];
}

// 🧠 Handle profile picture update
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["profile_pic"])) {
    if ($role === 'librarian') {
        $targetDir = "pictures/profiles/";
        $newFileName = $user['profile_pic']; // default keep old

        if (!empty($_FILES["profile_pic"]["name"])) {
            $fileName = basename($_FILES["profile_pic"]["name"]);
            $ext = pathinfo($fileName, PATHINFO_EXTENSION);
            $newFileName = uniqid("profile_", true) . "." . $ext;
            $targetFile = $targetDir . $newFileName;

            if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $targetFile)) {
                // remove old picture
                if (!empty($user['profile_pic']) && file_exists($targetDir . $user['profile_pic'])) {
                    unlink($targetDir . $user['profile_pic']);
                }
                $update = $conn->prepare("UPDATE tbl_librarians SET profile_pic = ? WHERE librarian_id = ?");
                $update->bind_param("si", $newFileName, $librarian_id);
                $update->execute();
                $message = "✅ Profile picture updated successfully!";
                $user['profile_pic'] = $newFileName;
            } else {
                $message = "❌ Failed to upload image.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | CEIT Thesis Hub</title>
    <link rel="icon" type="image/png" href="pictures/Logo.png" />
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: "Poppins", sans-serif;
            background: #f5f8fb;
            margin: 0;
            padding: 0;
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
            margin-bottom: 25px;
            transition: 0.25s ease;
            box-shadow: 0 3px 8px rgba(10, 61, 145, 0.2);
        }

        .back-btn:hover {
            background: #062e72;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(10, 61, 145, 0.3);
        }

        /* Profile Header */
        .profile-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.05);
            padding: 25px 35px;
            margin-bottom: 25px;
        }

        .profile-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .profile-img {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #e0e7ff;
            cursor: pointer;
            transition: 0.3s ease;
        }

        .profile-img:hover {
            transform: scale(1.03);
        }

        .profile-details h3 {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 600;
            color: #0A3D91;
        }

        .profile-details p {
            margin: 3px 0;
            color: #666;
            font-size: 0.9rem;
        }

        .personal-info {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.05);
            padding: 30px 40px;
        }

        .personal-info h4 {
            font-size: 1.1rem;
            color: #0A3D91;
            font-weight: 600;
            margin-bottom: 25px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px 40px;
        }

        .info-item label {
            font-size: 0.85rem;
            color: #6b7280;
            font-weight: 500;
        }

        .info-item input {
            width: 100%;
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid #ccd6eb;
            background: #f9fbff;
            font-size: 0.9rem;
            outline: none;
            transition: 0.25s;
        }

        .info-item input:focus {
            border-color: #0A3D91;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(10, 61, 145, 0.12);
        }

        .message {
            background: #e9f9ef;
            color: #2e7d32;
            border-left: 4px solid #2e7d32;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.55);
            justify-content: center;
            align-items: center;
            z-index: 100;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .modal-content {
            background: #fff;
            padding: 30px;
            border-radius: 14px;
            text-align: center;
            width: 400px;
            max-width: 90%;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .modal-content img {
            width: 160px;
            height: 160px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
            border: 3px solid #e0e7ff;
        }

        .modal-content h3 {
            color: #0A3D91;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .modal-content input[type="file"] {
            display: none;
        }

        .modal-content label {
            background: #0A3D91;
            color: #fff;
            padding: 10px 18px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: 0.25s;
            display: inline-block;
        }

        .modal-content label:hover {
            background: #083377;
        }

        .save-btn {
            background: #22c55e;
            color: #fff;
            border: none;
            padding: 10px 16px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: 0.25s;
        }

        .save-btn:hover {
            background: #16a34a;
        }

        .close-btn {
            background: #ef4444;
            color: #fff;
            border: none;
            padding: 10px 16px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            margin-left: 10px;
            transition: 0.25s;
        }

        .close-btn:hover {
            background: #dc2626;
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
            <h2>Profile</h2>
            <div class="header-logo">
                <img src="pictures/Logo.png" alt="CEIT Logo" width="90" height="60">
            </div>
        </div>
    </header>

    <div class="container">
        <?php include 'sidebar.php'; ?>

        <main>
            <a href="settings.php" class="back-btn">← Back</a>

            <div class="profile-header">
                <div class="profile-info">
                    <?php if ($role === 'librarian'): ?>
                        <img id="profilePreview"
                            src="pictures/profiles/<?= !empty($user['profile_pic']) ? htmlspecialchars($user['profile_pic']) : 'default-avatar.png'; ?>"
                            alt="Profile" class="profile-img" onclick="openModal()">
                    <?php else: ?>
                        <img src="pictures/profiles/default-avatar.png" alt="Profile" class="profile-img">
                    <?php endif; ?>
                    <div class="profile-details">
                        <h3><?= htmlspecialchars($role === 'admin' ? $user['username'] : $user['fullname']); ?></h3>
                        <p><?= ucfirst($role); ?></p>
                    </div>
                </div>
            </div>

            <div class="personal-info">
                <h4>Personal Information</h4>
                <?php if (!empty($message)): ?>
                    <p class="message"><?= $message ?></p>
                <?php endif; ?>
                <div class="info-grid">
                    <?php if ($role === 'admin'): ?>
                        <div class="info-item">
                            <label>Username</label>
                            <input type="text" value="<?= htmlspecialchars($user['username']) ?>" readonly>
                        </div>
                    <?php else: ?>
                        <div class="info-item">
                            <label>Full Name</label>
                            <input type="text" value="<?= htmlspecialchars($user['fullname']) ?>" readonly>
                        </div>
                        <div class="info-item">
                            <label>Section</label>
                            <input type="text" value="<?= htmlspecialchars($user['section']) ?>" readonly>
                        </div>
                        <div class="info-item">
                            <label>Email</label>
                            <input type="email" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Profile Picture Modal -->
    <?php if ($role === 'librarian'): ?>
        <div class="modal" id="profileModal">
            <div class="modal-content">
                <h3>Profile Picture</h3>
                <form method="POST" enctype="multipart/form-data">
                    <img id="modalPreview"
                        src="pictures/profiles/<?= !empty($user['profile_pic']) ? htmlspecialchars($user['profile_pic']) : 'default-avatar.png'; ?>"
                        alt="Preview">
                    <br>
                    <label for="profile_pic">Change Picture</label>
                    <input type="file" name="profile_pic" id="profile_pic" accept="image/*" onchange="previewModal(event)">
                    <div style="margin-top:15px;">
                        <button type="submit" class="save-btn">Save</button>
                        <button type="button" class="close-btn" onclick="closeModal()">Close</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <script>
        const modal = document.getElementById('profileModal');

        function openModal() {
            modal.style.display = 'flex';
        }

        function closeModal() {
            modal.style.display = 'none';
        }

        function previewModal(event) {
            const reader = new FileReader();
            reader.onload = function() {
                document.getElementById('modalPreview').src = reader.result;
                document.getElementById('profilePreview').src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }

        window.onclick = (e) => {
            if (e.target === modal) closeModal();
        };
    </script>
</body>

</html>