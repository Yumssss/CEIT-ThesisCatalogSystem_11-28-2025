<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include "../PHP/db_connect.php";

require_once __DIR__ . '/../phpmailer/src/Exception.php';
require_once __DIR__ . '/../phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

date_default_timezone_set('Asia/Manila');
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);

    // Check if librarian exists
    $stmt = $conn->prepare("SELECT librarian_id, fullname FROM tbl_librarians WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $user = $res->fetch_assoc();

        // Generate OTP
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiry = time() + (10 * 60);
        $_SESSION['reset_otp'] = [
            'email' => $email,
            'otp' => $otp,
            'expiry' => $expiry
        ];

        // Send OTP via PHPMailer
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'rhinielcollection.shop'; // ✅ correct host (with "mail.")
            $mail->SMTPAuth = true;
            $mail->Username = 'thesiscatalogemail@rhinielcollection.shop';
            $mail->Password = '@Qwerty.123';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;

            $mail->setFrom('thesiscatalogemail@rhinielcollection.shop', 'CEIT Thesis Hub');
            $mail->addAddress($email, $user['fullname']);
            $mail->isHTML(true);
            $mail->Subject = 'CEIT Thesis Hub - Password Reset Code';
            $mail->Body = "
                <div style='font-family:Poppins,Arial,sans-serif;color:#333'>
                    <h2 style='color:#0a3d91;'>Password Reset Request</h2>
                    <p>Hello {$user['fullname']},</p>
                    <p>Your password reset code is:</p>
                    <h1 style='color:#0a3d91;'>$otp</h1>
                    <p>This code will expire in 10 minutes.</p>
                </div>
            ";

            $mail->send();
            header("Location: verify_reset.php");
            exit();
        } catch (Exception $e) {
            $message = "❌ Failed to send email. Please try again later.";
            error_log("Mailer error: " . $mail->ErrorInfo);
            echo "<pre>Mailer error: " . $mail->ErrorInfo . "</pre>"; // 🔍 show error for debugging
        }
    } else {
        $message = "⚠️ No account found with that email address.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | CEIT Thesis Hub</title>
    <link rel="icon" type="image/png" href="pictures/Logo.png">
    <link rel="stylesheet" href="style.css">
    <style>
        .login-box h2 { margin-bottom: 10px; color: #0a3d91; }
        .back-btn {
            display: inline-block;
            margin-top: 15px;
            color: #0a3d91;
            text-decoration: none;
            font-weight: 500;
        }
        .back-btn:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <main class="login-main">
        <div class="login-container">
            <div class="login-box">
                <img src="pictures/Logo.png" alt="CEIT Logo" class="logo">
                <h2>Forgot Password</h2>
                <p style="color:#555;">Enter your registered email to receive a reset code.</p>

                <form method="POST" action="">
                    <div class="input-group">
                        <input type="email" name="email" placeholder="Enter your email" required>
                    </div>
                    <button type="submit" class="login-btn">Send Code</button>
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
