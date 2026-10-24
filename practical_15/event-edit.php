
<?php
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/auth.php";

if (!isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== "admin") {
    die("Access denied.");
}

$id = (int)($_GET["id"] ?? 0);
if ($id <= 0) {
    die("Invalid event ID.");
}

$stmt = mysqli_prepare($conn, "SELECT * FROM events WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$event = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$event) {
    die("Event not found.");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $date = $_POST["event_date"] ?? "";
    $location = trim($_POST["location"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $status = $_POST["status"] ?? "";
    $poster = $event["poster"];
    $newPoster = null;

    if ($title === "" || $description === "" || $date === "" ||
        $location === "" || $category === "" ||
        !in_array($status, ["open", "closed"], true)) {
        $message = "Please fill in all fields correctly.";
    } else {
        if (isset($_FILES["poster"]) &&
            $_FILES["poster"]["error"] !== UPLOAD_ERR_NO_FILE) {

            if ($_FILES["poster"]["error"] !== UPLOAD_ERR_OK) {
                $message = "Poster upload failed.";
            } elseif ($_FILES["poster"]["size"] > 2 * 1024 * 1024) {
                $message = "Poster must be 2 MB or smaller.";
            } else {
                $tmp = $_FILES["poster"]["tmp_name"];
                $info = getimagesize($tmp);
                $mime = $info["mime"] ?? "";

                $types = [
                    "image/jpeg" => "jpg",
                    "image/png" => "png",
                    "image/gif" => "gif"
                ];

                if ($info === false || !isset($types[$mime])) {
                    $message = "Only JPG, PNG, and GIF images are allowed.";
                } else {
                    $newPoster = bin2hex(random_bytes(16)) . "." . $types[$mime];

                    if (move_uploaded_file($tmp, __DIR__ . "/uploads/" . $newPoster)) {
                        $poster = $newPoster;
                    } else {
                        $newPoster = null;
                        $message = "Could not save the new poster.";
                    }
                }
            }
        }

        if ($message === "") {
            $sql = "UPDATE events
                    SET title=?, description=?, event_date=?,
                        location=?, category=?, status=?, poster=?
                    WHERE id=?";

            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param(
                $stmt, "sssssssi",
                $title, $description, $date, $location,
                $category, $status, $poster, $id
            );

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);

                if ($newPoster !== null && !empty($event["poster"])) {
                    $oldPath = __DIR__ . "/uploads/" . basename($event["poster"]);
                    if (is_file($oldPath)) {
                        unlink($oldPath);
                    }
                }

                mysqli_close($conn);
                header("Location: events.php?updated=1");
                exit;
            } else {
                $message = "Could not update event.";
                if ($newPoster !== null) {
                    @unlink(__DIR__ . "/uploads/" . $newPoster);
                }
            }

            if (isset($stmt) && $stmt instanceof mysqli_stmt) {
                mysqli_stmt_close($stmt);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Event</title>
    <style>
        body { font-family: Arial; margin: 30px; background: #f4f4f4; }
        input, textarea, select, button {
            display: block; width: 320px; padding: 9px;
            margin: 10px 0; box-sizing: border-box;
        }
        textarea { height: 100px; }
        img { width: 150px; display: block; margin: 10px 0; }
        .message { color: red; }
    </style>
</head>
<body>

<h2>Edit Event</h2>
<p class="message"><?php echo htmlspecialchars($message); ?></p>

<form method="POST" enctype="multipart/form-data">
    <label>Event Title</label>
    <input name="title" required value="<?php echo htmlspecialchars($event["title"]); ?>">

    <label>Description</label>
    <textarea name="description" required><?php echo htmlspecialchars($event["description"]); ?></textarea>

    <label>Event Date</label>
    <input type="date" name="event_date" required value="<?php echo htmlspecialchars($event["event_date"]); ?>">

    <label>Location</label>
    <input name="location" required value="<?php echo htmlspecialchars($event["location"]); ?>">

    <label>Category</label>
    <input name="category" required value="<?php echo htmlspecialchars($event["category"]); ?>">

    <label>Status</label>
    <select name="status">
        <option value="open" <?php if ($event["status"] === "open") echo "selected"; ?>>Open</option>
        <option value="closed" <?php if ($event["status"] === "closed") echo "selected"; ?>>Closed</option>
    </select>

    <label>Current Poster</label>
    <?php if (!empty($event["poster"])) { ?>
        <img src="uploads/<?php echo rawurlencode(basename($event["poster"])); ?>" alt="Current poster">
    <?php } else { ?>
        <p>No poster uploaded.</p>
    <?php } ?>

    <label>Choose a new poster (optional, max 2 MB)</label>
    <input type="file" name="poster" accept=".jpg,.jpeg,.png,.gif,image/jpeg,image/png,image/gif">

    <button type="submit">Update Event</button>
</form>

<p><a href="events.php">Back to Events</a></p>

</body>
</html>
