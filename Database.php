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
    image_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    image_path VARCHAR(255),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
)";

$conn->query($sqlStudents);
$conn->query($sqlImages);

// REGISTER NEW STUDENT by Insert
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'registerStudent') {
    
    $name = $_POST['name'];
    $age = $_POST['age'];
    $email = $_POST['email'];
    $course = $_POST['course'];
    $year_level = $_POST['year_level'];
    $graduating = isset($_POST['graduating']) ? 1 : 0;

    // Insert into 'students' table
    $insertSql = "INSERT INTO students (name, age, email, course, year_level, graduating) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertSql);
    $stmt->bind_param("sissii", $name, $age, $email, $course, $year_level, $graduating);
    
    if ($stmt->execute()) {
        // Get the ID of the student
        $newStudentId = $stmt->insert_id; 
        $stmt->close();

        // Image Directory Processing
        if (isset($_FILES['image_path']) && $_FILES['image_path']['error'] == 0) {
            
            $target_dir = "uploads/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true); 
            }

            $file_name = time() . "_" . basename($_FILES["image_path"]["name"]);
            $target_file = $target_dir . $file_name;

            // Insert into 'student_images' table
            if (move_uploaded_file($_FILES["image_path"]["tmp_name"], $target_file)) {
                $imgSql = "INSERT INTO student_images (student_id, image_path) VALUES (?, ?)";
                $imgStmt = $conn->prepare($imgSql);
                $imgStmt->bind_param("is", $newStudentId, $target_file);
                $imgStmt->execute();
                $imgStmt->close();
            }
        }
        
        echo "Registration submitted successfully!";
    } else {
        echo "Database Error: " . $conn->error;
    }
    
    exit(); 
}

// SEARCH STUDENT by Select
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['searchValue'])) {
    
    $searchInput = trim($_POST['searchValue']);

    $sql = "SELECT * FROM students WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $searchInput);
    $stmt->execute();
    $result = $stmt->get_result();

    // Display Student INFO
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo "<h3>Student Details:</h3>";
        echo "<p><strong>ID:</strong> " . htmlspecialchars($row['id']) . "</p>";
        echo "<p><strong>Name:</strong> " . htmlspecialchars($row['name']) . "</p>";
        echo "<p><strong>Age:</strong> " . htmlspecialchars($row['age']) . "</p>";
        echo "<p><strong>Email:</strong> " . htmlspecialchars($row['email']) . "</p>";
        echo "<p><strong>Course:</strong> " . htmlspecialchars($row['course']) . "</p>";
        echo "<p><strong>Year Level:</strong> " . htmlspecialchars($row['year_level']) . "</p>";
        echo "<p><strong>Graduating:</strong> " . ($row['graduating'] ? "Yes" : "No") . "</p>";
    } else {
        echo "<p style='color: red;'>No student found with that ID.</p>";
    }
}

// Display Update Form
elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'getUpdateForm') {
    
    $searchInput = trim($_POST['updateID']);

    $sql = "SELECT * FROM students WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $searchInput);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        $isChecked = $row['graduating'] ? "checked" : "";
        $sel1 = ($row['year_level'] == 1) ? "selected" : "";
        $sel2 = ($row['year_level'] == 2) ? "selected" : "";
        $sel3 = ($row['year_level'] == 3) ? "selected" : "";
        $sel4 = ($row['year_level'] == 4) ? "selected" : "";
        $sel5 = ($row['year_level'] == 5) ? "selected" : "";
        
        // Form Creation through HTML
        $html = <<<HTML
        <form id='updateForm' style='margin-top: 20px; text-align: left;'>
            
            <div class="section">
                <div class="section-title">UPDATE STUDENT DETAILS</div>

                <div class="row">
                    <div class="field">
                        <label>Name</label>
                        <input type="text" name="name" maxlength="40" value="{$row['name']}" required>
                    </div>
                
                    <div class="field">
                        <label>Age</label>
                        <input type="number" name="age" min="0" max="99" value="{$row['age']}" required>
                    </div>
                </div>
            </div>

            <div class="field full-width">
                <label>Email</label>
                <input type="email" name="email" maxlength="40" pattern="^[a-zA-Z0-9._%+-]+@up\.edu\.ph$" value="{$row['email']}" required>
                <small style="color: gray;">Must be a valid @up.edu.ph email address.</small>
            </div>

            <div class="section">
                <div class="row">
                    <div class="field">
                        <label>Course</label>
                        <input type="text" name="course" maxlength="40" value="{$row['course']}" required>
                    </div>
                
                    <div class="field">
                        <label>Year Level</label>
                        <select name="year_level" required>
                            <option value="1" {$sel1}>1</option>
                            <option value="2" {$sel2}>2</option>
                            <option value="3" {$sel3}>3</option>
                            <option value="4" {$sel4}>4</option>
                            <option value="5" {$sel5}>Nth</option>
                        </select>
                    </div>
                </div>

                <label>Graduating this year?</label>
                <div class="checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="graduating" value="1" {$isChecked}> Yes
                    </label>
                </div>
            </div>

            <div class="section">
                <div class="section-title">PROFILE PHOTO</div>
                <label>Update Profile Image (Leave blank to keep current photo)</label>
                <div class="file-box" style="margin-top: 10px;">
                    <input type="file" name="image_path" accept=".jpg,.png,.gif,.webp">
                    <p style="margin: 5px 0; color: gray;">Choose a file or drag it here</p>
                    <small style="color: gray;">JPG, PNG, GIF, WEBP accepted</small>
                </div>
            </div>

            <div class="section">
                <button type="button" class="submit-btn" onclick="submitUpdate({$row['id']})">Save Changes</button>
            </div>

        </form>
        HTML;

        echo $html;

    } else {
        echo "<p style='color: red;'>No student found to update.</p>";
    }
    
    $stmt->close();
    exit();
}

// UPDATE STUDENT INFORMATION by Update
elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'processUpdate') {
    
    $id = $_POST['id'];
    $name = $_POST['name'];
    $age = $_POST['age'];
    $email = $_POST['email'];
    $course = $_POST['course'];
    $year_level = $_POST['year_level'];
    $graduating = isset($_POST['graduating']) ? 1 : 0;

    // Update the 'students' table
    $updateSql = "UPDATE students SET name=?, age=?, email=?, course=?, year_level=?, graduating=? WHERE id=?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("sissisi", $name, $age, $email, $course, $year_level, $graduating, $id);
    $studentUpdated = $stmt->execute();
    $stmt->close();

    if ($studentUpdated) {
        $feedback = "<h3 style='color: green;'>Update Successful!</h3>";
        $feedback .= "<p>Student ID $id has been updated in the database.</p>";

        // Handle the image update
        if (isset($_FILES['image_path']) && $_FILES['image_path']['error'] == 0) {
            
            // CLEANUP where image gets deleted locally
            $findOldSql = "SELECT image_path FROM student_images WHERE student_id = ?";
            $findStmt = $conn->prepare($findOldSql);
            $findStmt->bind_param("i", $id);
            $findStmt->execute();
            $result = $findStmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                $oldFile = $row['image_path'];
                if (file_exists($oldFile)) {
                    unlink($oldFile); 
                }
            }
            $findStmt->close();

            $target_dir = "uploads/";
            $file_name = time() . "_" . basename($_FILES["image_path"]["name"]);
            $target_file = $target_dir . $file_name;

            // Update the database with the new path
            if (move_uploaded_file($_FILES["image_path"]["tmp_name"], $target_file)) {
                $updateImgSql = "UPDATE student_images SET image_path = ? WHERE student_id = ?";
                $imgStmt = $conn->prepare($updateImgSql);
                $imgStmt->bind_param("si", $target_file, $id);
                $imgStmt->execute();
                $imgStmt->close();
                
            } else {
                $feedback .= "<p style='color: red;'>Warning: Failed to save the new image.</p>";
            }
        }
        echo $feedback;
    } else {
        echo "<p style='color: red;'>Error updating record: " . $conn->error . "</p>";
    }
    exit();
}

// DELETE STUDENT by Delete
elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'deleteStudent') {
    
    $studentID = trim($_POST['deleteID']);

    // Find using the ID
    $findSql = "SELECT s.id, i.image_path FROM students s 
                LEFT JOIN student_images i ON s.id = i.student_id 
                WHERE s.id = ?";
    
    $stmt = $conn->prepare($findSql);
    $stmt->bind_param("s", $studentID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $realDbId = $row['id'];
        $filePath = $row['image_path'];

        // Remove file locally
        if ($filePath && file_exists($filePath)) {
            unlink($filePath);
        }

        // Deletion in the database
        $deleteSql = "DELETE FROM students WHERE id = ?";
        $delStmt = $conn->prepare($deleteSql);
        $delStmt->bind_param("i", $realDbId);

        if ($delStmt->execute()) {
            echo "<h3 style='color: #721c24;'>Deletion Successful</h3>";
            echo "<p>Student ID <b>$studentID</b> has been completely removed from the system.</p>";
        } else {
            echo "<p style='color: red;'>Database Error: " . $conn->error . "</p>";
        }
        $delStmt->close();

    } else {
        echo "<p style='color: orange;'>No record found with ID: $studentID</p>";
    }

    $stmt->close();
    exit();
}

// close connection
$conn->close(); 
?>