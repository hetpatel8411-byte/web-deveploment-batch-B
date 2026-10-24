
<?php
$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "practical14_db"
);

if (!$conn) {
    die("Database connection failed.");
}

mysqli_set_charset($conn, "utf8mb4");
?>
