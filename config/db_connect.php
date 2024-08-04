<?php
$host = 'blitz.cs.niu.edu';
$port = 3306;
$database = 'csci467';
$username = 'student';
$password = 'student';

$conn = new mysqli($host, $username, $password, $database, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>