<?php
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "eventflow_db"; // Match your SQL script

$conn = mysqli_connect(
    $servername,
    $username,
    $password,
    $dbname
);

if (!$conn) {
    error_log("Database connection failed: " . mysqli_connect_error());
    die("Unable to connect to the database. Please try again later.");
}

mysqli_set_charset($conn, "utf8mb4");
?>