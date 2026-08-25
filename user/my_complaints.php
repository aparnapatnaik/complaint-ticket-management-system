<?php

session_start();

require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

$stmt = $conn->prepare(
    "SELECT
        id,
        subject,
        category,
        priority,
        status,
        created_at
     FROM complaints
     WHERE user_id = ?
     ORDER BY created_at DESC"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html>

<head>

    <title>My Complaints</title>

</head>

<body>

<h1>My Complaints</h1>

<table border="1" cellpadding="10">

<tr>

    <th>ID</th>
    <th>Subject</th>
    <th>Category</th>
    <th>Priority</th>
    <th>Status</th>
    <th>Created</th>

</tr>

<?php if ($result->num_rows > 0): ?>

    <?php while ($complaint = $result->fetch_assoc()): ?>

        <tr>

            <td>
                <?php echo $complaint["id"]; ?>
            </td>

            <td>
                <?php echo htmlspecialchars($complaint["subject"]); ?>
            </td>

            <td>
                <?php echo htmlspecialchars($complaint["category"]); ?>
            </td>

            <td>
                <?php echo htmlspecialchars($complaint["priority"]); ?>
            </td>

            <td>
                <?php echo htmlspecialchars($complaint["status"]); ?>
            </td>

            <td>
                <?php echo htmlspecialchars($complaint["created_at"]); ?>
            </td>

        </tr>

    <?php endwhile; ?>

<?php else: ?>

    <tr>

        <td colspan="6">
            No complaints found.
        </td>

    </tr>

<?php endif; ?>

</table>

<br>

<a href="dashboard.php">
    Back to Dashboard
</a>

</body>

</html>

<?php

$stmt->close();

?>
