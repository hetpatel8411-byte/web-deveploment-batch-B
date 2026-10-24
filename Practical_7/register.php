<?php
require_once __DIR__ . "/session.php";

function registration_response($title, $messages, $status)
{
    http_response_code($status);
    header("Content-Type: text/html; charset=UTF-8");
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title><?= escape_html($title) ?> - StudentHub</title>
      <link rel="stylesheet" href="register.css">
    </head>
    <body>
      <main class="result">
        <h1><?= escape_html($title) ?></h1>
        <?php foreach ($messages as $message): ?>
          <p><?= escape_html($message) ?></p>
        <?php endforeach; ?>
        <nav aria-label="Registration links">
          <a href="register.html">Register another student</a>
          <a href="index.html">Home</a>
        </nav>
      </main>
      <?php if ($status === 201): ?>
        <script>
          window.alert("Registration successful!");
          window.location.replace("register.html");
        </script>
      <?php endif; ?>
    </body>
    </html>
    <?php
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    header("Allow: POST");
    exit("Submit the registration form using POST.");
}

start_portal_session();
$submittedToken = isset($_POST["csrf_token"]) && is_string($_POST["csrf_token"])
    ? $_POST["csrf_token"]
    : "";
$sessionToken = isset($_SESSION["csrf_token"]) ? $_SESSION["csrf_token"] : "";
if ($sessionToken === "" || !hash_equals($sessionToken, $submittedToken)) {
    registration_response("Registration could not be completed", [
        "Your form session expired or could not be verified. Reload the registration page and try again."
    ], 403);
}

function post_string($key)
{
    return isset($_POST[$key]) && is_string($_POST[$key]) ? trim($_POST[$key]) : "";
}

$name = post_string("name");
$email = strtolower(post_string("email"));
$mobile = post_string("mobile");
$address = post_string("address");
$password = isset($_POST["password"]) && is_string($_POST["password"]) ? $_POST["password"] : "";
$confirm = isset($_POST["confirm"]) && is_string($_POST["confirm"]) ? $_POST["confirm"] : "";
$course = post_string("course");
$year = post_string("year");
$gender = post_string("gender");
$terms = isset($_POST["terms"]) && $_POST["terms"] === "yes";

$errors = [];
if (preg_match("/^[\\p{L}][\\p{L} .'-]{1,99}$/u", $name) !== 1) {
    $errors[] = "Enter a name between 2 and 100 characters, using letters, spaces, apostrophes, periods, or hyphens.";
}
if (strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Enter a valid email address.";
}
if (!preg_match("/^[0-9]{10}$/", $mobile)) {
    $errors[] = "Mobile number must contain exactly 10 digits.";
}
if (!preg_match("//u", $address)) {
    $errors[] = "Enter a valid address of no more than 1000 characters.";
} else {
    $address = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', "", $address);
    $addressLength = is_string($address)
        ? preg_match_all('/./us', $address, $addressCharacters)
        : false;
    if (!is_string($address) || $address === "" ||
        $addressLength === false || $addressLength > 1000) {
        $errors[] = "Enter a valid address of no more than 1000 characters.";
    }
}
if (strlen($password) < 8 || strlen($password) > 4096) {
    $errors[] = "Password must contain between 8 and 4096 characters.";
}
if (!hash_equals($password, $confirm)) {
    $errors[] = "Passwords do not match.";
}
if (!in_array($course, ["BCA", "BSc", "BCom", "BA"], true)) {
    $errors[] = "Choose a valid course.";
}
if (!in_array($year, ["1", "2", "3", "4"], true)) {
    $errors[] = "Choose a valid year.";
}
if (!in_array($gender, ["female", "male", "other"], true)) {
    $errors[] = "Choose a valid gender.";
}
if (!$terms) {
    $errors[] = "Please accept the terms.";
}

if ($errors) {
    registration_response("Please correct the following", $errors, 422);
}

$folder = __DIR__ . DIRECTORY_SEPARATOR . "data";
if (!is_dir($folder) && !mkdir($folder, 0750, true) && !is_dir($folder)) {
    registration_response("Registration could not be saved", [
        "The data folder could not be created. Check the server folder permissions."
    ], 500);
}
if (!is_writable($folder)) {
    registration_response("Registration could not be saved", [
        "The data folder is not writable. Check the server folder permissions."
    ], 500);
}

$passwordHash = password_hash($password, PASSWORD_DEFAULT);
if ($passwordHash === false) {
    registration_response("Registration could not be saved", [
        "The password could not be processed. Please try again."
    ], 500);
}
$record = [
    "name" => $name,
    "email" => $email,
    "mobile" => $mobile,
    "address" => $address,
    "password_hash" => $passwordHash,
    "course" => $course,
    "year" => $year,
    "gender" => $gender
];

$csvFile = $folder . DIRECTORY_SEPARATOR . "students.csv";
$csv = fopen($csvFile, "c+");
if ($csv === false || !flock($csv, LOCK_EX)) {
    if (is_resource($csv)) {
        fclose($csv);
    }
    registration_response("Registration could not be saved", [
        "The spreadsheet could not be opened for a safe update. Check the data folder permissions."
    ], 500);
}

$fileStats = fstat($csv);
$csvSize = $fileStats === false ? false : $fileStats["size"];
$headerWritten = $csvSize !== false && (
    $csvSize !== 0 ||
    fputcsv($csv, ["Name", "Email", "Mobile", "Address", "Password Hash", "Course", "Year", "Gender"], ",", '"', "") !== false
);
$written = false;
if ($headerWritten) {
    if (fseek($csv, 0, SEEK_END) === 0) {
        $csvRecord = array_values($record);
        foreach ($csvRecord as $index => $value) {
            if (is_string($value) && preg_match('/^[\s]*[=+\-@]/', $value)) {
                $csvRecord[$index] = "'" . $value;
            }
        }
        $written = fputcsv($csv, $csvRecord, ",", '"', "");
    }
}
$flushSucceeded = fflush($csv);
flock($csv, LOCK_UN);
fclose($csv);

if (!$headerWritten || $written === false || !$flushSucceeded) {
    registration_response("Registration could not be saved", [
        "The spreadsheet row could not be written. Check the data folder permissions and try again."
    ], 500);
}

unset($_SESSION["csrf_token"]);
registration_response("Registration successful", [
    "Your information was saved to data/students.csv. Passwords are stored only as a one-way hash."
], 201);
