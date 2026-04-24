// Submit Registration Button Listener
function submitRegistration() {
    const form = document.getElementById('registrationForm');s
    if (!form.reportValidity()) {
        return; 
    }

    const formData = new FormData(form);
    // Tell PHP which action to perform
    formData.append('action', 'registerStudent');

    fetch('Database.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        alert("Registration has been submitted successfully!");
        form.reset();
        console.log("Server Response:", data);
    })
    .catch(error => {
        console.error('Error:', error);
        alert("Something went wrong with the registration.");
    });
}

document.getElementById('searchBtn').addEventListener('click', function() {
    const inputVal = document.getElementById('IDInput').value;
    const display = document.getElementById('resultDisplay');

    // Send the data to the PHP file using a POST request
    fetch('Database.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        // We label the data "searchValue"
        body: 'searchValue=' + encodeURIComponent(inputVal) 
    })
    .then(response => response.text()) // Get the HTML response from PHP
    .then(data => {
        // Display the PHP output inside the HTML div
        display.innerHTML = data;
    })
    .catch(error => {
        console.error('Error:', error);
    });
});

// Update button Listener
document.getElementById('updateBtn').addEventListener('click', function(e) {
    e.preventDefault();
    const inputVal = document.getElementById('IDInput').value;
    const display = document.getElementById('resultDisplay');

    if (inputVal === "") {
        display.innerHTML = "<span style='color:red'>Please enter an ID or Name to update.</span>";
        return;
    }

    display.innerHTML = "<i>Loading update form...</i>";

    // Fetch the pre-filled form from PHP
    fetch('Database.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=getUpdateForm&updateID=' + encodeURIComponent(inputVal) 
    })
    .then(response => response.text())
    .then(data => {
        display.innerHTML = data;
    })
    .catch(error => console.error('Error:', error));
});

// Save Changes button listener
function submitUpdate(studentId) {
    const form = document.getElementById('updateForm');
    
    if (!form.reportValidity()) {
        return; // Stop the function if the form has errors
    }

    const formData = new FormData(form);
    formData.append('action', 'processUpdate');
    formData.append('id', studentId);

    const display = document.getElementById('resultDisplay');
    display.innerHTML = "<i>Saving changes...</i>";

    // Send the updated data to PHP
    fetch('Database.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        display.innerHTML = data;
    })
    .catch(error => console.error('Error:', error));
}

// Delete button Listener
function confirmDelete() {
    // Grab value from the existing IDInput field
    const studentID = document.getElementById('IDInput').value.trim();
    
    if (studentID === "") {
        alert("Please enter a Student ID to delete.");
        return;
    }

    if (confirm("Are you sure you want to permanently delete Student ID: " + studentID + "?")) {
        const display = document.getElementById('resultDisplay');
        display.innerHTML = "<i>Processing deletion...</i>";

        const formData = new FormData();
        formData.append('action', 'deleteStudent');
        formData.append('deleteID', studentID);

        fetch('Database.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            display.innerHTML = data;
            document.getElementById('IDInput').value = ""; // Clear the search box
        })
        .catch(error => {
            console.error('Error:', error);
            display.innerHTML = "<p style='color: red;'>An error occurred during deletion.</p>";
        });
    }
}