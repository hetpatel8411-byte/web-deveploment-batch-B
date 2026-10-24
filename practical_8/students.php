<?php
require_once __DIR__ . "/db.php";

header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: no-store");

try {
    $statement = get_db_connection()->query(
        "SELECT student_id, name FROM students ORDER BY name, student_id"
    );
    $json = json_encode($statement->fetchAll(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        error_log("StudentHub student response could not be encoded: " . json_last_error_msg());
        http_response_code(500);
        echo '{"error":"Students could not be loaded."}';
        exit;
    }

    echo $json;
} catch (PDOException $exception) {
    error_log("StudentHub student query failed: " . $exception->getMessage());
    http_response_code(503);
    echo '{"error":"Students could not be loaded. Check the MySQL connection and database setup."}';
}
