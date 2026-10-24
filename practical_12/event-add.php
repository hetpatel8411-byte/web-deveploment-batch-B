
<?php
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/auth.php";

if (!isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== "admin") {
    die("Access denied. Please log in as an admin.");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $date = $_POST["event_date"] ?? "";
    $location = trim($_POST["location"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $status = $_POST["status"] ?? "open";
    $poster = null;

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
                    $filename = bin2hex(random_bytes(16)) . "." . $types[$mime];
                    $folder = __DIR__ . "/uploads/";

                    if (!is_dir($folder)) {
                        mkdir($folder, 0755, true);
                    }

                    if (move_uploaded_file($tmp, $folder . $filename)) {
                        $poster = $filename;
                    } else {
                        $message = "Could not save the poster.";
                    }
                }
            }
        }

        if ($message === "") {
            $sql = "INSERT INTO events
                    (title, description, event_date, location, category, status, poster)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";

            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param(
                $stmt, "sssssss",
                $title, $description, $date, $location,
                $category, $status, $poster
            );

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                mysqli_close($conn);
                header("Location: events.php?added=1");
                exit;
            } else {
                $message = "Could not add event.";
            }

            mysqli_stmt_close($stmt);

            if ($poster !== null && isset($filename)) {
                @unlink(__DIR__ . "/uploads/" . $filename);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Event</title>
    <style>
        body { font-family: Arial; margin: 30px; background: #f4f4f4; }
        input, textarea, select, button {
            display: block; width: 320px; padding: 9px;
            margin: 10px 0; box-sizing: border-box;
        }
        textarea { height: 100px; }
        .message { color: red; }
    </style>
</head>
<body>

<h2>Add New Event</h2>
<p class="message"><?php echo htmlspecialchars($message); ?></p>

<form method="POST" enctype="multipart/form-data">
    <label>Event Title</label>
    <input type="text" name="title" required>

    <label>Description</label>
    <textarea name="description" required></textarea>

    <label>Event Date</label>
    <input type="date" name="event_date" required>

    <label>Location</label>
    <input type="text" name="location" required>

    <label>Category</label>
    <input type="text" name="category" required>

    <label>Status</label>
    <select name="status">
        <option value="open">Open</option>
        <option value="closed">Closed</option>
    </select>

    <label>Poster (JPG, PNG, or GIF; maximum 2 MB)</label>
    <input type="file" name="poster" accept=".jpg,.jpeg,.png,.gif,image/jpeg,image/png,image/gif">

    <button type="submit">Add Event</button>
</form>

<p><a href="events.php">Back to Events</a></p>

</body>
</html>
