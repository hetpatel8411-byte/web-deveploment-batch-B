
<?php
require_once __DIR__ . "/session.php";
start_portal_session();
require_once __DIR__ . "/db.php";

function login_error()
{
    echo "<h2>Login Failed</h2>";
    echo "<p>Invalid email or password.</p>";
    echo '<a href="login.html">Try again</a>';
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.html");
    exit;
}

$email = $_POST["email"] ?? "";
$password = $_POST["password"] ?? "";

if (!is_string($email) || !is_string($password)) {
    login_error();
}

$email = strtolower(trim($email));

if (!filter_var($email, FILTER_VALIDATE_EMAIL) ||
    $password === "") {
    login_error();
}

$sql = "SELECT id, name, email, password_hash, role
        FROM students WHERE email = ? LIMIT 1";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$user || !password_verify($password, $user["password_hash"])) {
    login_error();
}

session_regenerate_id(true);

$_SESSION["user_id"] = $user["id"];
$_SESSION["user_name"] = $user["name"];
$_SESSION["user_email"] = $user["email"];
$_SESSION["user_role"] = $user["role"];
$_SESSION["last_activity"] = time();

if (isset($_POST["remember"]) && $_POST["remember"] === "yes") {
    $token = bin2hex(random_bytes(32));
    $tokenHash = hash("sha256", $token);
    $expires = date("Y-m-d H:i:s", time() + (30 * 24 * 60 * 60));

    $sql = "INSERT INTO remember_tokens
            (student_id, token_hash, expires_at)
            VALUES (?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param(
        $stmt, "iss", $user["id"], $tokenHash, $expires
    );

    if (mysqli_stmt_execute($stmt)) {
        setcookie("remember_token", $token, [
            "expires" => time() + (30 * 24 * 60 * 60),
            "path" => "/practical11/",
            "httponly" => true,
            "secure" => !empty($_SERVER["HTTPS"]) &&
                        $_SERVER["HTTPS"] !== "off",
            "samesite" => "Lax"
        ]);
    }

    mysqli_stmt_close($stmt);
}

mysqli_close($conn);

if ($user["role"] === "admin") {
    header("Location: admin-dashboard.php");
} else {
    header("Location: dashboard.php");
}
exit;
?>
