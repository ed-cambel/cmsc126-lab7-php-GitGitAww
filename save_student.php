<?php
include 'DBConnector.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect data from HTML 'name' attributes
    $name = $_POST['name'];
    $age = $_POST['age'];
    $email = $_POST['email'];
    $course = $_POST['course'];
    $year = $_POST['year_level'];
    $graduating = isset($_POST['graduating']) ? 1 : 0;

    // Handle Image Upload
    if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
    }
    
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
    
    $file_path = $target_dir . time() . "_" . basename($_FILES["image_path"]["name"]);
    move_uploaded_file($_FILES["image_path"]["tmp_name"], $file_path);

    // Insert into Table
    $sql = "INSERT INTO gitgitawdb (name, age, email, course, year_level, graduating, image_path) 
            VALUES ('$name', '$age', '$email', '$course', '$year', '$graduating', '$file_path')";

    if ($conn->query($sql) === TRUE) {
        echo "Successfully Registered! <a href='index.html'>Go Back</a>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>