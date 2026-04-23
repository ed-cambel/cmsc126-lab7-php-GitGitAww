<?php

$servername = "localhost";
$username = "root";
$password = ""; 

// create connection
$conn = new mysqli($servername, $username, $password);

// create database
$sql = "CREATE DATABASE IF NOT EXISTS gitgitawDB";

if ($conn->query($sql) === TRUE) {
    echo "Database ready";
} else {
    echo "Error creating database: " . $conn->error;
}

$conn->close();
?>