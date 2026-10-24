
<?php
require_once "auth.php";
require_admin();
require_once "db.php";
require_once "audit-helper.php";

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST["csrf_token"] ?? "";

    if (!is_string($token) ||
        !hash_equals($_SESSION["csrf_token"], $token)) {
        http_response_code(403);
        exit("Invalid request. Please refresh the page.");
    }

    $id = filter_input(INPUT_POST, "user_id", FILTER_VALIDATE_INT);

    if (!$id || $id === (int) $_SESSION["user_id"]) {
        $message = "You cannot change your own account status here.";
    } else {
        $stmt = mysqli_prepare(
            $conn,
            "SELECT name, role, status FROM students WHERE id = ?"
        );
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $target = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if (!$target) {
            $message = "User not found.";
        } else {
            $newStatus = $target["status"] === "active"
                ? "inactive" : "active";

            $stmt = mysqli_prepare(
                $conn,
                "UPDATE students SET status = ? WHERE id = ?"
            );
            mysqli_stmt_bind_param($stmt, "si", $newStatus, $id);

            if (mysqli_stmt_execute($stmt)) {
                logAction(
                    $conn,
                    "user_status_changed",
                    "Changed user " . $target["name"] .
                    " (ID " . $id . ") to " . $newStatus
                );
                $message = "User status updated successfully.";
            } else {
                $message = "Could not update user status.";
            }

            mysqli_stmt_close($stmt);
        }
    }
}

$result = mysqli_query(
    $conn,
    "SELECT id, name, email, role, status, created_at
     FROM students ORDER BY id DESC"
);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
<div class="main">
    <h1>Manage Users</h1>
    <p><a href="admin-dashboard.php">Back to Dashboard</a></p>

    <?php if ($message !== "") { ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php } ?>

    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Action</th>
        </tr>

        <?php while ($user = mysqli_fetch_assoc($result)) { ?>
        <tr>
            <td><?php echo (int) $user["id"]; ?></td>
            <td><?php echo htmlspecialchars($user["name"]); ?></td>
            <td><?php echo htmlspecialchars($user["email"]); ?></td>
            <td><?php echo htmlspecialchars($user["role"]); ?></td>
            <td><?php echo htmlspecialchars($user["status"]); ?></td>
            <td>
                <?php if ((int) $user["id"] !== (int) $_SESSION["user_id"]) { ?>
                <form method="POST" action="users.php">
                    <input type="hidden" name="csrf_token"
                           value="<?php echo htmlspecialchars($_SESSION["csrf_token"]); ?>">
                    <input type="hidden" name="user_id"
                           value="<?php echo (int) $user["id"]; ?>">
                    <button type="submit">
                        <?php echo $user["status"] === "active"
                            ? "Deactivate" : "Activate"; ?>
                    </button>
                </form>
                <?php } else { ?>
                    Current account
                <?php } ?>
            </td>
        </tr>
        <?php } ?>
    </table>
</div>
</body>
</html>
