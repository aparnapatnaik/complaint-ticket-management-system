<?php
$host = '172.31.29.90';
$username = 'root';
$password = 'rootpassword';
$db_name = 'complaint_db';

// Create MySQLi connection
$conn = new mysqli($host, $username, $password, $db_name, 3306);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
