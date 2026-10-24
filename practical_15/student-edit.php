
<?php
require_once "db.php";
require_once "auth.php";

if (!isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== "admin") {
    die("Access denied.");
}

$id = (int)($_GET["id"] ?? 0);
$message = "";

if ($id <= 0) {
    die("Invalid student ID.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = strtolower(trim($_POST["email"] ?? ""));
    $mobile = trim($_POST["mobile"] ?? "");
    $address = trim($_POST["address"] ?? "");
    $course = $_POST["course"] ?? "";
    $year = (int)($_POST["year"] ?? 0);
    $gender = $_POST["gender"] ?? "";

    if ($name === "" || !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        $mobile === "" || $address === "" ||
        !in_array($course, ["BCA", "BBA", "BTECH", "MCA"], true) ||
        $year < 1 || $year > 4 ||
        !in_array($gender, ["Male", "Female", "Other"], true)) {
        $message = "Please enter valid details.";
    } else {
        $sql = "UPDATE students
                SET name=?, email=?, mobile=?, address=?,
                    course=?, year=?, gender=?
                WHERE id=?";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param(
            $stmt, "sssssisi",
            $name, $email, $mobile, $address,
            $course, $year, $gender, $id
        );

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            mysqli_close($conn);
            header("Location: students.php?updated=1");
            exit;
        } else {
            $message = "Could not update student. The email may already exist.";
        }

        mysqli_stmt_close($stmt);
    }
}

$stmt = mysqli_prepare(
    $conn,
    "SELECT name, email, mobile, address, course, year, gender
     FROM students WHERE id=?"
);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$student) {
    die("Student not found.");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Student</title>
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

<h2>Edit Student</h2>

<p class="message"><?php echo htmlspecialchars($message); ?></p>

<form method="POST">
    <input name="name" value="<?php echo htmlspecialchars($student["name"]); ?>" required>
    <input type="email" name="email" value="<?php echo htmlspecialchars($student["email"]); ?>" required>
    <input name="mobile" value="<?php echo htmlspecialchars($student["mobile"]); ?>" required>
    <textarea name="address" required><?php echo htmlspecialchars($student["address"]); ?></textarea>

    <select name="course" required>
        <?php foreach (["BCA", "BBA", "BTECH", "MCA"] as $c) { ?>
            <option value="<?php echo $c; ?>"
                <?php if ($student["course"] === $c) echo "selected"; ?>>
                <?php echo $c; ?>
            </option>
        <?php } ?>
    </select>

    <select name="year" required>
        <?php for ($y = 1; $y <= 4; $y++) { ?>
            <option value="<?php echo $y; ?>"
                <?php if ((int)$student["year"] === $y) echo "selected"; ?>>
                Year <?php echo $y; ?>
            </option>
        <?php } ?>
    </select>

    <select name="gender" required>
        <?php foreach (["Male", "Female", "Other"] as $g) { ?>
            <option value="<?php echo $g; ?>"
                <?php if ($student["gender"] === $g) echo "selected"; ?>>
                <?php echo $g; ?>
            </option>
        <?php } ?>
    </select>

    <button type="submit">Update Student</button>
</form>

<p><a href="students.php">Back to Student List</a></p>

</body>
</html>

