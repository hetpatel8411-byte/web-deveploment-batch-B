
<?php
require_once __DIR__ . "/session.php";

start_portal_session();

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

header("Content-Type: application/json");
header("Cache-Control: no-store");

echo json_encode([
    "csrf_token" => $_SESSION["csrf_token"]
]);
?>
