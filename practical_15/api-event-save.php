
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

if (!is_array($data)) {
    sendResponse(false, "Invalid JSON data.", 400);
}

$id = (int)($data["id"] ?? 0);
$title = trim($data["title"] ?? "");
$description = trim($data["description"] ?? "");
$date = trim($data["event_date"] ?? "");
$location = trim($data["location"] ?? "");
$category = trim($data["category"] ?? "");
$status = $data["status"] ?? "open";

if ($title === "" || $description === "" ||
    $date === "" || $location === "" ||
    $category === "") {
    sendResponse(false, "Please fill in all required fields.", 400);
}

$dateObject = DateTime::createFromFormat("!Y-m-d", $date);

if (!$dateObject || $dateObject->format("Y-m-d") !== $date) {
    sendResponse(false, "Invalid event date.", 400);
}

if (!in_array($status, ["open", "closed"], true)) {
    sendResponse(false, "Invalid event status.", 400);
}

if ($id > 0) {
    $sql = "UPDATE events SET title=?, description=?,
            event_date=?, location=?, category=?, status=?
            WHERE id=?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt, "ssssssi",
        $title, $description, $date,
        $location, $category, $status, $id
    );

    if (mysqli_stmt_execute($stmt)) {
        if (mysqli_stmt_affected_rows($stmt) >= 0) {
            mysqli_stmt_close($stmt);
            mysqli_close($conn);
            sendResponse(true, "Event updated successfully.");
        }
    }
} else {
    $sql = "INSERT INTO events
            (title, description, event_date, location, category, status)
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt, "ssssss",
        $title, $description, $date,
        $location, $category, $status
    );

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        sendResponse(true, "Event added successfully.", 201);
    }
}

if (isset($stmt) && $stmt) {
    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
sendResponse(false, "Could not save the event.", 500);
?>
