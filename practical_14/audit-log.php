
<?php
require_once "auth.php";
require_admin();
require_once "db.php";

$sql = "SELECT a.action, a.details, a.created_at, s.name
        FROM audit_logs a
        LEFT JOIN students s ON a.user_id = s.id
        ORDER BY a.id DESC";

$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Audit Log</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
<div class="main">
    <h1>Audit Log</h1>
    <p><a href="admin-dashboard.php">Back to Dashboard</a></p>

    <table>
        <tr>
            <th>User</th>
            <th>Action</th>
            <th>Details</th>
            <th>Date</th>
        </tr>

        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
        <tr>
            <td><?php echo htmlspecialchars($row["name"] ?? "Unknown"); ?></td>
            <td><?php echo htmlspecialchars($row["action"]); ?></td>
            <td><?php echo htmlspecialchars($row["details"] ?? ""); ?></td>
            <td><?php echo htmlspecialchars($row["created_at"]); ?></td>
        </tr>
        <?php } ?>
    </table>
</div>
</body>
</html>
