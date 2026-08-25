<?php
session_start();

require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $subject = trim($_POST["subject"]);
    $description = trim($_POST["description"]);
    $category = trim($_POST["category"]);
    $priority = $_POST["priority"];

    $stmt = $conn->prepare(
        "INSERT INTO complaints
        (user_id, subject, description, category, priority)
        VALUES (?, ?, ?, ?, ?)"
    );

    $stmt->bind_param(
        "issss",
        $_SESSION["user_id"],
        $subject,
        $description,
        $category,
        $priority
    );

    if ($stmt->execute()) {
        $message = "Complaint submitted successfully.";
        $messageType = "success";
    } else {
        $message = "Failed to submit complaint.";
        $messageType = "error";
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Complaint</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }

        body {
            background-color: #f4f6f9;
            color: #333;
            min-height: 100vh;
        }

        .navbar {
            background-color: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 16px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar .brand {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1a202c;
            text-decoration: none;
        }

        .navbar .user-info {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .navbar .user-name {
            font-size: 0.95rem;
            color: #4a5568;
            font-weight: 500;
        }

        .btn-logout {
            padding: 8px 14px;
            font-size: 0.875rem;
            color: #e53e3e;
            border: 1px solid #feb2b2;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-logout:hover {
            background-color: #fff5f5;
            border-color: #e53e3e;
        }

        .container {
            max-width: 600px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .card {
            background: #ffffff;
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        .card h1 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 24px;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            font-size: 0.875rem;
            margin-bottom: 20px;
            text-align: center;
        }

        .alert.error {
            background-color: #fde8e8;
            color: #9b1c1c;
            border: 1px solid #f8b4b4;
        }

        .alert.success {
            background-color: #def7ec;
            color: #03543f;
            border: 1px solid #84e1bc;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-row {
            display: flex;
            gap: 16px;
        }

        .form-row .form-group {
            flex: 1;
        }

        .form-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #4a5568;
            margin-bottom: 8px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 14px;
            font-size: 0.95rem;
            border: 1px solid #cbd5e0;
            border-radius: 6px;
            background-color: #f8fafc;
            transition: border-color 0.2s, background-color 0.2s;
            outline: none;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #3182ce;
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.15);
        }

        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 8px;
        }

        .btn-submit {
            padding: 12px 24px;
            background-color: #2563eb;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .btn-submit:hover {
            background-color: #1d4ed8;
        }

        .btn-back {
            color: #718096;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .btn-back:hover {
            color: #2d3748;
        }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="dashboard.php" class="brand">Complaint Manager</a>
    <div class="user-info">
        <span class="user-name">Hi, <?php echo htmlspecialchars($_SESSION["user_name"]); ?></span>
        <a href="logout.php" class="btn-logout">Logout</a>
    </div>
</nav>

<div class="container">
    <div class="card">
        <h1>Submit a Complaint</h1>

        <?php if (!empty($message)): ?>
            <div class="alert <?php echo htmlspecialchars($messageType); ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="subject">Subject</label>
                <input type="text" id="subject" name="subject" placeholder="Brief summary of the issue" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category">
                        <option value="Technical">Technical</option>
                        <option value="Service">Service</option>
                        <option value="Billing">Billing</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="priority">Priority</label>
                    <select id="priority" name="priority">
                        <option value="Low">Low</option>
                        <option value="Medium" selected>Medium</option>
                        <option value="High">High</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" placeholder="Provide full details about your issue..." required></textarea>
            </div>

            <div class="form-actions">
                <a href="dashboard.php" class="btn-back">← Back to Dashboard</a>
                <button type="submit" class="btn-submit">Submit Complaint</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
