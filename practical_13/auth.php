
<?php
require_once __DIR__ . "/session.php";
start_portal_session();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit;
}

if (isset($_SESSION["last_activity"]) &&
    time() - $_SESSION["last_activity"] > 900) {

    $_SESSION = [];
    session_destroy();

    header("Location: login.html?timeout=1");
    exit;
}

$_SESSION["last_activity"] = time();
?>
