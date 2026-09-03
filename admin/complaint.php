<?php
session_start();

require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

$complaint_id = (int)($_GET['id'] ?? 0);

if ($complaint_id <= 0) {
    header('Location: dashboard.php');
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
    LIMIT 1
");

$stmt->bind_param('i', $complaint_id);

$stmt->execute();

$result = $stmt->get_result();

$complaint = $result->fetch_assoc();

$stmt->close();

if (!$complaint) {
    http_response_code(404);
    die('Complaint not found.');
}

/* Timeline */

$stmt = $conn->prepare("
    SELECT
        ct.*,
        u.name AS actor_name
    FROM complaint_timeline ct
    LEFT JOIN users u ON ct.user_id = u.id
    WHERE ct.complaint_id = ?
    ORDER BY ct.created_at ASC, ct.id ASC
");

$stmt->bind_param('i', $complaint_id);

$stmt->execute();

$result = $stmt->get_result();

$timeline = [];

while ($row = $result->fetch_assoc()) {
    $timeline[] = $row;
}

$stmt->close();

/* Responses */

$stmt = $conn->prepare("
    SELECT
        cr.*,
        u.name AS admin_name
    FROM complaint_responses cr
    LEFT JOIN users u ON cr.admin_id = u.id
    WHERE cr.complaint_id = ?
    ORDER BY cr.created_at ASC
");

$stmt->bind_param('i', $complaint_id);

$stmt->execute();

$result = $stmt->get_result();

$responses = [];

while ($row = $result->fetch_assoc()) {
    $responses[] = $row;
}

$stmt->close();

/* Attachments */

$attachments = [];

$stmt = $conn->prepare("
    SELECT *
    FROM complaint_attachments
    WHERE complaint_id = ?
    ORDER BY uploaded_at DESC
");

$stmt->bind_param('i', $complaint_id);

$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $attachments[] = $row;
}

$stmt->close();

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
Admin - Complaint #<?= $complaint_id ?>
</title>

<link rel="stylesheet" href="../assets/css/style.css">

<style>

body {
    margin:0;
    background:#f5f7fb;
    font-family:Arial,sans-serif;
}

.container {
    max-width:1100px;
    margin:30px auto;
    padding:0 20px;
}

.card {
    background:#fff;
    padding:25px;
    border-radius:10px;
    margin-bottom:25px;
    box-shadow:0 2px 10px rgba(0,0,0,.08);
}

.btn {
    display:inline-block;
    padding:10px 15px;
    border-radius:6px;
    text-decoration:none;
    border:0;
    cursor:pointer;
}

.btn-primary {
    background:#2563eb;
    color:#fff;
}

.btn-secondary {
    background:#6b7280;
    color:#fff;
}

.btn-success {
    background:#16a34a;
    color:#fff;
}

.badge {
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

.timeline-item {
    position:relative;
    padding:0 0 25px 30px;
    border-left:2px solid #d1d5db;
}

.timeline-item:last-child {
    border-left-color:transparent;
}

.timeline-dot {
    position:absolute;
    left:-8px;
    top:0;
    width:14px;
    height:14px;
    border-radius:50%;
    background:#2563eb;
}

.response,
.attachment {
    border:1px solid #e5e7eb;
    border-radius:8px;
    padding:15px;
    margin-bottom:12px;
}

.attachment {
    display:flex;
    justify-content:space-between;
    align-items:center;
}

textarea {
    width:100%;
    min-height:120px;
    box-sizing:border-box;
    padding:12px;
    border:1px solid #d1d5db;
    border-radius:6px;
}

select {
    padding:10px;
    border:1px solid #d1d5db;
    border-radius:6px;
}

</style>

</head>

<body>

<div class="container">

    <div style="margin-bottom:20px;">

        <a href="dashboard.php" class="btn btn-secondary">
            ← Dashboard
        </a>

        <a
            href="respond.php?id=<?= $complaint_id ?>"
            class="btn btn-primary"
        >
            Respond
        </a>

    </div>

    <div class="card">

        <h1>
            Complaint #<?= $complaint_id ?>
        </h1>

        <h2>
            <?= htmlspecialchars($complaint['subject']) ?>
        </h2>

        <p>
            <strong>User:</strong>
            <?= htmlspecialchars($complaint['user_name']) ?>
            —
            <?= htmlspecialchars($complaint['user_email']) ?>
        </p>

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

        <hr>

        <h3>Description</h3>

        <div style="white-space:pre-wrap;">
            <?= htmlspecialchars($complaint['description']) ?>
        </div>

    </div>

    <div class="card">

        <h2>Update Status</h2>

        <form method="POST" action="update_status.php">

            <input
                type="hidden"
                name="complaint_id"
                value="<?= $complaint_id ?>"
            >

            <select name="status" required>

                <option value="Pending"
                    <?= $complaint['status'] === 'Pending' ? 'selected' : '' ?>>
                    Pending
                </option>

                <option value="In Progress"
                    <?= $complaint['status'] === 'In Progress' ? 'selected' : '' ?>>
                    In Progress
                </option>

                <option value="Resolved"
                    <?= $complaint['status'] === 'Resolved' ? 'selected' : '' ?>>
                    Resolved
                </option>

                <option value="Rejected"
                    <?= $complaint['status'] === 'Rejected' ? 'selected' : '' ?>>
                    Rejected
                </option>

            </select>

            <button class="btn btn-success" type="submit">
                Update Status
            </button>

        </form>

    </div>

    <div class="card">

        <h2>Timeline</h2>

        <?php if (empty($timeline)): ?>

            <p>No timeline records.</p>

        <?php else: ?>

            <?php foreach ($timeline as $event): ?>

            <div class="timeline-item">

                <div class="timeline-dot"></div>

                <strong>
                    <?= htmlspecialchars($event['action_type']) ?>
                </strong>

                <p>
                    <?= htmlspecialchars($event['description']) ?>
                </p>

                <?php if ($event['old_status'] !== null || $event['new_status'] !== null): ?>

                    <p>
                        <?= htmlspecialchars($event['old_status'] ?? 'N/A') ?>

                        →

                        <?= htmlspecialchars($event['new_status'] ?? 'N/A') ?>
                    </p>

                <?php endif; ?>

                <small>

                    <?= htmlspecialchars($event['created_at']) ?>

                    <?php if (!empty($event['actor_name'])): ?>

                        · <?= htmlspecialchars($event['actor_name']) ?>

                    <?php endif; ?>

                </small>

            </div>

            <?php endforeach; ?>

        <?php endif; ?>

    </div>

    <div class="card">

        <h2>Administrator Responses</h2>

        <?php if (empty($responses)): ?>

            <p>No responses yet.</p>

        <?php else: ?>

            <?php foreach ($responses as $response): ?>

            <div class="response">

                <strong>
                    <?= htmlspecialchars($response['admin_name'] ?? 'Administrator') ?>
                </strong>

                <p style="white-space:pre-wrap;">
                    <?= htmlspecialchars($response['response']) ?>
                </p>

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
