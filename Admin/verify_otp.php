<?php
session_start();
include "../PHP/db_connect.php";
date_default_timezone_set('Asia/Manila');

// if there's no pending registration, send them back
if (!isset($_SESSION['pending_user'])) {
    header("Location: register-librarian.php");
    exit;
}

$pending = $_SESSION['pending_user'];
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered = trim($_POST['otp'] ?? '');

    if ($entered === '') {
        $message = "⚠️ Please enter your verification code.";
    } elseif ($entered !== $pending['otp']) {
        $message = "❌ Invalid code. Please try again.";
    } elseif (time() > $pending['otp_expiry']) {
        $message = "⚠️ Code expired. Please re-register to get a new code.";
    } else {
        // all good — insert into DB
        $fullname = $pending['fullname'];
        $email    = $pending['email'];
        $section  = $pending['section'];
        $password = $pending['password'];
        $status   = 'active';

        $stmt = $conn->prepare("INSERT INTO tbl_librarians (fullname, email, password, section, status)
                                VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $fullname, $email, $password, $section, $status);

        if ($stmt->execute()) {
            unset($_SESSION['pending_user']);
            echo "<script>alert('✅ Registration successful! You can now log in.');
                  window.location.href='index.php';</script>";
            exit;
        } else {
            $message = "❌ Database error: please contact the administrator.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Email | CEIT Thesis Hub</title>
    <link rel="icon" type="image/png" href="pictures/Logo.png">
    <link rel="stylesheet" href="style.css">
    <style>
        body { font-family: Poppins, sans-serif; background:#f4f7fb; }
        .verify-container { max-width:440px; margin:80px auto; background:#fff;
            border-radius:12px; padding:40px 50px; box-shadow:0 6px 18px rgba(0,0,0,0.08); }
        .verify-container h2 { color:#0a3d91; margin-bottom:10px; }
        .verify-container p { color:#444; margin-bottom:20px; }
        .verify-container input { width:100%; padding:12px; font-size:16px;
            border:1px solid #cfd8e3; border-radius:8px; text-align:center; letter-spacing:4px; }
        .verify-container button { width:100%; margin-top:15px; background:#0a3d91; color:#fff;
            border:none; padding:12px; border-radius:8px; font-weight:600; cursor:pointer; transition:0.25s; }
        .verify-container button:hover { background:#083377; transform:translateY(-2px); }
        .message { color:#b91c1c; background:#fff0f0; border-left:4px solid #ef4444;
            padding:10px 12px; border-radius:8px; margin-top:15px; }
        .back { display:inline-block; margin-top:18px; text-decoration:none; color:#0a3d91; }
    </style>
</head>
<body>
    <div class="verify-container">
        <img src="pictures/Logo.png" alt="CEIT Logo" width="90" style="display:block;margin:0 auto 10px;">
        <h2>Email Verification</h2>
        <p>A 6-digit verification code was sent to <b><?= htmlspecialchars($pending['email']); ?></b></p>

        <form method="POST">
            <input type="text" name="otp" maxlength="6" placeholder="Enter code" required>
            <button type="submit">Verify</button>
        </form>

        <?php if ($message): ?>
            <p class="message"><?= htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <a href="register-librarian.php" class="back">← Back to Registration</a>
    </div>
</body>
</html>
