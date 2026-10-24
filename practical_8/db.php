<?php

function get_db_connection()
{
    $host = getenv("STUDENTHUB_DB_HOST");
    $database = getenv("STUDENTHUB_DB_NAME");
    $username = getenv("STUDENTHUB_DB_USER");
    $password = getenv("STUDENTHUB_DB_PASS");

    $host = $host === false || $host === "" ? "127.0.0.1" : $host;
    $database = $database === false || $database === "" ? "studenthub" : $database;
    $username = $username === false || $username === "" ? "root" : $username;
    $password = $password === false ? "" : $password;

    $dsn = "mysql:host=" . $host . ";dbname=" . $database . ";charset=utf8mb4";
    return new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
}
