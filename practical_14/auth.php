
<?php
require_once __DIR__ . "/session.php";

start_portal_session();

function require_login()
{
    if (!isset($_SESSION["user_id"])) {
        header("Location: login.html");
        exit;
    }
}

function require_admin()
{
    require_login();

    if (($_SESSION["user_role"] ?? "") !== "admin") {
        http_response_code(403);
        exit("Access denied. Admins only.");
    }
}
?>
