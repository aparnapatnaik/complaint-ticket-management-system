<?php

session_start();

require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

$complaint_id = (int)($_POST['complaint_id'] ?? 0);
$new_status = trim($_POST['status'] ?? '');

$allowed_statuses = [
    'Pending',
    'In Progress',
    'Resolved',
    'Rejected'
];

if (
    $complaint_id <= 0 ||
    !in_array($new_status, $allowed_statuses, true)
) {
    header('Location: dashboard.php');
    exit;
}

$stmt = $conn->prepare("
    SELECT status
    FROM complaints
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param('i', $complaint_id);

$stmt->execute();

$result = $stmt->get_result();

$complaint = $result->fetch_assoc();

$stmt->close();

if (!$complaint) {
    header('Location: dashboard.php');
    exit;
}

$stmt = $conn->prepare("
    UPDATE complaints
    SET
        status = ?,
        updated_at = CURRENT_TIMESTAMP
    WHERE id = ?
");

$stmt->bind_param(
    'si',
    $new_status,
    $complaint_id
);

$stmt->execute();

$stmt->close();

header(
    'Location: complaint.php?id=' .
    $complaint_id
);

exit;
