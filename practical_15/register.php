
<?php
require_once __DIR__ . "/session.php";
start_portal_session();

require_once __DIR__ . "/db.php";

function show_message($title, $message, $status)
{
    http_response_code($status);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title><?php echo htmlspecialchars($title); ?></title>
        <link rel="stylesheet" href="register.css">
    </head>
    <body>
        <main class="result">
            <h1><?php echo htmlspecialchars($title); ?></h1>
            <p><?php echo htmlspecialchars($message); ?></p>
            <a href="register.php">Register another student</a>
            <br><br>
            <a href="index.html">Home</a>
        </main>
    </body>
    </html>
    <?php
    exit;
}

// Accept only form submissions
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    show_message("Invalid Request", "Please submit the registration form.", 405);
}

// Check CSRF token
$token = $_POST["csrf_token"] ?? "";
$sessionToken = $_SESSION["csrf_token"] ?? "";

if (!is_string($token) || $sessionToken === "" ||
    !hash_equals($sessionToken, $token)) {
    show_message("Security Error", "Reload the registration page and try again.", 403);
}

// Get form values
$name = trim($_POST["name"] ?? "");
$email = strtolower(trim($_POST["email"] ?? ""));
$mobile = trim($_POST["mobile"] ?? "");
$address = trim($_POST["address"] ?? "");
$password = $_POST["password"] ?? "";
$confirm = $_POST["confirm"] ?? "";
$course = trim($_POST["course"] ?? "");
$year = trim($_POST["year"] ?? "");
$gender = trim($_POST["gender"] ?? "");
$terms = $_POST["terms"] ?? "";

// Check required fields
if (!is_string($name) || !is_string($email) ||
    !is_string($mobile) || !is_string($address) ||
    !is_string($password) || !is_string($confirm) ||
    !is_string($course) || !is_string($year) ||
    !is_string($gender) || !is_string($terms)) {
    show_message("Invalid Input", "Please enter valid form details.", 422);
}

if ($name === "" || $email === "" || $mobile === "" ||
    $address === "" || $password === "" || $confirm === "" ||
    $course === "" || $year === "" || $gender === "") {
    show_message("Missing Details", "Please fill in all required fields.", 422);
}

// Validate name
if (preg_match("/^[\p{L}][\p{L} .'-]{1,99}$/u", $name) !== 1) {
    show_message("Invalid Name", "Enter a valid name.", 422);
}

// Validate email
if (strlen($email) > 254 ||
    !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    show_message("Invalid Email", "Enter a valid email address.", 422);
}

// Validate mobile number
if (!preg_match("/^[0-9]{10}$/", $mobile)) {
    show_message("Invalid Mobile", "Mobile number must contain 10 digits.", 422);
}

// Validate address
if (strlen($address) > 4000 ||
    preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $address)) {
    show_message("Invalid Address", "Please enter a valid address.", 422);
}

// Validate password
if (strlen($password) < 8 || strlen($password) > 4096) {
    show_message("Invalid Password", "Password must contain at least 8 characters.", 422);
}

// Check password confirmation
if (!hash_equals($password, $confirm)) {
    show_message("Password Error", "Passwords do not match.", 422);
}

// Validate course, year and gender
if (!in_array($course, ["BCA", "BSc", "BCom", "BA"], true)) {
    show_message("Invalid Course", "Please select a valid course.", 422);
}

if (!in_array($year, ["1", "2", "3", "4"], true)) {
    show_message("Invalid Year", "Please select a valid year.", 422);
}

if (!in_array($gender, ["female", "male", "other"], true)) {
    show_message("Invalid Gender", "Please select a valid gender.", 422);
}

if ($terms !== "yes") {
    show_message("Terms Required", "Please accept the terms.", 422);
}

// Check whether email already exists
$sql = "SELECT id FROM students WHERE email = ? LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    show_message("Database Error", "Could not check the email.", 500);
}

mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {
    mysqli_stmt_close($stmt);
    show_message("Duplicate Email", "This email is already registered.", 409);
}

mysqli_stmt_close($stmt);

// Hash password before saving
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

if ($passwordHash === false) {
    show_message("Error", "Could not secure the password.", 500);
}

// Save student details in MySQL
$sql = "INSERT INTO students
(name, email, mobile, address, password_hash, course, year, gender)
VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    show_message("Database Error", "Could not save student details.", 500);
}

$yearNumber = (int)$year;

mysqli_stmt_bind_param(
    $stmt,
    "ssssssis",
    $name,
    $email,
    $mobile,
    $address,
    $passwordHash,
    $course,
    $yearNumber,
    $gender
);

if (mysqli_stmt_execute($stmt)) {
    unset($_SESSION["csrf_token"]);

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    show_message(
        "Registration Successful",
        "Student details have been saved in the MySQL database.",
        201
    );
} else {
    $errorNumber = mysqli_stmt_errno($stmt);

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    if ($errorNumber === 1062) {
        show_message("Duplicate Email", "This email is already registered.", 409);
    }

    show_message("Registration Failed", "Please try again.", 500);
}
?>
