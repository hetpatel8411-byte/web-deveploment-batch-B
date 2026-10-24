
<?php
$conn_auth = mysqli_connect(
    "localhost",
    "root",
    "",
    "practical11_db"
);

if (!$conn_auth) {
    die("Login database connection failed.");
}

mysqli_set_charset($conn_auth, "utf8mb4");
?>
