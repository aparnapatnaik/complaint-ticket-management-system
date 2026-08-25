<?php

session_start();

require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'user') {
    header('Location: login.php');
    exit;
}

$user_id = (int)$_SESSION['user_id'];

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $subject = trim($_POST['subject'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $priority = trim($_POST['priority'] ?? '');
    $description = trim($_POST['description'] ?? '');

    $allowed_categories = [
        'Service',
        'Technical',
        'Billing',
        'Account',
        'Other'
    ];

    $allowed_priorities = [
        'Low',
        'Medium',
        'High'
    ];

    if ($subject === '') {
        $error = 'Subject is required.';
    } elseif ($description === '') {
        $error = 'Description is required.';
    } elseif (!in_array($category, $allowed_categories, true)) {
        $error = 'Invalid category.';
    } elseif (!in_array($priority, $allowed_priorities, true)) {
        $error = 'Invalid priority.';
    }

    /*
    |--------------------------------------------------------------------------
    | Validate attachment
    |--------------------------------------------------------------------------
    */

    $has_file = isset($_FILES['attachment'])
        && $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE;

    $file_data = null;

    if ($error === '' && $has_file) {

        if ($_FILES['attachment']['error'] !== UPLOAD_ERR_OK) {

            $error = 'File upload failed.';

        } else {

            $max_size = 5 * 1024 * 1024;

            if ($_FILES['attachment']['size'] > $max_size) {

                $error = 'File must be 5 MB or smaller.';

            } else {

                $original_name = $_FILES['attachment']['name'];

                $tmp_name = $_FILES['attachment']['tmp_name'];

                $extension = strtolower(
                    pathinfo($original_name, PATHINFO_EXTENSION)
                );

                $allowed_extensions = [
                    'jpg',
                    'jpeg',
                    'png',
                    'gif',
                    'pdf',
                    'doc',
                    'docx',
                    'txt'
                ];

                if (!in_array($extension, $allowed_extensions, true)) {

                    $error = 'File type is not allowed.';

                } else {

                    $finfo = new finfo(FILEINFO_MIME_TYPE);

                    $mime = $finfo->file($tmp_name);

                    $allowed_mimes = [
                        'image/jpeg',
                        'image/png',
                        'image/gif',
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'text/plain'
                    ];

                    if (!in_array($mime, $allowed_mimes, true)) {

                        $error = 'Invalid file content.';

                    } else {

                        $stored_name =
                            bin2hex(random_bytes(16))
                            . '.'
                            . $extension;

                        $relative_path =
                            'uploads/complaints/'
                            . $stored_name;

                        $absolute_path =
                            __DIR__
                            . '/../'
                            . $relative_path;

                        $file_data = [
                            'original_name' => $original_name,
                            'stored_name' => $stored_name,
                            'relative_path' => $relative_path,
                            'absolute_path' => $absolute_path,
                            'mime' => $mime,
                            'size' => (int)$_FILES['attachment']['size'],
                            'tmp_name' => $tmp_name
                        ];
                    }
                }
            }
        }
    }

    if ($error === '') {

        $conn->begin_transaction();

        try {

            $status = 'Pending';

            $stmt = $conn->prepare("
                INSERT INTO complaints
                (
                    user_id,
                    subject,
                    category,
                    priority,
                    description,
                    status
                )
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            if (!$stmt) {
                throw new Exception($conn->error);
            }

            $stmt->bind_param(
                'isssss',
                $user_id,
                $subject,
                $category,
                $priority,
                $description,
                $status
            );

            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }

            $complaint_id = $stmt->insert_id;

            $stmt->close();

            /*
            |--------------------------------------------------------------------------
            | Save attachment
            |--------------------------------------------------------------------------
            */

            if ($file_data !== null) {

                if (!move_uploaded_file(
                    $file_data['tmp_name'],
                    $file_data['absolute_path']
                )) {

                    throw new Exception(
                        'Could not save uploaded file.'
                    );
                }

                $stmt = $conn->prepare("
                    INSERT INTO complaint_attachments
                    (
                        complaint_id,
                        user_id,
                        original_name,
                        stored_name,
                        file_path,
                        file_type,
                        file_size
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");

                if (!$stmt) {
                    throw new Exception($conn->error);
                }

                $stmt->bind_param(
                    'iissssi',
                    $complaint_id,
                    $user_id,
                    $file_data['original_name'],
                    $file_data['stored_name'],
                    $file_data['relative_path'],
                    $file_data['mime'],
                    $file_data['size']
                );

                if (!$stmt->execute()) {
                    throw new Exception($stmt->error);
                }

                $stmt->close();
            }

            $conn->commit();

            header(
                'Location: complaint.php?id=' .
                $complaint_id
            );

            exit;

        } catch (Throwable $e) {

            $conn->rollback();

            if (
                isset($file_data)
                && $file_data !== null
                && file_exists($file_data['absolute_path'])
            ) {
                unlink($file_data['absolute_path']);
            }

            $error = 'Unable to create complaint: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>New Complaint</title>

<link rel="stylesheet" href="../assets/css/style.css">

<style>

body {
    margin:0;
    background:#f5f7fb;
    font-family:Arial,sans-serif;
}

.container {
    max-width:800px;
    margin:40px auto;
    padding:20px;
}

.card {
    background:#fff;
    padding:30px;
    border-radius:10px;
    box-shadow:0 2px 10px rgba(0,0,0,.08);
}

.form-group {
    margin-bottom:18px;
}

label {
    display:block;
    margin-bottom:7px;
    font-weight:bold;
}

input,
select,
textarea {
    width:100%;
    box-sizing:border-box;
    padding:12px;
    border:1px solid #d1d5db;
    border-radius:6px;
}

textarea {
    min-height:160px;
    resize:vertical;
}

.btn {
    display:inline-block;
    padding:11px 17px;
    border:0;
    border-radius:6px;
    cursor:pointer;
    text-decoration:none;
}

.btn-primary {
    background:#2563eb;
    color:#fff;
}

.btn-secondary {
    background:#6b7280;
    color:#fff;
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

    <div style="margin-bottom:20px;">

        <a
            href="complaints.php"
            class="btn btn-secondary"
        >
            ← Back
        </a>

    </div>

    <h1>Submit New Complaint</h1>

    <?php if ($error !== ''): ?>

        <div class="error">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>

    <form
        method="POST"
        enctype="multipart/form-data"
    >

        <div class="form-group">

            <label>
                Subject
            </label>

            <input
                type="text"
                name="subject"
                maxlength="255"
                required
                value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>"
            >

        </div>

        <div class="form-group">

            <label>
                Category
            </label>

            <select name="category" required>

                <option value="">
                    Select Category
                </option>

                <?php
                foreach (
                    ['Service','Technical','Billing','Account','Other']
                    as $option
                ):
                ?>

                    <option
                        value="<?= $option ?>"
                        <?= ($_POST['category'] ?? '') === $option
                            ? 'selected'
                            : '' ?>
                    >
                        <?= $option ?>
                    </option>

                <?php endforeach; ?>

            </select>

        </div>

        <div class="form-group">

            <label>
                Priority
            </label>

            <select name="priority" required>

                <option value="">
                    Select Priority
                </option>

                <?php
                foreach (
                    ['Low','Medium','High']
                    as $option
                ):
                ?>

                    <option
                        value="<?= $option ?>"
                        <?= ($_POST['priority'] ?? '') === $option
                            ? 'selected'
                            : '' ?>
                    >
                        <?= $option ?>
                    </option>

                <?php endforeach; ?>

            </select>

        </div>

        <div class="form-group">

            <label>
                Description
            </label>

            <textarea
                name="description"
                required
            ><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>

        </div>

        <div class="form-group">

            <label>
                Attachment
            </label>

            <input
                type="file"
                name="attachment"
                accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.txt"
            >

            <small>
                Optional. Maximum 5 MB.
                Allowed: JPG, PNG, GIF, PDF, DOC, DOCX, TXT.
            </small>

        </div>

        <button
            type="submit"
            class="btn btn-primary"
        >
            Submit Complaint
        </button>

    </form>

</div>

</div>

</body>
</html>
