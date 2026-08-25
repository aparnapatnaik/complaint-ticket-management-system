<?php
session_start();

require_once __DIR__ . '/../config/database.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $message = 'Please enter email and password.';
    } else {

        $stmt = $conn->prepare(
            "SELECT id, name, email, password, role
             FROM users
             WHERE email = ?
             LIMIT 1"
        );

        if (!$stmt) {
            $message = 'Database error. Please try again.';
        } else {

            $stmt->bind_param('s', $email);
            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows === 1) {

                $user = $result->fetch_assoc();

                if (
                    password_verify($password, $user['password']) &&
                    $user['role'] === 'admin'
                ) {

                    session_regenerate_id(true);

                    /*
                     * Set both names because different admin
                     * pages may use different session variables.
                     */
                    $_SESSION['user_id'] = (int)$user['id'];
                    $_SESSION['admin_id'] = (int)$user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['admin_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['role'] = 'admin';
                    $_SESSION['logged_in'] = true;

                    header('Location: dashboard.php');
                    exit;

                } else {

                    $message = 'Invalid admin email or password.';
                }

            } else {

                $message = 'Invalid admin email or password.';
            }

            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Complaint System</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family:
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                Roboto,
                Arial,
                sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            background: #f4f6f9;
        }

        .card {
            width: 100%;
            max-width: 420px;
            background: #fff;
            padding: 40px;
            border-radius: 14px;
            box-shadow: 0 10px 35px rgba(0,0,0,.10);
        }

        .header {
            text-align: center;
            margin-bottom: 28px;
        }

        .badge {
            display: inline-block;
            padding: 6px 14px;
            margin-bottom: 12px;
            border-radius: 20px;
            background: #e2e8f0;
            color: #334155;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        h1 {
            font-size: 25px;
            color: #1e293b;
        }

        .alert {
            margin-bottom: 20px;
            padding: 12px 14px;
            border-radius: 7px;
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #991b1b;
            text-align: center;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            color: #475569;
            font-size: 14px;
            font-weight: 600;
        }

        input {
            width: 100%;
            padding: 13px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            background: #f8fafc;
            font-size: 15px;
            outline: none;
        }

        input:focus {
            border-color: #2563eb;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(37,99,235,.12);
        }

        button {
            width: 100%;
            padding: 13px;
            margin-top: 5px;
            border: none;
            border-radius: 7px;
            background: #1e293b;
            color: #fff;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
        }

        button:hover {
            background: #0f172a;
        }

        .footer {
            margin-top: 24px;
            text-align: center;
            color: #64748b;
            font-size: 14px;
        }

        .footer a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
        }

        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

<div class="card">

    <div class="header">
        <span class="badge">Admin Portal</span>
        <h1>Admin Sign In</h1>
    </div>

    <?php if ($message !== ''): ?>
        <div class="alert">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">

        <div class="form-group">
            <label for="email">Admin Email</label>

            <input
                type="email"
                id="email"
                name="email"
                placeholder="admin@complaint.com"
                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                required
                autocomplete="username"
            >
        </div>

        <div class="form-group">
            <label for="password">Password</label>

            <input
                type="password"
                id="password"
                name="password"
                placeholder="Enter your password"
                required
                autocomplete="current-password"
            >
        </div>

        <button type="submit">
            Log In to Dashboard
        </button>

    </form>

    <div class="footer">
        Not an admin?
        <a href="../user/login.php">User Portal</a>
    </div>

</div>

</body>
</html>
