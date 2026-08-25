<?php
session_start();

require_once "../config/database.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST['email'] ?? "");
    $password = $_POST['password'] ?? "";

    if ($email === "" || $password === "") {

        $error = "Please enter your email and password.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } else {

        $sql = "SELECT id, name, email, password, role
                FROM users
                WHERE email = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {

                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] === 'admin') {
                    header("Location: ../admin/dashboard.php");
                } else {
                    header("Location: dashboard.php");
                }

                exit;

            } else {

                $error = "Incorrect email or password.";
            }

        } else {

            $error = "Incorrect email or password.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>User Login - ComplaintSys</title>

    <link rel="stylesheet" href="../css/style.css">

    <style>

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f4f7fb;
        }

        .auth-container {
            width: 100%;
            max-width: 430px;
            padding: 20px;
        }

        .auth-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 35px;
            box-shadow: 0 5px 20px rgba(15, 23, 42, 0.08);
        }

        .auth-logo {
            text-align: center;
            font-size: 25px;
            font-weight: bold;
            color: #172033;
            margin-bottom: 8px;
        }

        .auth-logo span {
            color: #2563eb;
        }

        .auth-title {
            text-align: center;
            margin-bottom: 25px;
        }

        .auth-title h2 {
            margin-bottom: 7px;
        }

        .auth-title p {
            color: #64748b;
            font-size: 14px;
        }

        .error-message {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px 15px;
            border-radius: 7px;
            margin-bottom: 18px;
        }

        .auth-footer {
            text-align: center;
            margin-top: 20px;
            color: #64748b;
            font-size: 14px;
        }

        .auth-footer a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
        }

    </style>

</head>

<body>

<div class="auth-container">

    <div class="auth-card">

        <div class="auth-logo">
            Complaint<span>Sys</span>
        </div>

        <div class="auth-title">

            <h2>User Login</h2>

            <p>
                Login to manage your complaints
            </p>

        </div>

        <?php if ($error !== ""): ?>

            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>

        <form method="POST">

            <div class="form-group">

                <label for="email">
                    Email Address
                </label>

                <input
                    type="email"
                    name="email"
                    id="email"
                    class="form-control"
                    placeholder="Enter your email"
                    required
                >

            </div>

            <div class="form-group">

                <label for="password">
                    Password
                </label>

                <input
                    type="password"
                    name="password"
                    id="password"
                    class="form-control"
                    placeholder="Enter your password"
                    required
                >

            </div>

            <button
                type="submit"
                class="btn btn-primary"
                style="width:100%;"
            >
                Login
            </button>

        </form>

        <div class="auth-footer">

            Don't have an account?

            <a href="register.php">
                Register
            </a>

        </div>

    </div>

</div>

</body>

</html>
