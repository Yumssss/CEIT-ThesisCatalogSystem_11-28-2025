<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include "../PHP/db_connect.php";
date_default_timezone_set('Asia/Manila');

$message = "";

/**
 * Handle POST registration -> send OTP and save pending user in session
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // collect & sanitize
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname  = trim($_POST['lastname'] ?? '');
    $email     = strtolower(trim($_POST['email'] ?? ''));
    $section   = trim($_POST['section'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm_password'] ?? '';

    // basic validation
    if ($firstname === '' || $lastname === '' || $email === '' || $section === '' || $password === '' || $confirm === '') {
        $message = "⚠️ Please fill out all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "⚠️ Please enter a valid email address.";
    } elseif ($password !== $confirm) {
        $message = "⚠️ Passwords do not match. Please try again.";
    } elseif (strlen($password) < 8) {
        $message = "⚠️ Password must be at least 8 characters.";
    } else {
        // check if email exists
        $checkEmail = $conn->prepare("SELECT COUNT(*) AS c FROM tbl_librarians WHERE email = ?");
        $checkEmail->bind_param("s", $email);
        $checkEmail->execute();
        $res = $checkEmail->get_result();
        $emailExists = false;
        if ($res) {
            $emailExists = $res->fetch_assoc()['c'] > 0;
        }
        $checkEmail->close();

        if ($emailExists) {
            $message = "⚠️ Email already exists. Please use a different one.";
        } else {
            // prepare pending user data and send OTP
            $fullname = $firstname . ' ' . $lastname;
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // 6-digit OTP
            $otp = str_pad((string)rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $otpExpiry = time() + (10 * 60); // valid 10 minutes

            // store pending user in session (do not insert to DB yet)
            $_SESSION['pending_user'] = [
                'fullname' => $fullname,
                'email' => $email,
                'section' => $section,
                'password' => $hashedPassword,
                'otp' => $otp,
                'otp_expiry' => $otpExpiry,
                'created_at' => time()
            ];

            // -----------------------------
            // SEND OTP EMAIL (PHPMailer)
            // -----------------------------
            require_once __DIR__ . '/../phpmailer/src/Exception.php';
            require_once __DIR__ . '/../phpmailer/src/PHPMailer.php';
            require_once __DIR__ . '/../phpmailer/src/SMTP.php';

            $mail = new PHPMailer\PHPMailer\PHPMailer(true);

            try {
                // ---------- SMTP CONFIG (UPDATE THESE) ----------
                $mail->isSMTP();
                $mail->Host       = 'rhinielcollection.shop'; // usually "mail." before domain
                $mail->SMTPAuth   = true;
                $mail->Username   = 'thesiscatalogemail@rhinielcollection.shop'; // your domain email
                $mail->Password   = '@Qwerty.123'; // your actual mailbox password
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS; // use SMTPS for port 465
                $mail->Port       = 465;
                // -------------------------------------------------

                $mail->setFrom('thesiscatalogemail@rhinielcollection.shop', 'CEIT Thesis Hub');
                $mail->addAddress($email, $fullname);

                $mail->isHTML(true);
                $mail->Subject = 'CEIT Thesis Hub — Verify your email (OTP)';
                $mail->Body = "
                    <div style='font-family:Poppins,Arial,sans-serif;color:#222'>
                        <h2 style='color:#0a3d91;margin:0 0 8px 0'>Email Verification</h2>
                        <p>Hello " . htmlspecialchars($firstname) . ",</p>
                        <p>Your verification code is:</p>
                        <h1 style='color:#0a3d91;letter-spacing:4px;'>$otp</h1>
                        <p style='font-size:0.9rem;color:#555'>This code will expire in 10 minutes.</p>
                    </div>
                ";

                $mail->send();

                // redirect to verify OTP page
                header("Location: verify_otp.php");
                exit();
            } catch (Exception $e) {
                $message = "❌ Could not send verification email. Please try again later.";
                error_log("PHPMailer error: " . $mail->ErrorInfo);
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
    <title>Librarian Registration | CEIT Thesis Hub</title>
    <link rel="icon" type="image/png" href="pictures/Logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css" />
    <style>
        .message {
            margin-top: 12px;
            color: #b91c1c;
            background: #fff0f0;
            padding: 8px 12px;
            border-radius: 8px;
            border-left: 4px solid #ef4444;
        }
    </style>
</head>

<body>
    <div class="register-container">
        <img src="pictures/Logo.png" alt="CEIT Logo" />
        <h2>Librarian Registration</h2>

        <form method="POST" action="">
            <div class="name-group">
                <input type="text" name="firstname" placeholder="First Name" required value="<?= isset($firstname) ? htmlspecialchars($firstname) : '' ?>">
                <input type="text" name="lastname" placeholder="Last Name" required value="<?= isset($lastname) ? htmlspecialchars($lastname) : '' ?>">
            </div>

            <input type="text" name="section" placeholder="Section" required value="<?= isset($section) ? htmlspecialchars($section) : '' ?>">
            <input type="email" name="email" placeholder="Email Address" required value="<?= isset($email) ? htmlspecialchars($email) : '' ?>">

            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>

            <div class="password-rules">
                <strong>Password must:</strong><br>
                • Be at least 8 characters long<br>
                • Contain one uppercase letter<br>
                • Contain one lowercase letter<br>
                • Contain one number or symbol
            </div>

            <button type="submit" class="login-btn" style="margin-top:14px;">Register</button>
        </form>

        <?php if (!empty($message)): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <div class="footer-link" style="margin-top:12px;">
            <a href="index.php">Back to Login</a>
        </div>
    </div>
</body>

</html>