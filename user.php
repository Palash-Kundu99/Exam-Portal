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

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $email = $_POST['email'];
    $password = $_POST['password'];
    $captured_image = $_POST['captured_image'];

    // Extract only the base64 string (remove the prefix)
    if (strpos($captured_image, ',') !== false) {
        $captured_image = explode(',', $captured_image)[1]; // Get only the base64 data
    }

    // Generate a unique filename for the image
    $image_file = 'uploads/' . uniqid('img_', true) . '.jpg';

    // Save the image as a file
    if (file_put_contents($image_file, base64_decode($captured_image)) === false) {
        die("Error saving image.");
    }

    // Prepare an SQL statement to fetch user data
    $stmt = $mysqli->prepare("SELECT password FROM students WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($hashed_password);
    $stmt->fetch();
    $stmt->close();

    // Verify the password
    if (password_verify($password, $hashed_password)) {
        // Store user data in the session
        $_SESSION['email'] = $email;

        // Redirect to the dashboard
        header("Location: dashboard.php");
        exit();
    } else {
        $message = "Invalid email or password.";
    }
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
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

</head>
<body>

<div class="container">
    <!-- Form Section -->
    <div class="form-section">
        <h2>Log in to check score</h2>
        <div id="countdownMessage" class="error-message"><?php echo $message; ?></div>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <input type="hidden" id="image_data" name="captured_image" value="">
           
            <button type="submit">Log In</button>
        </form>
        <p>Don't have an account? <a href="auth.php">Register here</a></p>
    </div>


</body>
</html>
