<?php

$servername = "localhost";
$username = "root";
$password = "";

// create connection
$conn = new mysqli($servername, $username, $password);

// connection check
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully!";

// create database
$sql = "CREATE DATABASE gitgitawDB";

if ($conn->query($sql) === TRUE) {
    echo "Database created successfully";
} else {
    echo "Error creating database: " . $conn->error;
}

// close connection
$conn->close();
?>