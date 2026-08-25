<?php
session_start();

require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'user') {
    header('Location: login.php');
    exit;
}

$user_id = (int)$_SESSION['user_id'];

$search = trim($_GET['search'] ?? '');
$status = trim($_GET['status'] ?? '');
$priority = trim($_GET['priority'] ?? '');
$category = trim($_GET['category'] ?? '');

$where = [
    "c.user_id = ?"
];

$params = [$user_id];
$types = 'i';

if ($search !== '') {

    $where[] = "(
        CAST(c.id AS CHAR) LIKE ?
        OR c.subject LIKE ?
        OR c.description LIKE ?
    )";

    $value = '%' . $search . '%';

    $params[] = $value;
    $params[] = $value;
    $params[] = $value;

    $types .= 'sss';
}

if ($status !== '') {
    $where[] = "c.status = ?";
    $params[] = $status;
    $types .= 's';
}

if ($priority !== '') {
    $where[] = "c.priority = ?";
    $params[] = $priority;
    $types .= 's';
}

if ($category !== '') {
    $where[] = "c.category = ?";
    $params[] = $category;
    $types .= 's';
}

$sql = "
    SELECT
        c.id,
        c.subject,
        c.category,
        c.priority,
        c.status,
        c.created_at,
        c.updated_at
    FROM complaints c
    WHERE " . implode(" AND ", $where) . "
    ORDER BY c.created_at DESC
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . htmlspecialchars($conn->error));
}

$stmt->bind_param($types, ...$params);

$stmt->execute();

$result = $stmt->get_result();

$complaints = [];

while ($row = $result->fetch_assoc()) {
    $complaints[] = $row;
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

<title>My Complaints</title>

<link rel="stylesheet" href="../assets/css/style.css">

<style>

body {
    margin:0;
    background:#f5f7fb;
    font-family:Arial,sans-serif;
}

.container {
    max-width:1200px;
    margin:30px auto;
    padding:0 20px;
}

.panel {
    background:#fff;
    padding:22px;
    margin-bottom:25px;
    border-radius:10px;
    box-shadow:0 2px 10px rgba(0,0,0,.08);
}

.filter-grid {
    display:grid;
    grid-template-columns:2fr 1fr 1fr 1fr auto auto;
    gap:12px;
    align-items:end;
}

label {
    display:block;
    margin-bottom:6px;
    font-weight:bold;
}

input,
select {
    width:100%;
    box-sizing:border-box;
    padding:11px;
    border:1px solid #d1d5db;
    border-radius:6px;
}

.btn {
    display:inline-block;
    padding:11px 16px;
    border-radius:6px;
    text-decoration:none;
    border:0;
}

.btn-primary {
    background:#2563eb;
    color:white;
}

.btn-secondary {
    background:#6b7280;
    color:white;
}

.table-wrapper {
    overflow-x:auto;
}

table {
    width:100%;
    border-collapse:collapse;
}

th,
td {
    padding:13px;
    border-bottom:1px solid #e5e7eb;
    text-align:left;
}

th {
    background:#f3f4f6;
}

.badge {
    display:inline-block;
    padding:5px 9px;
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

.low {
    background:#e0f2fe;
    color:#075985;
}

.medium {
    background:#fef3c7;
    color:#92400e;
}

.high {
    background:#fee2e2;
    color:#991b1b;
}

@media(max-width:900px) {
    .filter-grid {
        grid-template-columns:1fr;
    }
}

</style>

</head>

<body>

<div class="container">

    <div style="display:flex;justify-content:space-between;align-items:center;">

        <h1>My Complaints</h1>

        <div>
            <a href="dashboard.php" class="btn btn-secondary">
                Dashboard
            </a>

            <a href="new_complaint.php" class="btn btn-primary">
                New Complaint
            </a>
        </div>

    </div>

    <div class="panel">

        <h3>Search & Filter</h3>

        <form method="GET">

            <div class="filter-grid">

                <div>

                    <label>Search</label>

                    <input
                        type="text"
                        name="search"
                        placeholder="ID, subject or description"
                        value="<?= htmlspecialchars($search) ?>"
                    >

                </div>

                <div>

                    <label>Status</label>

                    <select name="status">

                        <option value="">All Status</option>

                        <option value="Pending"
                            <?= $status === 'Pending' ? 'selected' : '' ?>>
                            Pending
                        </option>

                        <option value="In Progress"
                            <?= $status === 'In Progress' ? 'selected' : '' ?>>
                            In Progress
                        </option>

                        <option value="Resolved"
                            <?= $status === 'Resolved' ? 'selected' : '' ?>>
                            Resolved
                        </option>

                        <option value="Rejected"
                            <?= $status === 'Rejected' ? 'selected' : '' ?>>
                            Rejected
                        </option>

                    </select>

                </div>

                <div>

                    <label>Priority</label>

                    <select name="priority">

                        <option value="">All Priority</option>

                        <option value="Low"
                            <?= $priority === 'Low' ? 'selected' : '' ?>>
                            Low
                        </option>

                        <option value="Medium"
                            <?= $priority === 'Medium' ? 'selected' : '' ?>>
                            Medium
                        </option>

                        <option value="High"
                            <?= $priority === 'High' ? 'selected' : '' ?>>
                            High
                        </option>

                    </select>

                </div>

                <div>

                    <label>Category</label>

                    <select name="category">

                        <option value="">All Categories</option>

                        <option value="Service"
                            <?= $category === 'Service' ? 'selected' : '' ?>>
                            Service
                        </option>

                        <option value="Technical"
                            <?= $category === 'Technical' ? 'selected' : '' ?>>
                            Technical
                        </option>

                        <option value="Billing"
                            <?= $category === 'Billing' ? 'selected' : '' ?>>
                            Billing
                        </option>

                        <option value="Account"
                            <?= $category === 'Account' ? 'selected' : '' ?>>
                            Account
                        </option>

                        <option value="Other"
                            <?= $category === 'Other' ? 'selected' : '' ?>>
                            Other
                        </option>

                    </select>

                </div>

                <div>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Search
                    </button>

                </div>

                <div>

                    <a
                        href="complaints.php"
                        class="btn btn-secondary"
                    >
                        Clear
                    </a>

                </div>

            </div>

        </form>

    </div>

    <div class="panel">

        <h3>
            My Complaints (<?= count($complaints) ?>)
        </h3>

        <div class="table-wrapper">

            <table>

                <thead>

                <tr>
                    <th>ID</th>
                    <th>Subject</th>
                    <th>Category</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Action</th>
                </tr>

                </thead>

                <tbody>

                <?php if (empty($complaints)): ?>

                    <tr>
                        <td colspan="7" style="text-align:center;">
                            No complaints found.
                        </td>
                    </tr>

                <?php else: ?>

                    <?php foreach ($complaints as $complaint): ?>

                    <tr>

                        <td>
                            #<?= (int)$complaint['id'] ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($complaint['subject']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($complaint['category']) ?>
                        </td>

                        <td>
                            <span class="badge <?= badgeClass($complaint['priority']) ?>">
                                <?= htmlspecialchars($complaint['priority']) ?>
                            </span>
                        </td>

                        <td>
                            <span class="badge <?= badgeClass($complaint['status']) ?>">
                                <?= htmlspecialchars($complaint['status']) ?>
                            </span>
                        </td>

                        <td>
                            <?= htmlspecialchars($complaint['created_at']) ?>
                        </td>

                        <td>

                            <a
                                href="complaint.php?id=<?= (int)$complaint['id'] ?>"
                                class="btn btn-primary"
                            >
                                View
                            </a>

                        </td>

                    </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

</body>
</html>

