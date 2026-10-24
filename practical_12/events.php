
<?php
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/auth.php";

if (!isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== "admin") {
    die("Access denied. Please log in as an admin.");
}

$search = trim($_GET["search"] ?? "");
$status = $_GET["status"] ?? "";

$sql = "SELECT id, title, description, event_date, location,
               category, status, poster
        FROM events WHERE 1=1";

if ($search !== "") {
    $sql .= " AND (title LIKE ? OR location LIKE ?)";
}

if (in_array($status, ["open", "closed"], true)) {
    $sql .= " AND status = ?";
} else {
    $status = "";
}

$sql .= " ORDER BY event_date DESC";

$stmt = mysqli_prepare($conn, $sql);

if ($search !== "" && $status !== "") {
    $term = "%" . $search . "%";
    mysqli_stmt_bind_param($stmt, "sss", $term, $term, $status);
} elseif ($search !== "") {
    $term = "%" . $search . "%";
    mysqli_stmt_bind_param($stmt, "ss", $term, $term);
} elseif ($status !== "") {
    mysqli_stmt_bind_param($stmt, "s", $status);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Event Management</title>
    <style>
        body {
            font-family: Arial;
            background: #f4f4f4;
            margin: 30px;
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
        th { background: #ddd; }
        input, select, button { padding: 9px; margin: 5px; }
        img { width: 90px; }
    </style>
</head>
<body>

<h2>Event Management</h2>

<?php if (isset($_GET["added"])) { ?>
    <p style="color:green;">Event added successfully!</p>
<?php } ?>

<?php if (isset($_GET["updated"])) { ?>
    <p style="color:green;">Event updated successfully!</p>
<?php } ?>

<?php if (isset($_GET["deleted"])) { ?>
    <p style="color:green;">Event deleted successfully!</p>
<?php } ?>

<form method="GET">
    <input name="search" placeholder="Search title or location"
           value="<?php echo htmlspecialchars($search); ?>">

    <select name="status">
        <option value="">All Statuses</option>
        <option value="open" <?php if ($status === "open") echo "selected"; ?>>Open</option>
        <option value="closed" <?php if ($status === "closed") echo "selected"; ?>>Closed</option>
    </select>

    <button type="submit">Search / Filter</button>
    <a href="events.php">Clear</a>
</form>

<p><a href="event-add.php">Add New Event</a></p>

<table>
    <tr>
        <th>ID</th>
        <th>Title</th>
        <th>Date</th>
        <th>Location</th>
        <th>Category</th>
        <th>Status</th>
        <th>Poster</th>
        <th>Actions</th>
    </tr>

    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
    <tr>
        <td><?php echo (int)$row["id"]; ?></td>
        <td><?php echo htmlspecialchars($row["title"]); ?></td>
        <td><?php echo htmlspecialchars($row["event_date"]); ?></td>
        <td><?php echo htmlspecialchars($row["location"]); ?></td>
        <td><?php echo htmlspecialchars($row["category"]); ?></td>
        <td><?php echo htmlspecialchars($row["status"]); ?></td>
        <td>
            <?php if (!empty($row["poster"])) { ?>
                <img src="uploads/<?php echo rawurlencode(basename($row["poster"])); ?>"
                     alt="Event poster">
            <?php } else { ?>
                No poster
            <?php } ?>
        </td>
        
    <td>
        <a href="event-edit.php?id=<?php echo (int)$row["id"]; ?>">Edit</a> |

        <form action="event-delete.php" method="POST"
            onsubmit="return confirm('Are you sure you want to delete this event?');"
             style="display:inline;">

            <input type="hidden" name="id"
                 value="<?php echo (int)$row["id"]; ?>">

            <button type="submit">Delete</button>
        </form>
    </td>

    </tr>
    <?php } ?>
</table>

<?php
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>

</body>
</html>
