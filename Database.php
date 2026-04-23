<?php
include 'DBConnector.php';

// create table
$sql = "CREATE TABLE IF NOT EXISTS gitgitawdb (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(40),
    age INT(2),
    email VARCHAR(40),
    course VARCHAR(40),
    year_level INT(1),
    graduating BOOLEAN,
    image_path VARCHAR(255)
)";

if ($conn->query($sql) === TRUE) {
    echo "Table Students created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully!";

// close connection
$conn->close(); 
?>