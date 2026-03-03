<?php
// Database configuration for InfinityFree
$host = "sql308.infinityfree.com"; // Get this from your 'MySQL Hostname'
$db_user = "if0_41224072";        // Get this from your 'MySQL Username'
$db_pass = "8thmileims"; // The password you use to log in to InfinityFree
$db_name = "if0_41224072_8th_mile_db"; // The full database name you created in the panel

$conn = mysqli_connect($host, $db_user, $db_pass, $db_name);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
