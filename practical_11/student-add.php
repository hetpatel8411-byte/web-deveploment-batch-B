
<?php
require_once "db.php";
require_once "auth.php";

if (!isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== "admin") {
    die("Access denied.");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = strtolower(trim($_POST["email"] ?? ""));
    $mobile = trim($_POST["mobile"] ?? "");
    $address = trim($_POST["address"] ?? "");
    $password = $_POST["password"] ?? "";
    $course = $_POST["course"] ?? "";
    $year = (int)($_POST["year"] ?? 0);
    $gender = $_POST["gender"] ?? "";

    if ($name === "" || !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        $mobile === "" || $address === "" || $password === "" ||
        !in_array($course, ["BCA", "BBA", "BTECH", "MCA"], true) ||
        $year < 1 || $year > 4 ||
        !in_array($gender, ["Male", "Female", "Other"], true)) {
        $message = "Please enter valid details in every field.";
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO students
        (name, email, mobile, address, password_hash, course, year, gender)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param(
            $stmt, "ssssssis",
            $name, $email, $mobile, $address,
            $password_hash, $course, $year, $gender
        );

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            mysqli_close($conn);
            header("Location: students.php?added=1");
            exit;
        } else {
            $message = "Could not add student. The email may already exist.";
        }

        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Student</title>
    <style>
        body {
            font-family: Arial;
            background: #f4f4f4;
            margin: 30px;
        }
        input, select, textarea, button {
            display: block;
            padding: 9px;
            margin: 10px 0;
            width: 300px;
            box-sizing: border-box;
        }
        textarea { height: 70px; }
        .message { color: red; }
    </style>
</head>
<body>

<h2>Add New Student</h2>

<p class="message"><?php echo htmlspecialchars($message); ?></p>

<form method="POST">
    <input name="name" placeholder="Full Name" required>
    <input type="email" name="email" placeholder="Email" required>
    <input name="mobile" placeholder="Mobile Number" required>
    <textarea name="address" placeholder="Address" required></textarea>
    <input type="password" name="password" placeholder="Password" required>

    <select name="course" required>
        <option value="">Select Course</option>
        <option value="BCA">BCA</option>
        <option value="BBA">BBA</option>
        <option value="BTECH">BTECH</option>
        <option value="MCA">MCA</option>
    </select>

    <select name="year" required>
        <option value="">Select Year</option>
        <option value="1">Year 1</option>
        <option value="2">Year 2</option>
        <option value="3">Year 3</option>
        <option value="4">Year 4</option>
    </select>

    <select name="gender" required>
        <option value="">Select Gender</option>
        <option value="Male">Male</option>
        <option value="Female">Female</option>
        <option value="Other">Other</option>
    </select>

    <button type="submit">Add Student</button>
</form>

<p><a href="students.php">Back to Student List</a></p>

</body>
</html>

