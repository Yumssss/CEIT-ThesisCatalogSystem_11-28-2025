<?php
session_start();
if (!isset($_SESSION['reset_otp'])) {
    header("Location: forgot-password.php");
    exit();
}

$message = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $entered = trim($_POST['otp']);
    $stored = $_SESSION['reset_otp'];

    if (time() > $stored['expiry']) {
        $message = "⚠️ Code expired. Please request a new one.";
        unset($_SESSION['reset_otp']);
    } elseif ($entered === $stored['otp']) {
        $_SESSION['verified_email'] = $stored['email'];
        unset($_SESSION['reset_otp']);
        header("Location: reset-password.php");
        exit();
    } else {
        $message = "❌ Incorrect code. Try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Code | CEIT Thesis Hub</title>
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
                <h2>Enter Verification Code</h2>
                <p style="color:#555;">We sent a 6-digit code to your email.</p>

                <form method="POST" action="">
                    <div class="input-group">
                        <input type="text" name="otp" maxlength="6" placeholder="Enter code" required>
                    </div>
                    <button type="submit" class="login-btn">Verify</button>
                </form>

                <?php if (!empty($message)): ?>
                    <p style="color:red; margin-top:10px;"><?= htmlspecialchars($message) ?></p>
                <?php endif; ?>

                <a href="forgot-password.php" class="back-btn">← Back</a>
            </div>
        </div>
    </main>
</body>
</html>
