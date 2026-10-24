<?php
require_once __DIR__ . "/session.php";
require_once __DIR__ . "/db.php";

header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: no-store");

function event_registration_response($message, $status)
{
    http_response_code($status);
    echo json_encode(["message" => $message], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Allow: POST");
    event_registration_response("Submit the event registration form using POST.", 405);
}

start_portal_session();
$submittedToken = isset($_POST["csrf_token"]) && is_string($_POST["csrf_token"])
    ? $_POST["csrf_token"]
    : "";
$sessionToken = isset($_SESSION["csrf_token"]) ? $_SESSION["csrf_token"] : "";
if ($sessionToken === "" || !hash_equals($sessionToken, $submittedToken)) {
    event_registration_response("Your form session expired. Reload the page and try again.", 403);
}

$studentId = isset($_POST["student_id"]) && is_string($_POST["student_id"])
    ? filter_var($_POST["student_id"], FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]])
    : false;
$eventId = isset($_POST["event_id"]) && is_string($_POST["event_id"])
    ? filter_var($_POST["event_id"], FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]])
    : false;

if ($studentId === false || $eventId === false) {
    event_registration_response("Choose a valid student and event.", 422);
}

try {
    $pdo = get_db_connection();
    $studentQuery = $pdo->prepare("SELECT student_id FROM students WHERE student_id = :student_id");
    $studentQuery->execute(["student_id" => $studentId]);
    if ($studentQuery->fetchColumn() === false) {
        event_registration_response("That student could not be found. Choose a student from the list.", 422);
    }

    $eventQuery = $pdo->prepare(
        "SELECT event_id FROM events WHERE event_id = :event_id AND event_date >= CURDATE()"
    );
    $eventQuery->execute(["event_id" => $eventId]);
    if ($eventQuery->fetchColumn() === false) {
        event_registration_response("That event is unavailable or has already passed. Choose an upcoming event.", 422);
    }

    $insert = $pdo->prepare(
        "INSERT INTO registrations (student_id, event_id, status)
         VALUES (:student_id, :event_id, 'registered')"
    );
    $insert->execute([
        "student_id" => $studentId,
        "event_id" => $eventId
    ]);
} catch (PDOException $exception) {
    if (isset($exception->errorInfo[1]) && (int) $exception->errorInfo[1] === 1062) {
        event_registration_response("You are already registered for this event.", 409);
    }

    error_log("StudentHub event registration failed: " . $exception->getMessage());
    event_registration_response("The registration could not be saved. Check that MySQL is running and studenthub.sql has been imported.", 503);
}

event_registration_response("You're registered! Your place at the event has been saved.", 201);
