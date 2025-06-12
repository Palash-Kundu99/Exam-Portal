<?php
session_start();

// Ensure the user is logged in before accessing this page
if (!isset($_SESSION['email'])) {
    header("Location: auth.php"); // Redirect to registration if not logged in
    exit();
}

// Connect to your database
$conn = new mysqli('localhost', 'root', '', 'exam');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the submitted answers
$questions = $_SESSION['questions'];
$score = 0;

foreach ($questions as $index => $question) {
    if (isset($_POST['q' . $index])) {
        // Increment score if the answer is correct
        if ($_POST['q' . $index] === $question[5]) { // Assuming the correct answer is the 6th element
            $score++;
        }
    }
}

// Save the score to the database
$email = $_SESSION['email']; // Assuming the email is used to identify the student
$sql = "UPDATE students SET score = ? WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ds", $score, $email);
$stmt->execute();

// Clear the questions from the session
unset($_SESSION['questions']);

// Display the score
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Result</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .result-container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 600px;
            width: 100%;
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        .score {
            font-size: 24px;
            color: #007bff;
        }
        .message {
            margin-top: 20px;
            font-size: 18px;
            color: #666;
        }
    </style>
</head>
<body>

<div class="result-container">
    <h1>Your Exam Result</h1>
    <p class="score">Score: <?php echo $score; ?> / 10</p>
    <?php if ($score >= 5): ?>
        <p class="message">Congratulations! You passed the exam.</p>
    <?php else: ?>
        <p class="message">Sorry, better luck next time.</p>
    <?php endif; ?>
    <script>
        // Automatically redirect after a few seconds (e.g., 5 seconds)
        setTimeout(function() {
            window.location.href = 'dashboard.php'; // Change to the desired page after exam
        }, 1000); // Adjust the time as needed
    </script>
</div>

</body>
</html>
