<?php

session_start();

require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

$admin_id = (int)$_SESSION['user_id'];

$complaint_id = (int)(
    $_GET['id']
    ?? $_POST['complaint_id']
    ?? 0
);

if ($complaint_id <= 0) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $response = trim($_POST['response'] ?? '');

    if ($response === '') {

        $error = 'Response cannot be empty.';

    } else {

        $stmt = $conn->prepare("
            INSERT INTO complaint_responses
            (
                complaint_id,
                admin_id,
                response
            )
            VALUES (?, ?, ?)
        ");

        if (!$stmt) {
            $error = $conn->error;
        } else {

            $stmt->bind_param(
                'iis',
                $complaint_id,
                $admin_id,
                $response
            );

            if ($stmt->execute()) {

                $stmt->close();

                header(
                    'Location: complaint.php?id=' .
                    $complaint_id
                );

                exit;

            } else {

                $error = $stmt->error;

                $stmt->close();
            }
        }
    }
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
    exit('Complaint not found.');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Respond to Complaint</title>

<link rel="stylesheet" href="../assets/css/style.css">

<style>

body {
    background:#f5f7fb;
    font-family:Arial,sans-serif;
}

.container {
    max-width:800px;
    margin:40px auto;
    padding:20px;
}

.card {
    background:white;
    padding:30px;
    border-radius:10px;
    box-shadow:0 2px 10px rgba(0,0,0,.08);
}

textarea {
    width:100%;
    min-height:200px;
    padding:12px;
    box-sizing:border-box;
    border:1px solid #d1d5db;
    border-radius:6px;
}

.btn {
    display:inline-block;
    padding:11px 17px;
    border:0;
    border-radius:6px;
    text-decoration:none;
    cursor:pointer;
}

.btn-primary {
    background:#2563eb;
    color:white;
}

.btn-secondary {
    background:#6b7280;
    color:white;
}

.error {
    background:#fee2e2;
    color:#991b1b;
    padding:12px;
    border-radius:6px;
    margin-bottom:20px;
}

</style>

</head>

<body>

<div class="container">

<div class="card">

    <a
        href="complaint.php?id=<?= $complaint_id ?>"
        class="btn btn-secondary"
    >
        ← Back
    </a>

    <h1>
        Respond to Complaint #<?= $complaint_id ?>
    </h1>

    <h3>
        <?= htmlspecialchars($complaint['subject']) ?>
    </h3>

    <p>
        <strong>User:</strong>
        <?= htmlspecialchars($complaint['user_name']) ?>
    </p>

    <?php if ($error !== ''): ?>

        <div class="error">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>

    <form method="POST">

        <input
            type="hidden"
            name="complaint_id"
            value="<?= $complaint_id ?>"
        >

        <label>
            Response
        </label>

        <br><br>

        <textarea
            name="response"
            required
            placeholder="Write your response..."
        ></textarea>

        <br><br>

        <button
            type="submit"
            class="btn btn-primary"
        >
            Submit Response
        </button>

    </form>

</div>

</div>

</body>
</html>
