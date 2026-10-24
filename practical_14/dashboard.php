
<?php
require_once __DIR__ . "/auth.php";

if ($_SESSION["user_role"] !== "student") {
    header("Location: admin-dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>
</head>
<body>
    <h1>Student Dashboard</h1>

    <p>
        Welcome,
        <?php echo escape_html($_SESSION["user_name"]); ?>!
    </p>

    <p>You are logged in as a student.</p>

    <a href="logout.php">Logout</a>
    <br><br>
    <a href="index.html">Home</a>
</body>
</html>
