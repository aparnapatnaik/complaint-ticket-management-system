<?php
session_start();

require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'user') {
    header('Location: login.php');
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$complaint_id = (int)($_GET['id'] ?? 0);

if ($complaint_id <= 0) {
    header('Location: complaints.php');
    exit;
}

$stmt = $conn->prepare("
    SELECT
        c.*,
        u.name AS user_name,
        u.email AS user_email
    FROM complaints c
    INNER JOIN users u ON c.user_id = u.id
    WHERE c.id = ?
      AND c.user_id = ?
    LIMIT 1
");

$stmt->bind_param(
    'ii',
    $complaint_id,
    $user_id
);

$stmt->execute();

$result = $stmt->get_result();

$complaint = $result->fetch_assoc();

$stmt->close();

if (!$complaint) {
    http_response_code(404);
    die('Complaint not found.');
}

/*
|--------------------------------------------------------------------------
| Timeline
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        ct.*,
        u.name AS actor_name
    FROM complaint_timeline ct
    LEFT JOIN users u
        ON ct.user_id = u.id
    WHERE ct.complaint_id = ?
    ORDER BY ct.created_at ASC, ct.id ASC
");

$stmt->bind_param('i', $complaint_id);

$stmt->execute();

$timeline_result = $stmt->get_result();

$timeline = [];

while ($row = $timeline_result->fetch_assoc()) {
    $timeline[] = $row;
}

$stmt->close();

/*
|--------------------------------------------------------------------------
| Responses
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        cr.*,
        u.name AS admin_name
    FROM complaint_responses cr
    LEFT JOIN users u
        ON cr.admin_id = u.id
    WHERE cr.complaint_id = ?
    ORDER BY cr.created_at ASC
");

$stmt->bind_param('i', $complaint_id);

$stmt->execute();

$response_result = $stmt->get_result();

$responses = [];

while ($row = $response_result->fetch_assoc()) {
    $responses[] = $row;
}

$stmt->close();

/*
|--------------------------------------------------------------------------
| Attachments
|--------------------------------------------------------------------------
*/

$attachments = [];

$check = $conn->query("SHOW TABLES LIKE 'complaint_attachments'");

if ($check && $check->num_rows > 0) {

    $stmt = $conn->prepare("
        SELECT *
        FROM complaint_attachments
        WHERE complaint_id = ?
        ORDER BY created_at DESC
    ");

    $stmt->bind_param('i', $complaint_id);

    $stmt->execute();

    $attachment_result = $stmt->get_result();

    while ($row = $attachment_result->fetch_assoc()) {
        $attachments[] = $row;
    }

    $stmt->close();
}

function badgeClass($value)
{
    return strtolower(str_replace(' ', '-', $value));
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>
Complaint #<?= $complaint_id ?>
</title>

<link rel="stylesheet" href="../assets/css/style.css">

<style>

body {
    background:#f5f7fb;
    font-family:Arial,sans-serif;
    margin:0;
}

.container {
    max-width:1100px;
    margin:30px auto;
    padding:0 20px;
}

.card {
    background:#fff;
    border-radius:10px;
    padding:25px;
    margin-bottom:25px;
    box-shadow:0 2px 10px rgba(0,0,0,.08);
}

.badge {
    display:inline-block;
    padding:6px 10px;
    border-radius:20px;
    font-size:12px;
    font-weight:bold;
}

.pending {
    background:#fef3c7;
    color:#92400e;
}

.in-progress {
    background:#dbeafe;
    color:#1e40af;
}

.resolved {
    background:#dcfce7;
    color:#166534;
}

.rejected {
    background:#fee2e2;
    color:#991b1b;
}

.timeline {
    position:relative;
    margin-top:20px;
}

.timeline-item {
    position:relative;
    padding:0 0 25px 35px;
    border-left:2px solid #d1d5db;
}

.timeline-item:last-child {
    border-left:2px solid transparent;
}

.timeline-dot {
    position:absolute;
    left:-8px;
    top:0;
    width:14px;
    height:14px;
    background:#2563eb;
    border-radius:50%;
}

.timeline-title {
    font-weight:bold;
    margin-bottom:5px;
}

.timeline-date {
    font-size:12px;
    color:#6b7280;
}

.response {
    border:1px solid #e5e7eb;
    border-radius:8px;
    padding:15px;
    margin-bottom:12px;
}

.attachment {
    display:flex;
    justify-content:space-between;
    align-items:center;
    border:1px solid #e5e7eb;
    padding:12px;
    margin-bottom:10px;
    border-radius:7px;
}

.btn {
    display:inline-block;
    padding:10px 15px;
    border-radius:6px;
    text-decoration:none;
}

.btn-primary {
    background:#2563eb;
    color:white;
}

.btn-secondary {
    background:#6b7280;
    color:white;
}

</style>

</head>

<body>

<div class="container">

    <div style="margin-bottom:20px;">

        <a href="complaints.php" class="btn btn-secondary">
            ← Back to Complaints
        </a>

    </div>

    <div class="card">

        <h1>
            #<?= $complaint_id ?>
            -
            <?= htmlspecialchars($complaint['subject']) ?>
        </h1>

        <p>
            <strong>Category:</strong>
            <?= htmlspecialchars($complaint['category']) ?>
        </p>

        <p>
            <strong>Priority:</strong>
            <?= htmlspecialchars($complaint['priority']) ?>
        </p>

        <p>
            <strong>Status:</strong>

            <span class="badge <?= badgeClass($complaint['status']) ?>">
                <?= htmlspecialchars($complaint['status']) ?>
            </span>
        </p>

        <p>
            <strong>Created:</strong>
            <?= htmlspecialchars($complaint['created_at']) ?>
        </p>

        <?php if (!empty($complaint['updated_at'])): ?>

        <p>
            <strong>Last Updated:</strong>
            <?= htmlspecialchars($complaint['updated_at']) ?>
        </p>

        <?php endif; ?>

        <hr>

        <h3>Description</h3>

        <div style="white-space:pre-wrap;">
            <?= htmlspecialchars($complaint['description']) ?>
        </div>

    </div>

    <div class="card">

        <h2>Complaint Timeline</h2>

        <?php if (empty($timeline)): ?>

            <p>No timeline events available.</p>

        <?php else: ?>

        <div class="timeline">

            <?php foreach ($timeline as $event): ?>

            <div class="timeline-item">

                <div class="timeline-dot"></div>

                <div class="timeline-title">
                    <?= htmlspecialchars($event['action_type']) ?>
                </div>

                <div>
                    <?= htmlspecialchars($event['description']) ?>
                </div>

                <?php if (!empty($event['old_status']) || !empty($event['new_status'])): ?>

                <div style="margin-top:5px;">

                    <?= htmlspecialchars($event['old_status'] ?? 'N/A') ?>

                    →

                    <?= htmlspecialchars($event['new_status'] ?? 'N/A') ?>

                </div>

                <?php endif; ?>

                <div class="timeline-date">

                    <?= htmlspecialchars($event['created_at']) ?>

                    <?php if (!empty($event['actor_name'])): ?>

                        · <?= htmlspecialchars($event['actor_name']) ?>

                    <?php endif; ?>

                </div>

            </div>

            <?php endforeach; ?>

        </div>

        <?php endif; ?>

    </div>

    <div class="card">

        <h2>Administrator Responses</h2>

        <?php if (empty($responses)): ?>

            <p>No administrator response yet.</p>

        <?php else: ?>

            <?php foreach ($responses as $response): ?>

            <div class="response">

                <strong>
                    <?= htmlspecialchars($response['admin_name'] ?? 'Administrator') ?>
                </strong>

                <div style="margin-top:8px;white-space:pre-wrap;">
                    <?= htmlspecialchars($response['response']) ?>
                </div>

                <small>
                    <?= htmlspecialchars($response['created_at']) ?>
                </small>

            </div>

            <?php endforeach; ?>

        <?php endif; ?>

    </div>

    <div class="card">

        <h2>Attachments</h2>

        <?php if (empty($attachments)): ?>

            <p>No attachments.</p>

        <?php else: ?>

            <?php foreach ($attachments as $attachment): ?>

            <div class="attachment">

                <div>

                    <strong>
                        <?= htmlspecialchars($attachment['original_name']) ?>
                    </strong>

                    <br>

                    <small>
                        <?= number_format($attachment['file_size'] / 1024, 1) ?> KB
                    </small>

                </div>

                <a
                    class="btn btn-primary"
                    href="../download_attachment.php?id=<?= (int)$attachment['id'] ?>"
                >
                    Download
                </a>

            </div>

            <?php endforeach; ?>

        <?php endif; ?>

    </div>

</div>

</body>
</html>
