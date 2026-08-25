<?php
session_start();

require_once "../config/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| Complaint Statistics
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            COUNT(*) AS total,
            SUM(status = 'Pending') AS pending,
            SUM(status = 'In Progress') AS in_progress,
            SUM(status = 'Resolved') AS resolved
        FROM complaints
        WHERE user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$stats = $stmt->get_result()->fetch_assoc();

$stmt->close();

$total = $stats['total'] ?? 0;
$pending = $stats['pending'] ?? 0;
$in_progress = $stats['in_progress'] ?? 0;
$resolved = $stats['resolved'] ?? 0;


/*
|--------------------------------------------------------------------------
| Recent Complaints
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            id,
            subject,
            category,
            priority,
            status,
            created_at
        FROM complaints
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT 5";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$complaints = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>User Dashboard - ComplaintSys</title>

    <link rel="stylesheet" href="../css/style.css">

</head>

<body>

<div class="dashboard">

    <!-- SIDEBAR -->

    <aside class="sidebar">

        <div class="logo">
            Complaint<span>Sys</span>
        </div>

        <ul class="sidebar-menu">

            <li>
                <a href="dashboard.php" class="active">
                    Dashboard
                </a>
            </li>

            <li>
                <a href="new_complaint.php">
                    Submit Complaint
                </a>
            </li>

            <li>
                <a href="complaints.php">
                    My Complaints
                </a>
            </li>

            <li>
                <a href="logout.php">
                    Logout
                </a>
            </li>

        </ul>

    </aside>


    <!-- MAIN -->

    <main class="main-content">

        <header class="topbar">

            <h1>
                User Dashboard
            </h1>

            <div class="admin-info">

                <div class="admin-avatar">
                    <?php
                    echo strtoupper(
                        substr($_SESSION['name'], 0, 1)
                    );
                    ?>
                </div>

                <div class="admin-name">

                    <?php
                    echo htmlspecialchars($_SESSION['name']);
                    ?>

                </div>

            </div>

        </header>


        <section class="content">

            <div class="welcome">

                <h2>
                    Welcome,
                    <?php
                    echo htmlspecialchars($_SESSION['name']);
                    ?>
                </h2>

                <p>
                    Manage and track your complaints from here.
                </p>

            </div>


            <!-- STATISTICS -->

            <div class="stats-grid">

                <div class="stat-card">

                    <div class="label">
                        Total Complaints
                    </div>

                    <div class="number">
                        <?php echo $total; ?>
                    </div>

                    <div class="description">
                        All your complaints
                    </div>

                </div>


                <div class="stat-card">

                    <div class="label">
                        Pending
                    </div>

                    <div class="number">
                        <?php echo $pending; ?>
                    </div>

                    <div class="description">
                        Awaiting action
                    </div>

                </div>


                <div class="stat-card">

                    <div class="label">
                        In Progress
                    </div>

                    <div class="number">
                        <?php echo $in_progress; ?>
                    </div>

                    <div class="description">
                        Currently processing
                    </div>

                </div>


                <div class="stat-card">

                    <div class="label">
                        Resolved
                    </div>

                    <div class="number">
                        <?php echo $resolved; ?>
                    </div>

                    <div class="description">
                        Successfully closed
                    </div>

                </div>

            </div>


            <!-- RECENT COMPLAINTS -->

            <div class="panel">

                <div class="panel-header">

                    <h3>
                        My Recent Complaints
                    </h3>

                    <a
                        href="complaints.php"
                        class="btn btn-secondary"
                    >
                        View All
                    </a>

                </div>


                <div class="table-wrapper">

                    <table>

                        <thead>

                            <tr>

                                <th>ID</th>
                                <th>Subject</th>
                                <th>Category</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Date</th>

                            </tr>

                        </thead>

                        <tbody>

                        <?php if ($complaints->num_rows > 0): ?>

                            <?php while ($complaint = $complaints->fetch_assoc()): ?>

                                <tr>

                                    <td>
                                        #<?php echo $complaint['id']; ?>
                                    </td>

                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $complaint['subject']
                                        );
                                        ?>
                                    </td>

                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $complaint['category']
                                        );
                                        ?>
                                    </td>

                                    <td>

                                        <span class="badge badge-pending">

                                            <?php
                                            echo htmlspecialchars(
                                                $complaint['priority']
                                            );
                                            ?>

                                        </span>

                                    </td>

                                    <td>

                                        <?php

                                        if ($complaint['status'] === 'Pending') {

                                            echo '<span class="badge badge-pending">Pending</span>';

                                        } elseif ($complaint['status'] === 'In Progress') {

                                            echo '<span class="badge badge-progress">In Progress</span>';

                                        } elseif ($complaint['status'] === 'Resolved') {

                                            echo '<span class="badge badge-resolved">Resolved</span>';

                                        } else {

                                            echo '<span class="badge badge-rejected">Rejected</span>';

                                        }

                                        ?>

                                    </td>

                                    <td>

                                        <?php
                                        echo date(
                                            "d M Y",
                                            strtotime(
                                                $complaint['created_at']
                                            )
                                        );
                                        ?>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>

                                <td
                                    colspan="6"
                                    style="text-align:center;"
                                >
                                    You haven't submitted any complaints yet.
                                </td>

                            </tr>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </section>

    </main>

</div>

</body>

</html>
