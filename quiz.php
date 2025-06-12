<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['name'])) {
    header('Location: auth.php'); // Redirect to the auth page if not logged in
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Exam</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: rgba(0, 0, 0, 1);
        
            background-position: top left, bottom right;
            background-size: 20px 20px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 0 20px;
            color: rgba(255, 255, 255, 0.9);
        }

        h1 {
            margin-bottom: 30px;
            font-size: 2.5em;
            color: rgba(0, 123, 255, 0.85);
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
        }

        .container {
            display: flex;
            gap: 30px;
            justify-content: center;
            align-items: flex-start;
            flex-wrap: wrap;
            padding: 20px;
        }

        .card {
            background-color: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(12px);
            width: 260px;
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 30px rgba(255, 255, 255, 0.2);
        }

        .card img {
            max-width: 100%;
            border-radius: 15px;
            margin-bottom: 15px;
            height: 160px;
            object-fit: cover;
            opacity: 0.9;
        }

        .card h3 {
            margin: 15px 0;
            font-size: 1.6em;
            color: rgba(0, 123, 255, 0.9);
            font-weight: 500;
        }

        .card button {
            background-color: rgba(0, 123, 255, 0.9);
            color: white;
            padding: 12px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            transition: background-color 0.3s, transform 0.3s;
        }

        .card button:hover {
            background-color: rgba(0, 123, 255, 1);
            transform: scale(1.05);
        }

        footer {
    margin-top: auto;
    background-color: rgba(0, 0, 0, 0.9);
    color: rgba(255, 255, 255, 0.85);
    text-align: center;
    padding: 30px 20px;
    width: 100%;
    font-size: 1em;
    border-radius: 0 0 15px 15px;
    box-shadow: 0 -6px 15px rgba(255, 255, 255, 0.1);
    font-family: 'Roboto', sans-serif;
}

.footer-content {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.footer-name {
    font-weight: 700;
    color: #00b4d8;
}

.footer-link {
    color: #00b4d8;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.3s;
}

.footer-link:hover {
    color: #90e0ef;
}

.social-links {
    margin-top: 15px;
    display: flex;
    gap: 20px;
}

.social-links img {
    width: 25px;
    height: 25px;
    opacity: 0.8;
    transition: opacity 0.3s, transform 0.3s;
}

.social-links img:hover {
    opacity: 1;
    transform: scale(1.1);
}

    </style>
</head>
<body>
    <h1>Select Your Exam</h1>
    <div class="container">
        <div class="card">
            <img src="img/PHP.png" alt="PHP Exam">
            <h3>PHP</h3>
            <button onclick="startExam('PHP')">Start Exam</button>
        </div>
        <div class="card">
            <img src="img/HCJ.png" alt="HTML/CSS/JS Exam">
            <h3>HTML/CSS/JS</h3>
            <button onclick="startExam('HTML/CSS/JS')">Start Exam</button>
        </div>
        <div class="card">
            <img src="img/DM.png" alt="Digital Marketing Exam">
            <h3>DIGITAL MARKETING</h3>
            <button onclick="startExam('DIGITAL MARKETING')">Start Exam</button>
        </div>
    </div>

    <footer>
    <div class="footer-content">
        <p>&copy; 2024 <span class="footer-name">Palash Kundu</span>. All Rights Reserved.</p>
        <p>Powered by <a href="https://travarsa.com/" class="footer-link">Travarsa Private Limited</a></p>
        <div class="social-links">
            <a href="https://www.facebook.com" target="_blank" aria-label="Facebook">
                <img src="img/facebook-icon.png" alt="Facebook" />
            </a>
            <a href="https://www.twitter.com" target="_blank" aria-label="Twitter">
                <img src="img/twitter-icon.png" alt="Twitter" />
            </a>
            <a href="https://www.linkedin.com" target="_blank" aria-label="LinkedIn">
                <img src="img/linkedin-icon.png" alt="LinkedIn" />
            </a>
        </div>
    </div>
</footer>



    <script>
        function startExam(examSubject) {
            const isLoggedIn = '<?php echo isset($_SESSION['name']) ? "true" : "false"; ?>';
            if (isLoggedIn === "false") {
                alert('You need to log in to start an exam.');
                window.location.href = 'auth.php'; // Redirect to auth page
                return;
            }

            let examPage;
            switch (examSubject) {
                case 'PHP':
                    examPage = 'exam.php';
                    break;
                case 'HTML/CSS/JS':
                    examPage = 'exam2.php';
                    break;
                case 'DIGITAL MARKETING':
                    examPage = 'exam3.php';
                    break;
                default:
                    examPage = 'exam.php';
            }

            sessionStorage.setItem('examSubject', examSubject);
            window.location.href = examPage;
        }
    </script>
</body>
</html>
