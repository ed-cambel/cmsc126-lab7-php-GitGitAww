<?php
include 'DBConnector.php';

//Connection Check
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// create table for students
$sqlStudents = "CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(40),
    age INT(2),
    email VARCHAR(40),
    course VARCHAR(40),
    year_level INT(1),
    graduating BOOLEAN
)";

// create table for images
$sqlImages = "CREATE TABLE IF NOT EXISTS student_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    image_path VARCHAR(255),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
)";

$conn->query($sqlStudents);
$conn->query($sqlImages);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Collect data
    $name = $_POST['name'];
    $age = $_POST['age'];
    $email = $_POST['email'];
    $course = $_POST['course'];
    $year = $_POST['year_level'];
    $graduating = isset($_POST['graduating']) ? 1 : 0;

    // Insert into table
    $studentInsert = "INSERT INTO students (name, age, email, course, year_level, graduating) 
                      VALUES ('$name', '$age', '$email', '$course', '$year', '$graduating')";

    if ($conn->query($studentInsert) === TRUE) {

        $last_student_id = $conn->insert_id; 

        if (!file_exists('uploads')) { mkdir('uploads', 0777, true); }
        $target_dir = "uploads/";
        $file_path = $target_dir . time() . "_" . basename($_FILES["image_path"]["name"]);
    
        if (move_uploaded_file($_FILES["image_path"]["tmp_name"], $file_path)) {
            
            // Insert into table
            $imageInsert = "INSERT INTO student_images (student_id, image_path) 
                                VALUES ('$last_student_id', '$file_path')";

            if ($conn->query($imageInsert) === TRUE) {
                echo "<h2>Successfully Registered!</h2>";
                echo "<p>Student $name has been added.</p>";
                echo "<a href='index.html'>Go Back to Form</a>";
            } else {
                echo "Database Error: " . $conn->error;
            }
        
        } else {
            echo "Error: Failed to upload the image.";
        }
    }
}

// close connection
$conn->close(); 
?>