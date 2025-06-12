<?php
session_start();

// Database connection details
$host = 'localhost'; // or your database host
$dbname = 'exam';
$user = 'root';
$pass = '';

// Create a connection
$mysqli = new mysqli($host, $user, $pass, $dbname);
// Check the connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$message = ''; // Initialize message variable
$canRegister = true; // Flag for registration

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
    $captured_image = $_POST['captured_image'];

    
    if (strpos($captured_image, ',') !== false) {
        $captured_image = explode(',', $captured_image)[1]; // Get only the base64 data
    }

    // Generate a unique filename for the image
    $image_file = 'uploads/' . uniqid('img_', true) . '.jpg';

    // Save the image as a file
    if (file_put_contents($image_file, base64_decode($captured_image)) === false) {
        die("Error saving image.");
    }

    // Get the current timestamp
    date_default_timezone_set('Asia/Kolkata');
    $current_time = date('Y-m-d H:i:s');

    // Check if the email already exists
    $checkStmt = $mysqli->prepare("SELECT timestamp FROM students WHERE email = ?");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkStmt->bind_result($last_submission_time);
    $checkStmt->fetch();
    $checkStmt->close();

    // Check if the email exists
if ($last_submission_time) {
    $last_submission = strtotime($last_submission_time);
    $current_time_stamp = strtotime($current_time);
    $time_difference = $current_time_stamp - $last_submission;

    // Check if it's been less than 30 days
    if ($time_difference < 2592000) { // 2592000 seconds = 30 days
        $remaining_time = 2592000 - $time_difference; // Remaining time in seconds
        $message = "You can register again after <strong>" . $remaining_time . " seconds</strong>.";
        $canRegister = false; // Prevent registration
    }
}

    if ($canRegister) {
        // Prepare an SQL statement to insert or update the data
        $stmt = $mysqli->prepare("INSERT INTO students (name, email, phone_number, password, timestamp, captured_image) 
                                  VALUES (?, ?, ?, ?, ?, ?)
                                  ON DUPLICATE KEY UPDATE timestamp = VALUES(timestamp), captured_image = VALUES(captured_image)");
        $stmt->bind_param("ssssss", $name, $email, $phone_number, $password, $current_time, $image_file);

        // Execute the statement
        if ($stmt->execute()) {
            // Store user data in the session
            $_SESSION['name'] = $name;
            $_SESSION['email'] = $email;
            $_SESSION['phone_number'] = $phone_number;

            // Success message
            $message = "<strong>Registration successful!</strong>.";
            // Set flag for SweetAlert
            $success = true;
        } else {
            $message = "Error: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    }
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            background-color: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            gap: 20px;
        }

        .form-section, .preview-section {
            flex: 1;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            background-color: #007bff;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #0056b3;
        }

        .capture-button {
            background-color: #28a745;
            margin-bottom: 20px;
        }

        .capture-button:hover {
            background-color: #218838;
        }

        .recapture-button {
            background-color: #ffc107;
            margin-top: 10px;
        }

        .recapture-button:hover {
            background-color: #e0a800;
        }

        video, img {
            display: block;
            margin: 0 auto 20px auto;
            border-radius: 5px;
            max-width: 100%;
        }

        canvas {
            display: none;
        }

        .success-message {
            color: green;
            text-align: center;
            font-size: 16px;
            margin-top: 10px;
        }

        .error-message {
            color: red;
            text-align: center;
            font-size: 16px;
            margin-top: 10px;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                width: 90%;
                margin: 20px;
            }
        }
    </style>
    <script>
        let capturedImage = '';

        // Play camera shutter sound
        function playShutterSound() {
            const audio = new Audio('camera-shutter-click.mp3'); // Use your own sound file
            audio.play();
        }

        // Capture image from webcam
        function captureImage() {
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            const context = canvas.getContext('2d');
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            capturedImage = canvas.toDataURL('image/jpeg');
            document.getElementById('image_data').value = capturedImage; // Set captured image to hidden input
            document.getElementById('capturedImagePreview').src = capturedImage; // Show captured image preview
            playShutterSound(); // Play sound
            
            // Show success message
            document.getElementById('successMessage').innerText = "Image captured successfully!";
        }

        // Start video stream from webcam
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(stream => {
                const video = document.getElementById('video');
                video.srcObject = stream;
                video.play();
            })
            .catch(err => {
                console.error("Error accessing the camera: ", err);
            });

        // Recapture image
        function recaptureImage() {
            document.getElementById('capturedImagePreview').src = ''; // Clear the preview
            document.getElementById('successMessage').innerText = ''; // Clear success message
        }

        // Countdown timer
// Countdown timer
function startCountdown(seconds) {
    let remainingTime = seconds;
    const countdownElement = document.getElementById('countdownMessage');
    const interval = setInterval(() => {
        if (remainingTime <= 0) {
            clearInterval(interval);
            countdownElement.innerText = ''; // Clear message after countdown
        } else {
            const days = Math.floor(remainingTime / (24 * 3600));
            const hours = Math.floor((remainingTime % (24 * 3600)) / 3600);
            const minutes = Math.floor((remainingTime % 3600) / 60);
            const secs = remainingTime % 60;

            countdownElement.innerText = `You can register again in ${days} days, ${hours} hours, ${minutes} minutes, and ${secs} seconds.`;
        }
        remainingTime--;
    }, 1000);
}


        // Start countdown if there's a wait message
        window.onload = function() {
            const waitMessage = "<?php echo $message; ?>";
            const successFlag = <?php echo isset($success) ? 'true' : 'false'; ?>;
            if (successFlag) {
                Swal.fire({
                    title: 'Registration successful!',
                    text: 'Redirecting to exam portal .',
                    icon: 'success',
                    timer: 3000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = "quiz.php"; // Redirect after the alert
                });
            } else if (waitMessage.includes("can register again after")) {
                const seconds = parseInt(waitMessage.match(/(\d+)/)[0]);
                startCountdown(seconds);
            } else {
                document.getElementById('countdownMessage').innerHTML = waitMessage;
            }
        };
    </script>
</head>
<body>

<div class="container">
    <!-- Form Section -->
    <div class="form-section">
        <h2>Register</h2>
        <div id="countdownMessage" class="error-message"><?php echo $message; ?></div> <!-- Display messages here -->
        <form method="POST">
            <input type="text" name="name" placeholder="Name" required><br>
            <input type="email" name="email" placeholder="Email" required><br>
            <input type="text" name="phone_number" placeholder="Phone Number" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <input type="hidden" id="image_data" name="captured_image" value="">
            <button type="button" class="capture-button" onclick="captureImage()">Capture Image</button>
            <button type="submit">Register</button>
        </form>

        <p>Want to check you profile? <a href="user.php">Log in here</a></p>
    </div>


    <!-- Preview Section -->
    <div class="preview-section">
        <h2>Preview</h2>
        <video id="video" width="320" height="240" autoplay></video>
        <canvas id="canvas" width="320" height="240"></canvas>
        <h3 style="text-align:center;">Captured Image</h3>
        <img id="capturedImagePreview" alt="Your image will appear here" />
        <div id="successMessage" class="success-message"></div>
        <button class="recapture-button" onclick="recaptureImage()">Recapture Image</button>
    </div>
</div>

</body>
</html>