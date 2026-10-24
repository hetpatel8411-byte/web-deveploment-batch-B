<?php
require_once __DIR__ . "/db.php";

header("Content-Type: text/html; charset=UTF-8");
header("Cache-Control: no-store");

try {
    $pdo = get_db_connection();
    $statement = $pdo->prepare(
        "SELECT COUNT(*) FROM information_schema.tables
         WHERE table_schema = DATABASE()
           AND table_name IN (?, ?, ?, ?)"
    );
    $statement->execute(["programs", "students", "events", "registrations"]);
    $tableCount = (int) $statement->fetchColumn();

    if ($tableCount !== 4) {
        http_response_code(500);
        exit(
            "<h1>Database connection succeeded, but the schema is incomplete.</h1>" .
            "<p>Import studenthub.sql using phpMyAdmin, then reload this page.</p>"
        );
    }

    echo "<h1>StudentHub database connection successful</h1>";
    echo "<p>PDO connected to the studenthub database and found the required tables.</p>";
} catch (PDOException $exception) {
    error_log("StudentHub database connection test failed: " . $exception->getMessage());
    http_response_code(503);
    echo "<h1>StudentHub database connection failed</h1>";
    echo "<p>Start MySQL in XAMPP and import studenthub.sql using phpMyAdmin.</p>";
}
