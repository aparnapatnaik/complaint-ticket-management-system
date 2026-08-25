<?php
session_start();

require_once "../config/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$sql = "SELECT 
            complaints.id,
            complaints.subject,
            complaints.category,
            complaints.priority,
            complaints.status,
            complaints.created_at,
            users.name,
            users.email
        FROM complaints
        JOIN users ON complaints.user_id = users.id
        ORDER BY complaints.created_at DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Complaints - ComplaintSys</title>

    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<div class="dashboard">

    <!-- Sidebar -->
    <aside class="sidebar">

        <div class="logo">
            Complaint<span>Sys</span>
        </div>

        <ul class="sidebar-menu">

            <li>
                <a href="dashboard.php">
                    Dashboard
                </a>
            </li>

            <li>
                <a href="complaints.php" class="active">
                    All Complaints
                </a>
            </li>

            <li>
                <a href="logout.php">
                    Logout
                </a>
            </li>

        </ul>

    </aside>


    <!-- Main Content -->
    <main class="main-content">

        <header class="topbar">

            <h1>All Complaints</h1>

            <div class="admin-info">

                <div class="admin-avatar">
                    A
                </div>

                <div class="admin-name">
                    Admin
                </div>

            </div>

        </header>


        <section class="content">

            <div class="welcome">

                <h2>Complaint Management</h2>

                <p>
                    View and manage all complaints submitted by users.
                </p>

            </div>


            <div class="panel">

                <div class="panel-header">

                    <h3>All Complaints</h3>

                </div>


                <div class="table-wrapper">

                    <table>

                        <thead>

                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Subject</th>
                                <th>Category</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>

                        </thead>


                        <tbody>

                        <?php if ($result && $result->num_rows > 0): ?>

                            <?php while ($complaint = $result->fetch_assoc()): ?>

                                <tr>

                                    <td>
                                        #<?php echo $complaint['id']; ?>
                                    </td>

                                    <td>
                                        <strong>
                                            <?php echo htmlspecialchars($complaint['name']); ?>
                                        </strong>
                                        <br>
                                        <small>
                                            <?php echo htmlspecialchars($complaint['email']); ?>
                                        </small>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($complaint['subject']); ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($complaint['category'] ?? 'N/A'); ?>
                                    </td>

                                    <td>

                                        <?php
                                        $priority = $complaint['priority'];

                                        if ($priority === 'High') {
                                            echo '<span class="badge badge-rejected">High</span>';
                                        } elseif ($priority === 'Medium') {
                                            echo '<span class="badge badge-pending">Medium</span>';
                                        } else {
                                            echo '<span class="badge badge-progress">Low</span>';
                                        }
                                        ?>

                                    </td>

                                    <td>

                                        <?php
                                        $status = $complaint['status'];

                                        if ($status === 'Pending') {
                                            echo '<span class="badge badge-pending">Pending</span>';
                                        } elseif ($status === 'In Progress') {
                                            echo '<span class="badge badge-progress">In Progress</span>';
                                        } elseif ($status === 'Resolved') {
                                            echo '<span class="badge badge-resolved">Resolved</span>';
                                        } elseif ($status === 'Rejected') {
                                            echo '<span class="badge badge-rejected">Rejected</span>';
                                        }
                                        ?>

                                    </td>

                                    <td>
                                        <?php
                                        echo date(
                                            "d M Y",
                                            strtotime($complaint['created_at'])
                                        );
                                        ?>
                                    </td>

                                    <td>

                                        <a
                                            href="complaint.php?id=<?php echo $complaint['id']; ?>"
                                            class="btn btn-primary"
                                        >
                                            View
                                        </a>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>

                                <td colspan="8" style="text-align:center;">
                                    No complaints found.
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
