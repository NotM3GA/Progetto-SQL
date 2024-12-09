<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "test_system";


try {
    $conn = new mysqli($servername, $username, $password, $dbname);
} catch (\Throwable $th) {
    die("Connection failed: " . throw $th);
}
?>

