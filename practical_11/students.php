
<?php
require_once "db.php";
require_once "auth.php";

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION["user_role"]) ||
    $_SESSION["user_role"] !== "admin") {
    die("Access denied. Please log in as an admin.");
}
$search = trim($_GET["search"] ?? "");
$course = trim($_GET["course"] ?? "");

$sql = "SELECT id, name, email, mobile, course, year, gender, role
        FROM students WHERE 1=1";

if ($search != "") {
    $sql .= " AND (name LIKE ? OR email LIKE ?)";
}

if ($course != "") {
    $sql .= " AND course = ?";
}

$sql .= " ORDER BY id DESC";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Could not load students.");
}

if ($search != "" && $course != "") {
    $term = "%" . $search . "%";
    mysqli_stmt_bind_param($stmt, "sss", $term, $term, $course);
} elseif ($search != "") {
    $term = "%" . $search . "%";
    mysqli_stmt_bind_param($stmt, "ss", $term, $term);
} elseif ($course != "") {
    mysqli_stmt_bind_param($stmt, "s", $course);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Management</title>
    <style>
        body {
            font-family: Arial;
            margin: 30px;
            background: #f4f4f4;
        }

        h2 {
            color: #222;
        }

        form {
            margin-bottom: 20px;
        }

        input, select, button, a {
            padding: 8px;
            margin: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }

        th {
            background: #ddd;
        }
    </style>
</head>
<body>

<h2>Student Management</h2>

<?php
if (isset($_GET["added"])) {
    echo "<p style='color:green;'>Student added successfully!</p>";
}

if (isset($_GET["updated"])) {
    echo "<p style='color:green;'>Student updated successfully!</p>";
}

if (isset($_GET["deleted"])) {
    echo "<p style='color:green;'>Student deleted successfully!</p>";
}
?>

<form method="GET">
    <input type="text" name="search"
           placeholder="Search name or email"
           value="<?php echo htmlspecialchars($search); ?>">

    <select name="course">
        <option value="">All Courses</option>
        <option value="BCA" <?php if ($course == "BCA") echo "selected"; ?>>BCA</option>
        <option value="BBA" <?php if ($course == "BBA") echo "selected"; ?>>BBA</option>
        <option value="BTECH" <?php if ($course == "BTECH") echo "selected"; ?>>BTECH</option>
        <option value="MCA" <?php if ($course == "MCA") echo "selected"; ?>>MCA</option>
    </select>

    <button type="submit">Search / Filter</button>
    <a href="students.php">Clear</a>
</form>

<p><a href="student-add.php">Add New Student</a></p>

<table>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Mobile</th>
        <th>Course</th>
        <th>Year</th>
        <th>Gender</th>
        <th>Role</th>
        <th>Actions</th>
    </tr>

    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
    <tr>
        <td><?php echo (int)$row["id"]; ?></td>
        <td><?php echo htmlspecialchars($row["name"]); ?></td>
        <td><?php echo htmlspecialchars($row["email"]); ?></td>
        <td><?php echo htmlspecialchars($row["mobile"]); ?></td>
        <td><?php echo htmlspecialchars($row["course"]); ?></td>
        <td><?php echo (int)$row["year"]; ?></td>
        <td><?php echo htmlspecialchars($row["gender"]); ?></td>
        <td><?php echo htmlspecialchars($row["role"]); ?></td>
        <td>
            <a href="student-edit.php?id=<?php echo (int)$row["id"]; ?>">Edit</a> |
            <a href="student-delete.php?id=<?php echo (int)$row["id"]; ?>">Delete</a>
        </td>
    </tr>
    <?php } ?>
</table>

</body>
</html>

<?php
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
