
<?php
require_once __DIR__ . "/auth.php";

if ($_SESSION["user_role"] !== "admin") {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
</head>
<body>
    <h1>Admin Dashboard</h1>

    <p>
        Welcome,
        <?php echo escape_html($_SESSION["user_name"]); ?>!
    </p>

    <p>You are logged in as an administrator.</p>

    <a href="logout.php">Logout</a>
    <br><br>
    <a href="index.html">Home</a>
</body>
</html>
