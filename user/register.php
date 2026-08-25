<?php
session_start();

require_once "../config/database.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST['name'] ?? "");
    $email = trim($_POST['email'] ?? "");
    $password = $_POST['password'] ?? "";
    $confirm_password = $_POST['confirm_password'] ?? "";

    // Validate name
    if ($name === "") {

        $error = "Please enter your name.";

    // Validate email
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    // Validate password
    } elseif (strlen($password) < 6) {

        $error = "Password must be at least 6 characters.";

    // Confirm password
    } elseif ($password !== $confirm_password) {

        $error = "Passwords do not match.";

    } else {

        // Check whether email already exists
        $check_sql = "SELECT id FROM users WHERE email = ?";

        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();

        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {

            $error = "An account with this email already exists.";

        } else {

            // Securely hash password
            $hashed_password = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $insert_sql = "INSERT INTO users
                           (name, email, password, role)
                           VALUES (?, ?, ?, 'user')";

            $insert_stmt = $conn->prepare($insert_sql);

            $insert_stmt->bind_param(
                "sss",
                $name,
                $email,
                $hashed_password
            );

            if ($insert_stmt->execute()) {

                $success = "Registration successful! You can now login.";

                // Clear form values
                $name = "";
                $email = "";

            } else {

                $error = "Registration failed. Please try again.";
            }

            $insert_stmt->close();
        }

        $check_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>User Registration - ComplaintSys</title>

    <link
        rel="stylesheet"
        href="../css/style.css"
    >

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
            max-width: 450px;
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

        .success-message {
            background: #dcfce7;
            color: #166534;
            padding: 12px 15px;
            border-radius: 7px;
            margin-bottom: 18px;
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

            <h2>
                Create Account
            </h2>

            <p>
                Register to submit and track complaints
            </p>

        </div>


        <?php if ($error !== ""): ?>

            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>


        <?php if ($success !== ""): ?>

            <div class="success-message">
                <?php echo htmlspecialchars($success); ?>
            </div>

        <?php endif; ?>


        <form method="POST">

            <div class="form-group">

                <label for="name">
                    Full Name
                </label>

                <input
                    type="text"
                    name="name"
                    id="name"
                    class="form-control"
                    value="<?php echo htmlspecialchars($name ?? ''); ?>"
                    placeholder="Enter your full name"
                    required
                >

            </div>


            <div class="form-group">

                <label for="email">
                    Email Address
                </label>

                <input
                    type="email"
                    name="email"
                    id="email"
                    class="form-control"
                    value="<?php echo htmlspecialchars($email ?? ''); ?>"
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
                    placeholder="Minimum 6 characters"
                    required
                >

            </div>


            <div class="form-group">

                <label for="confirm_password">
                    Confirm Password
                </label>

                <input
                    type="password"
                    name="confirm_password"
                    id="confirm_password"
                    class="form-control"
                    placeholder="Re-enter your password"
                    required
                >

            </div>


            <button
                type="submit"
                class="btn btn-primary"
                style="width:100%;"
            >
                Create Account
            </button>

        </form>


        <div class="auth-footer">

            Already have an account?

            <a href="login.php">
                Login
            </a>

        </div>

    </div>

</div>

</body>

</html>
