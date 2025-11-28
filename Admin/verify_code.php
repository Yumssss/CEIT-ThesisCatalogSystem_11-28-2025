<?php
// verify_code.php
include "../PHP/db_connect.php";
date_default_timezone_set('Asia/Manila');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'msg' => 'Invalid request']);
    exit;
}

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$code  = isset($_POST['code']) ? trim($_POST['code']) : '';

if (empty($email) || empty($code)) {
    echo json_encode(['success' => false, 'msg' => 'Email and code required.']);
    exit;
}

$stmt = $conn->prepare("SELECT librarian_id, reset_expiry, reset_code FROM tbl_librarians WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows !== 1) {
    echo json_encode(['success' => false, 'msg' => 'No account found.']);
    exit;
}

$user = $res->fetch_assoc();

// check match and expiry
if ($user['reset_code'] === null) {
    echo json_encode(['success' => false, 'msg' => 'No reset code was requested.']);
    exit;
}

if ($user['reset_code'] !== $code) {
    echo json_encode(['success' => false, 'msg' => 'Incorrect code.']);
    exit;
}

if (strtotime($user['reset_expiry']) < time()) {
    echo json_encode(['success' => false, 'msg' => 'Code expired.']);
    exit;
}

// valid
echo json_encode(['success' => true, 'msg' => 'Code verified.']);
exit;
