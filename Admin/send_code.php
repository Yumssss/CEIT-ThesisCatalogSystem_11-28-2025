<?php
// send_code.php
include "../PHP/db_connect.php";
date_default_timezone_set('Asia/Manila');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'msg' => 'Invalid request.']);
    exit;
}

$email = isset($_POST['email']) ? trim($_POST['email']) : '';

if (empty($email)) {
    echo json_encode(['success' => false, 'msg' => 'Email required.']);
    exit;
}

// find librarian
$stmt = $conn->prepare("SELECT librarian_id, fullname FROM tbl_librarians WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows !== 1) {
    echo json_encode(['success' => false, 'msg' => 'Email not found.']);
    exit;
}

$user = $res->fetch_assoc();
$librarian_id = $user['librarian_id'];

// generate code (6 digits)
$code = random_int(100000, 999999);
$expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));

// store code and expiry
$upd = $conn->prepare("UPDATE tbl_librarians SET reset_code = ?, reset_expiry = ? WHERE librarian_id = ?");
$upd->bind_param("ssi", $code, $expiry, $librarian_id);
$upd->execute();

// send email (placeholder). Replace with PHPMailer in production.
$subject = "CEIT Thesis Hub - Password Reset Code";
$body = "Hello {$user['fullname']},\n\nYour password reset code is: {$code}\nThis code is valid for 10 minutes.\n\nIf you didn't request this, ignore this message.";
$headers = "From: noreply@yourdomain.com\r\n";

@mail($email, $subject, $body, $headers); // best-effort

// mask email for UI: e.g. j***n@g****.com
function mask_email($e)
{
    $parts = explode("@", $e);
    $name = $parts[0];
    $domain = $parts[1] ?? '';
    $name_mask = strlen($name) <= 2 ? str_repeat('*', strlen($name)) : substr($name, 0, 1) . str_repeat('*', max(1, strlen($name) - 2)) . substr($name, -1);
    // mask domain before first dot: g****.com
    $dparts = explode(".", $domain);
    $dom0 = $dparts[0] ?? '';
    $dom0_mask = strlen($dom0) <= 1 ? '*' : substr($dom0, 0, 1) . str_repeat('*', max(1, strlen($dom0) - 1));
    $rest = count($dparts) > 1 ? '.' . implode('.', array_slice($dparts, 1)) : '';
    return $name_mask . '@' . $dom0_mask . $rest;
}

$masked = mask_email($email);

echo json_encode(['success' => true, 'msg' => 'Code sent', 'masked' => $masked]);
exit;
