<?php
session_start();

// Database connection details
$host = 'localhost';
$dbname = 'exam';
$user = 'root';
$pass = '';

// Create a connection
$mysqli = new mysqli($host, $user, $pass, $dbname);

// Check the connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Ensure the user is logged in before accessing the exam page
if (!isset($_SESSION['email'])) {
    header("Location: auth.php");
    exit();
}

if (!isset($_SESSION['questions'])) {
    $questions = [];
    $filename = 'question/DMQ.txt'; // Ensure this path is correct

    if (file_exists($filename)) {
        $lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        $currentQuestion = '';
        $options = [];
        $correctAnswer = '';

        foreach ($lines as $line) {
            // Check if it's a question line (e.g., 1. Which of the following is not a primary factor for on-page SEO optimization?)
            if (preg_match('/^\d+\.\s+(.*?)$/', $line, $matches)) {
                // Save the previous question if valid
                if ($currentQuestion && count($options) == 4 && $correctAnswer) {
                    $questions[] = [$currentQuestion, ...$options, $correctAnswer];
                }
                // Start a new question
                $currentQuestion = trim($matches[1]);
                $options = [];
                $correctAnswer = '';
            } elseif (preg_match('/^(a|b|c|d)\.\s+(.*)$/i', $line, $matches)) {
                // Adding options for lowercase letters
                $options[] = trim($matches[2]);
            } elseif (preg_match('/^Correct answer:\s+([A-C])\.\s*(.*)$/i', $line, $matches)) {
                // Capture the correct answer letter (C, etc.) and store it
                $correctAnswer = strtoupper(trim($matches[1]));
            }
        }

        // Add the last question if it's valid
        if ($currentQuestion && count($options) == 4 && $correctAnswer) {
            $questions[] = [$currentQuestion, ...$options, $correctAnswer];
        }

        // Check if questions were loaded
        if (empty($questions)) {
            die("Error: No valid questions found in the file.");
        }
    } else {
        die("Error: Questions file not found.");
    }

    // Shuffle questions and limit to 25
    shuffle($questions);
    $_SESSION['questions'] = array_slice($questions, 0, 25);
}


// Retrieve questions from session
$selectedQuestions = $_SESSION['questions'];

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $score = 0;
    $totalQuestions = count($selectedQuestions);

    foreach ($selectedQuestions as $index => $question) {
        $correctAnswer = trim($question[5]); // Correct answer is the 6th element in the question array
        if (isset($_POST["q$index"]) && $_POST["q$index"] === $correctAnswer) {
            $score++;
        }
    }

    // Calculate percentage score
    $percentageScore = ($score / $totalQuestions) * 100;

    // Store the score and exam taken in the database
    $email = $_SESSION['email'];
    $examGiven = 'DIGITAL MARKETING'; // Make sure this reflects the correct exam subject
    $stmt = $mysqli->prepare("UPDATE students SET score = ?, exam_given = ? WHERE email = ?");
    $stmt->bind_param("dss", $percentageScore, $examGiven, $email);
    $stmt->execute();
    $stmt->close();

    // Generate certificate if score is 80 or more
    $certificatePath = '';
    if ($percentageScore >= 80) {
        $certificatePath = 'certificates/certificate_' . uniqid() . '.pdf'; // Example path for the certificate
        // Logic to generate/save the certificate PDF should be implemented here
    }

    // Destroy the session to prevent re-taking the same exam
    session_destroy(); 

    // Redirect to the dashboard with the score and certificate path
    header("Location: dashboard.php?score=$percentageScore&certificate=" . urlencode($certificatePath));
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let warningGiven = false;
        let examCancelled = false;
        let blurCount = 0; // Count the number of times the window loses focus
        let timeRemaining = 1200; // 20 minutes in seconds

        function startExam() {
            document.getElementById('disclaimer').style.display = 'none';
            document.getElementById('exam-questions').style.display = 'block';

            // Enter full screen
            const elem = document.documentElement;
            if (elem.requestFullscreen) {
                elem.requestFullscreen();
            }

            monitorFocus(); // Start monitoring for focus loss
            monitorFullscreen(); // Start monitoring for fullscreen changes
            startTimer(); // Start the timer
        }

        function monitorFocus() {
            window.addEventListener('blur', function() {
                blurCount++;
                giveWarning();
            });
        }

        function monitorFullscreen() {
            document.addEventListener('fullscreenchange', function() {
                if (!document.fullscreenElement) {
                    // User has exited fullscreen
                    blurCount++; // Increment blur count
                    giveWarning();
                }
            });
        }

        function giveWarning() {
            if (blurCount === 1 && !warningGiven) {
                warningGiven = true;

                // Set a timeout to automatically cancel the exam after 4 seconds
                const cancelTimeout = setTimeout(() => {
                    cancelExam(); // Cancel exam if user doesn't respond in 4 seconds
                }, 4000);

                Swal.fire({
                    icon: 'warning',
                    title: 'Warning!',
                    text: 'EXITED FROM FULL SCREEN! Click "Continue" in 3 seconds or exam will be canceled!',
                    confirmButtonText: 'Continue Exam'
                }).then(() => {
                    clearTimeout(cancelTimeout); // Clear the timeout if the user clicks "Continue Exam"
                    startExam(); // Re-enter full-screen mode
                });

            } else if (blurCount > 1) {
                cancelExam();
            }
        }

        function cancelExam() {
            if (!examCancelled) {
                examCancelled = true;
                Swal.fire({
                    icon: 'error',
                    title: 'Exam Canceled',
                    text: 'Second attempt detected! Your exam has been canceled!',
                    confirmButtonText: 'OK'
                });
                // Delay redirection by 3 seconds
                setTimeout(() => {
                    window.location.href = "auth.php"; // Redirect back to registration or another page
                }, 3000); // 3000 milliseconds = 3 seconds
            }
        }

        function startTimer() {
            const timerElement = document.getElementById('time');
            const countdown = setInterval(() => {
                const minutes = Math.floor(timeRemaining / 60);
                const seconds = timeRemaining % 60;
                timerElement.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;

                if (timeRemaining <= 0) {
                    clearInterval(countdown);
                    document.getElementById('exam-form').submit(); // Auto-submit the exam when time is up
                }

                timeRemaining--;
            }, 1000);
        }
    </script>

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f1f3f5; /* Soft gray background */
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    overflow: hidden; /* Prevent scrolling on body */
}

.container {
    background-color: #ffffff; /* White background */
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); /* Slightly larger shadow for depth */
    text-align: center;
    max-width: 800px; /* Increased max width */
    width: 90%; /* Responsive width */
    max-height: 80vh; /* Limit height to 80% of the viewport height */
    overflow-y: auto; /* Enable vertical scrolling if content exceeds max height */
}

h1 {
    color: #343a40; /* Darker color for better contrast */
    margin-bottom: 20px;
    font-size: 2em; /* Increase size for headings */
}

h2 {
    color: #007bff; /* Keep primary color for subheadings */
    margin-bottom: 20px;
    font-size: 1.5em; /* Slightly larger subheading */
}

button {
    background-color: #007bff; /* Primary button color */
    color: #ffffff; /* White text */
    padding: 12px 24px; /* Slightly reduced padding */
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 1.1em; /* Slightly larger font size */
    transition: background-color 0.3s, transform 0.2s; /* Add transform for hover effect */
    margin-top: 10px; /* Margin to separate from other elements */
}

button:hover {
    background-color: #0056b3; /* Darker blue */
    transform: scale(1.05); /* Slightly enlarge */
}

#exam-questions {
    display: none; /* Initially hidden */
    text-align: left;
    margin-top: 20px;
}

.question {
    margin-bottom: 20px;
    padding: 25px; /* Increased padding for better spacing */
    border: 1px solid #007bff; /* Match border color with primary color */
    border-radius: 5px;
    background-color: #e9f7fe; /* Light blue background for questions */
    transition: transform 0.2s; /* Smooth scaling effect */
    max-width: 100%; /* Make sure the question does not overflow */
}

.question:hover {
    transform: scale(1.02); /* Slightly enlarge on hover */
}

.options {
    margin-left: 20px;
}

label {
    display: block; /* Stack options vertically */
    margin-bottom: 10px;
    font-weight: normal; /* Normal weight for options */
}

#timer {
    font-size: 24px;
    font-weight: bold;
    color: #dc3545; /* Red color for the timer */
    position: fixed;
    top: 20px;
    right: 20px;
    background-color: rgba(255, 255, 255, 0.8); /* Semi-transparent background */
    padding: 10px;
    border-radius: 5px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    z-index: 1000; /* Ensure timer is above other content */
}

    </style>
</head>
<body>

<div class="container">
        <div id="disclaimer">
        <h1 style="font-size: 2em; color: #343a40; margin-bottom: 15px; text-align: center;">
        ⚠️ Exam Disclaimer
        </h1>
        <p style="color: #dc3545; font-weight: bold; font-size: 1.1em; margin-bottom: 20px;">
        Please read the following instructions carefully before starting the exam:
    </p>
    <ul style="text-align:left; margin-left: 20px;">
        <li>🖥️ The exam will be in full-screen mode.</li>
        <li>🚫 Do not switch tabs, minimize the window, or move the exam to another screen.</li>
        <li>⚠️ If you exit full-screen mode, minimize, or switch screens more than once, your exam will be canceled.</li>
        <li>🚨 For any technical problems, please seek assistance from the invigilator.</li>
    </ul>
            <button onclick="startExam()">Start Exam</button>
        </div>

        <div id="exam-questions">
            <div id="timer" style="font-size: 24px; font-weight: bold; color: #333; position: fixed; top: 20px; right: 20px;">
                Timer: <span id="time">20:00</span>
            </div>
            <h2>Exam Questions</h2>
            <form id="exam-form" method="post" action="">
                <?php foreach ($selectedQuestions as $index => $question): ?>
                    <div class="question">
                        <p><?php echo ($index + 1) . '. ' . htmlspecialchars($question[0]); ?></p>
                        <div class="options">
                            <?php for ($i = 1; $i <= 4; $i++): ?>
                                <label>
                                    <input type="radio" name="q<?php echo $index; ?>" value="<?php echo htmlspecialchars(substr($question[5], 0, 1)); ?>"> 
                                    <?php echo htmlspecialchars($question[$i]); ?>
                                </label>
                            <?php endfor; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                <button type="submit">Submit Answers</button>
            </form>
        </div>
    </div>


</body>
</html>
