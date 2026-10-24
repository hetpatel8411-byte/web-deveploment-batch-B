
<?php
require_once "auth.php";
require_admin();
require_once "db.php";

function countRows($conn, $table)
{
    $allowed = ["students", "events", "registrations"];

    if (!in_array($table, $allowed, true)) {
        return 0;
    }

    $result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM " . $table);
    $row = mysqli_fetch_assoc($result);

    return $row["total"];
}

$students = countRows($conn, "students");
$events = countRows($conn, "events");
$registrations = countRows($conn, "registrations");

$sql = "SELECT r.registered_at, s.name AS student_name,
        e.title AS event_title
        FROM registrations r
        JOIN students s ON r.student_id = s.id
        JOIN events e ON r.event_id = e.id
        ORDER BY r.registered_at DESC LIMIT 5";

$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Practical 14</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>

<div class="sidebar">
    <h2>Practical 14</h2>
    <?php include "dynamic-menu.php"; ?>
</div>

<div class="main">
    <h1>Admin Dashboard</h1>
    <p>Welcome, <?php echo htmlspecialchars($_SESSION["user_name"]); ?>!</p>

    <div class="cards">
        <div class="card">
            <h3>Total Students</h3>
            <p><?php echo $students; ?></p>
        </div>

        <div class="card">
            <h3>Total Events</h3>
            <p><?php echo $events; ?></p>
        </div>

        <div class="card">
            <h3>Total Registrations</h3>
            <p><?php echo $registrations; ?></p>
        </div>
    </div>

    <h2>Latest Registrations</h2>

    <table>
        <tr>
            <th>Student</th>
            <th>Event</th>
            <th>Date</th>
        </tr>

        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
        <tr>
            <td><?php echo htmlspecialchars($row["student_name"]); ?></td>
            <td><?php echo htmlspecialchars($row["event_title"]); ?></td>
            <td><?php echo htmlspecialchars($row["registered_at"]); ?></td>
        </tr>
        <?php } ?>
    </table>
</div>

</body>
</html>
