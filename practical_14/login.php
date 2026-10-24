
<?php
require_once __DIR__ . "/session.php";
start_portal_session();

require_once __DIR__ . "/db.php";
require_once __DIR__ . "/audit-helper.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.html");
    exit;
}

$email = strtolower(trim($_POST["email"] ?? ""));
$password = $_POST["password"] ?? "";

if (!is_string($email) || !is_string($password) ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) || $password === "") {
    exit("Invalid email or password.");
}

$sql = "SELECT id, name, email, password_hash, role, status
        FROM students WHERE email = ? LIMIT 1";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$user ||
    !password_verify($password, $user["password_hash"]) ||
    $user["status"] !== "active") {
    exit("admin are not suppose to access in student portal");
}

session_regenerate_id(true);

$_SESSION["user_id"] = $user["id"];
$_SESSION["user_name"] = $user["name"];
$_SESSION["user_email"] = $user["email"];
$_SESSION["user_role"] = $user["role"];
logAction($conn, "login", "Successful login");

mysqli_close($conn);

if ($user["role"] === "admin") {
    header("Location: admin-dashboard.php");
} else {
    header("Location: student-dashboard.php");
}
exit;
?>
