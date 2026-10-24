
<?php
require_once __DIR__ . "/session.php";
start_portal_session();

require_once __DIR__ . "/db-auth.php";

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

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === "") {
    login_error();
}

$sql = "SELECT id, name, email, password_hash, role
        FROM students
        WHERE email = ?
        LIMIT 1";

$stmt = mysqli_prepare($conn_auth, $sql);

if (!$stmt) {
    http_response_code(500);
    exit("Login query failed.");
}

mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$user || !password_verify($password, $user["password_hash"])) {
    mysqli_close($conn_auth);
    login_error();
}

session_regenerate_id(true);

$_SESSION["user_id"] = $user["id"];
$_SESSION["user_name"] = $user["name"];
$_SESSION["user_email"] = $user["email"];
$_SESSION["user_role"] = $user["role"];
$_SESSION["last_activity"] = time();

mysqli_close($conn_auth);

if ($user["role"] === "admin") {
    header("Location: admin-dashboard.php");
} else {
    header("Location: dashboard.php");
}

exit;
?>
