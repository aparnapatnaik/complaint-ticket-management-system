<?php

session_start();

require_once __DIR__ . '/config/database.php';

$s3_config = require __DIR__ . '/config/s3.php';

$s3 = $s3_config['client'];
$s3_bucket = $s3_config['bucket'];

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Access denied.');
}

$user_id = (int)$_SESSION['user_id'];
$role = $_SESSION['role'] ?? '';

$attachment_id = (int)($_GET['id'] ?? 0);

if ($attachment_id <= 0) {
    http_response_code(400);
    exit('Invalid attachment.');
}

$stmt = $conn->prepare("
    SELECT
        a.*,
        c.user_id AS complaint_user_id
    FROM complaint_attachments a
    INNER JOIN complaints c
        ON a.complaint_id = c.id
    WHERE a.id = ?
    LIMIT 1
");

if (!$stmt) {
    http_response_code(500);
    exit('Database error.');
}

$stmt->bind_param('i', $attachment_id);
$stmt->execute();

$result = $stmt->get_result();
$attachment = $result->fetch_assoc();

$stmt->close();

if (!$attachment) {
    http_response_code(404);
    exit('Attachment not found.');
}

/*
|--------------------------------------------------------------------------
| Access control
|--------------------------------------------------------------------------
*/

if (
    $role !== 'admin'
    && (int)$attachment['complaint_user_id'] !== $user_id
) {
    http_response_code(403);
    exit('Access denied.');
}

/*
|--------------------------------------------------------------------------
| Download from S3
|--------------------------------------------------------------------------
*/

$s3_key = ltrim($attachment['file_path'], '/');

try {

    $object = $s3->getObject([
        'Bucket' => $s3_bucket,
        'Key' => $s3_key
    ]);

} catch (Throwable $e) {

    error_log(
        'S3 attachment download failed: ' .
        $e->getMessage()
    );

    http_response_code(404);
    exit('File not found.');
}

$download_name = basename($attachment['original_name']);

$mime = $attachment['mime_type'] ?? '';

if ($mime === '') {
    $mime = 'application/octet-stream';
}

$body = $object['Body'];

header('Content-Type: ' . $mime);

header(
    'Content-Disposition: attachment; filename="' .
    str_replace('"', '', $download_name) .
    '"'
);

header('Content-Length: ' . $object['ContentLength']);

header('X-Content-Type-Options: nosniff');

echo $body;
exit;
