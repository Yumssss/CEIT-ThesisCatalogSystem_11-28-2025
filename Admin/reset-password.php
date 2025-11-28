<?php
session_start();
include "../PHP/db_connect.php";
if (!isset($_SESSION['verified_email'])) {
    header("Location: forgot-password.php");
    exit();
}

$email = $_SESSION['verified_email'];
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if ($new !== $confirm) {
        $message = "⚠️ Passwords do not match.";
    } elseif (strlen($new) < 8) {
        $message = "⚠️ Password must be at least 8 characters.";
    } else {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE tbl_librarians SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashed, $email);
        if ($stmt->execute()) {
            unset($_SESSION['verified_email']);
            $message = "✅ Password reset successful! Redirecting...";
            echo "<script>setTimeout(()=>window.location.href='index.php', 2000);</script>";
        } else {
            $message = "❌ Something went wrong. Try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | CEIT Thesis Hub</title>
    <link rel="icon" type="image/png" href="pictures/Logo.png">
    <link rel="stylesheet" href="style.css">
    <style>
        .login-box h2 { color: #0a3d91; margin-bottom: 10px; }
        .back-btn { display: inline-block; margin-top: 15px; color: #0a3d91; text-decoration: none; font-weight: 500; }
        .back-btn:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <main class="login-main">
        <div class="login-container">
            <div class="login-box">
                <img src="pictures/Logo.png" alt="CEIT Logo" class="logo">
                <h2>Reset Password</h2>

                <form method="POST" action="">
                    <div class="input-group">
                        <input type="password" name="new_password" placeholder="New Password" required>
                    </div>
                    <div class="input-group">
                        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                    </div>
                    <button type="submit" class="login-btn">Save Password</button>
                </form>

                <?php if (!empty($message)): ?>
                    <p style="color:red; margin-top:10px;"><?= htmlspecialchars($message) ?></p>
                <?php endif; ?>

                <a href="index.php" class="back-btn">← Back to Login</a>
            </div>
        </div>
    </main>
</body>
</html>
