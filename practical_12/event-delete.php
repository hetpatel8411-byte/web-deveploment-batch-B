
<?php
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/auth.php";

if (!isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== "admin") {
    die("Access denied. Please log in as an admin.");
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request.");
}

$id = (int)($_POST["id"] ?? 0);

if ($id <= 0) {
    die("Invalid event ID.");
}

$stmt = mysqli_prepare($conn, "SELECT poster FROM events WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$event = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$event) {
    die("Event not found.");
}

$stmt = mysqli_prepare($conn, "DELETE FROM events WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    if (!empty($event["poster"])) {
        $path = __DIR__ . "/uploads/" . basename($event["poster"]);

        if (is_file($path)) {
            unlink($path);
        }
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    header("Location: events.php?deleted=1");
    exit;
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
die("Could not delete the event.");
?>
