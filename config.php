<?php
// Update these with your InfinityFree MySQL details
$host = "sql308.infinityfree.com"; // Your MySQL Hostname
$db_user = "if0_41224072";           // Your MySQL Username
$db_pass = "8thmileims";  // Your Account Password
$db_name = "if0_41224072_8th_mile_db";  // Your MySQL Database Name

$conn = mysqli_connect($host, $db_user, $db_pass, $db_name);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
