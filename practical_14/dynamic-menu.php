
<?php
$role = $_SESSION["user_role"] ?? "student";
?>

<a href="admin-dashboard.php">Dashboard</a>

<?php if ($role === "admin") { ?>
    <a href="users.php">Manage Users</a>
    <a href="audit-log.php">Audit Log</a>
<?php } ?>

<a href="index.html">Home</a>
<a href="logout.php">Logout</a>
