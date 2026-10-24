<?php
require_once __DIR__ . "/session.php";

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    header("Allow: GET");
    exit("Method not allowed.");
}

start_portal_session();
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: no-store");
echo json_encode(["csrf_token" => $_SESSION["csrf_token"]]);
