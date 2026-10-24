
<?php
header("Content-Type: application/json; charset=UTF-8");

require_once "db.php";
require_once "session.php";
start_portal_session();

if (!isset($_SESSION["user_role"]) ||
    $_SESSION["user_role"] !== "admin") {
    http_response_code(403);
    echo json_encode([
        "success" => false,
        "message" => "Admin login required."
    ]);
    exit;
}

$id = (int)($_GET["id"] ?? 0);

if ($id <= 0) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid event ID."
    ]);
    exit;
}

$stmt = mysqli_prepare(
    $conn,
    "SELECT id, title, description, event_date,
            location, category, status
     FROM events WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$event = mysqli_fetch_assoc($result);

if (!$event) {
    http_response_code(404);
    echo json_encode([
        "success" => false,
        "message" => "Event not found."
    ]);
} else {
    echo json_encode([
        "success" => true,
        "data" => $event
    ]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
