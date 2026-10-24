
<?php
header("Content-Type: application/json; charset=UTF-8");

require_once "db.php";
require_once "session.php";
start_portal_session();

function sendResponse($success, $message, $status = 200) {
    http_response_code($status);
    echo json_encode([
        "success" => $success,
        "message" => $message
    ]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendResponse(false, "Only POST requests are allowed.", 405);
}

if (!isset($_SESSION["user_role"]) ||
    $_SESSION["user_role"] !== "admin") {
    sendResponse(false, "Admin login required.", 403);
}

$data = json_decode(file_get_contents("php://input"), true);
$id = (int)($data["id"] ?? 0);

if ($id <= 0) {
    sendResponse(false, "Invalid event ID.", 400);
}

$stmt = mysqli_prepare(
    $conn,
    "SELECT poster FROM events WHERE id = ?"
);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$event = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$event) {
    mysqli_close($conn);
    sendResponse(false, "Event not found.", 404);
}

$stmt = mysqli_prepare($conn, "DELETE FROM events WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt) &&
    mysqli_stmt_affected_rows($stmt) === 1) {

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    if (!empty($event["poster"])) {
        $path = __DIR__ . "/uploads/" . basename($event["poster"]);

        if (is_file($path)) {
            unlink($path);
        }
    }

    sendResponse(true, "Event deleted successfully.");
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
sendResponse(false, "Could not delete the event.", 500);
?>

