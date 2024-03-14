<?php
$db_server = "localhost";
$db_user = "root";
$db_password = "";
$db_name = "community";
$conn = "";

try {
    $conn = mysqli_connect($db_server, $db_user, $db_password, $db_name);
    if (!$conn) {
        throw new Exception("Failed to connect to MySQL: " . mysqli_connect_error());
    }
} catch (Exception $e) {
    header("Location: error.php");
    exit;
}
?>
