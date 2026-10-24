
<?php
require_once "db.php";
require_once "auth.php";

if (!isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== "admin") {
    die("Access denied.");
}

$id = (int)($_GET["id"] ?? 0);

if ($id <= 0) {
    die("Invalid student ID.");
}

$stmt = mysqli_prepare(
    $conn,
    "SELECT id, name, email FROM students WHERE id = ?"
);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$student) {
    die("Student not found.");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (($_POST["confirm"] ?? "") === "yes") {
        $stmt = mysqli_prepare($conn, "DELETE FROM students WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            mysqli_close($conn);
            header("Location: students.php?deleted=1");
            exit;
        } else {
            $message = "Could not delete this student.";
        }

        mysqli_stmt_close($stmt);
    } else {
        header("Location: students.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Delete Student</title>
    <style>
        body {
            font-family: Arial;
            margin: 30px;
            background: #f4f4f4;
        }
        .box {
            background: white;
            padding: 20px;
            max-width: 450px;
            border: 1px solid #ccc;
        }
        button, a {
            padding: 10px;
            margin-right: 10px;
        }
        .delete {
            background: #c62828;
            color: white;
            border: none;
            cursor: pointer;
        }
        .message {
            color: red;
        }
    </style>
</head>
<body>

<div class="box">
    <h2>Delete Student</h2>

    <p class="message"><?php echo htmlspecialchars($message); ?></p>

    <p>Are you sure you want to delete this student?</p>

    <p><strong>Name:</strong>
        <?php echo htmlspecialchars($student["name"]); ?></p>

    <p><strong>Email:</strong>
        <?php echo htmlspecialchars($student["email"]); ?></p>

    <form method="POST"
          onsubmit="return confirm('Are you sure you want to permanently delete this student?');">
        <button class="delete" type="submit" name="confirm" value="yes">
            Yes, Delete
        </button>

        <button type="submit" name="confirm" value="no">
            Cancel
        </button>
    </form>

    <p><a href="students.php">Back to Student List</a></p>
</div>

</body>
</html>
