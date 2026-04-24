<?php
$conn = new mysqli("127.0.0.1", "root", "Hinata@07", "cozypages");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
session_start();
?>