
<?php
require_once __DIR__ . "/session.php";
start_portal_session();
require_once __DIR__ . "/db.php";

if (isset($_SESSION["user_id"]) &&
    isset($_COOKIE["remember_token"])) {

    $tokenHash = hash("sha256", $_COOKIE["remember_token"]);

    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM remember_tokens WHERE token_hash = ?"
    );

    mysqli_stmt_bind_param($stmt, "s", $tokenHash);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

setcookie("remember_token", "", [
    "expires" => time() - 3600,
    "path" => "/practical10/",
    "httponly" => true,
    "secure" => !empty($_SERVER["HTTPS"]) &&
                $_SERVER["HTTPS"] !== "off",
    "samesite" => "Lax"
]);

$_SESSION = [];
session_destroy();
mysqli_close($conn);

header("Location: login.html");
exit;
?>
