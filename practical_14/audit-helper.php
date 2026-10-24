
<?php
function logAction($conn, $action, $details = "")
{
    $userId = $_SESSION["user_id"] ?? null;

    $sql = "INSERT INTO audit_logs (user_id, action, details)
            VALUES (?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param(
        $stmt,
        "iss",
        $userId,
        $action,
        $details
    );

    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}
?>

