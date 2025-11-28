<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../PHP/db_connect.php';
session_start();
date_default_timezone_set('Asia/Manila');

$error = "";

// 🧠 Handle Login Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $error = "";

    /* =========================================================
       1️⃣ CHECK SUPER ADMIN FIRST (admin table)
    ========================================================= */
    $hashed = md5($password);
    $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $hashed);
    $stmt->execute();
    $adminResult = $stmt->get_result();

    if ($adminResult->num_rows === 1) {
        // SUCCESS → Super Admin
        $admin = $adminResult->fetch_assoc();

        $_SESSION['admin'] = $admin['username'];
        $_SESSION['role'] = 'admin';

        header("Location: dashboard.php");
        exit();
    }

    /* =========================================================
       2️⃣ CHECK LIBRARIAN (tbl_librarians)
    ========================================================= */
    $stmt = $conn->prepare("SELECT * FROM tbl_librarians WHERE email = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $libResult = $stmt->get_result();

    if ($libResult->num_rows === 1) {

        $user = $libResult->fetch_assoc();

        // ⛔ Account status restrictions
        if ($user['status'] === 'pending') {
            $error = "🕓 Your librarian account is still pending approval.";
        } elseif ($user['status'] === 'inactive') {
            $error = "🚫 Your librarian account is deactivated. Contact admin.";
        }
        // Password verify
        elseif (password_verify($password, $user['password'])) {

            // SUCCESS → Librarian
            $_SESSION['librarian_id'] = $user['librarian_id'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['section'] = $user['section'];
            $_SESSION['role'] = 'librarian';

            // Update last login
            $update = $conn->prepare("UPDATE tbl_librarians SET last_login = NOW() WHERE librarian_id = ?");
            $update->bind_param("i", $user['librarian_id']);
            $update->execute();

            header("Location: dashboard.php");
            exit();
        } else {
            $error = "❌ Incorrect password.";
        }
    } else {
        // Neither admin nor librarian
        $error = "⚠️ Account not found.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login | CEIT Thesis Hub</title>
    <link rel="icon" type="image/png" href="pictures/Logo.png" />
    <link rel="stylesheet" href="style.css" />

    <style>

    </style>
</head>

<body>
    <main class="login-main">
        <div class="login-container">
            <div class="login-box">
                <img src="pictures/Logo.png" alt="CEIT Thesis Hub Logo" class="logo" />
                <h2>CEIT Thesis Hub Login</h2>

                <form method="POST" action="">
                    <div class="input-group">
                        <input type="text" name="username" placeholder="Username or Email" required />
                    </div>

                    <div class="input-group password-group">
                        <input
                            type="password"
                            name="password"
                            placeholder="Password"
                            required
                            autocomplete="off"
                            oncopy="return false"
                            onpaste="return false">
                        <span class="toggle-password">
                            <img src="pictures/close-eye.png" alt="Toggle Password">
                        </span>
                    </div>

                    <!-- <div class="input-group select-group" style="margin-bottom: 10px;">
                        <select name="role" required>
                            <option value="" disabled selected>Select Role</option>
                            <option value="admin">Super Admin</option>
                            <option value="librarian">Librarian</option>
                        </select>
                    </div> -->

                    <button type="submit" class="login-btn">LOG IN</button>
                    <a href="forgot-password.php" class="forgot-link">Forgot password?</a>

                    <?php if (!empty($error)): ?>
                        <p style="color:red; margin-top:10px; text-align:center;"><?php echo $error; ?></p>
                    <?php endif; ?>
                </form>

                <p style="margin-top:15px;">
                    Not yet a librarian? <a href="register-librarian.php">Register here</a>.
                </p>
            </div>
        </div>
    </main>

    <script>
        // ✅ Password visibility toggle (fixed)
        const passwordInput = document.querySelector('input[name="password"]');
        const toggleSpan = document.querySelector('.toggle-password');
        const toggleImg = toggleSpan.querySelector('img');

        toggleSpan.addEventListener("click", () => {
            const isHidden = passwordInput.getAttribute("type") === "password";
            passwordInput.setAttribute("type", isHidden ? "text" : "password");
            toggleImg.src = isHidden ?
                "pictures/visible.png" // 👁️ when visible
                :
                "pictures/close-eye.png"; // 🙈 when hidden
        });
    </script>


</body>

</html>