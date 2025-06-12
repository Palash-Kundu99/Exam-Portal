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

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: user.php");
    exit();
}

// Fetch student data
$email = $_SESSION['email'];
$stmt = $mysqli->prepare("SELECT name, email, phone_number, captured_image, score, certificate FROM students WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($name, $email, $phone_number, $captured_image, $score, $certificate);
$stmt->fetch();
$stmt->close();

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f4f7fa;
            margin: 0;
            padding: 0;
            color: #343a40;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #74ebd5 0%, #acb6e5 100%);
        }
        .container {
    max-width: 600px; /* Reduced width for a more compact appearance */
    background: #ffffff; /* Keep the background white for contrast */
    border-radius: 30px; /* Adjusted border radius for a more subtle curve */
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15); /* Slightly softer shadow for a lighter look */
    padding: 20px; /* Reduced padding for a tighter layout */
    position: relative;
    overflow: hidden;
    text-align: center; /* Centered text for a clean appearance */
    margin: 0 auto; /* Center the container in its parent */
}

        h2 {
            color: #007bff;
            font-size: 2.5em;
            margin-bottom: 20px;
            position: relative;
        }
        h2::after {
            content: '';
            display: block;
            width: 40px;
            height: 4px;
            background: #007bff;
            margin: 10px auto 0;
            border-radius: 5px;
        }
        .profile-card {
            display: flex;
            align-items: center;
            justify-content: center;
            background: #000;
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;

        }
        .profile-card img {
            border-radius: 50%;
            width: 120px;
            height: 120px;
            margin-right: 20px;
            border: 3px solid white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        .profile-info {
            flex: 1;
            text-align: left;
        }
        .profile-info p {
            margin: 5px 0;
            font-size: 1.1em;
        }
        .score-section {
            background: #28a745;
            color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin: 20px 0;
        }
        .score-section p {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .generate-button, .logout-button {
            display: inline-block;
            margin-top: 10px;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s, transform 0.3s;
        }
        .generate-button {
            background-color: #ffc107;
            color: black;
        }
        .generate-button:hover {
            background-color: #e0a800;
            transform: translateY(-2px);
        }
        .logout-button {
            background-color: #dc3545;
            color: white;
            margin-top: 20px;
        }
        .logout-button:hover {
            background-color: #c82333;
            transform: translateY(-2px);
        }
        @media (max-width: 768px) {
            .container {
                width: 95%;
            }
            .profile-card {
                flex-direction: column;
                align-items: center;
            }
            .profile-card img {
                margin-bottom: 15px;
            }
            .score-section p {
                font-size: 20px; /* Adjust font size for smaller screens */
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Student Dashboard</h2>

    <div class="profile-card">
        <img src="<?php echo htmlspecialchars($captured_image); ?>" alt="Captured Image">
        <div class="profile-info">
            <p><strong>Name:</strong> <?php echo htmlspecialchars($name); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
            <p><strong>Phone Number:</strong> <?php echo htmlspecialchars($phone_number); ?></p>
        </div>
    </div>

    <div class="score-section">
        <p><strong>Your Score:</strong> <?php echo htmlspecialchars($score); ?>%</p>
        <?php if ($score >= 80): ?>
            <p>Congratulations! You have successfully passed the exam!</p>
            <button class="generate-button" id="generateCertificateBtn">Download Certificate</button>
        <?php else: ?>
            <p>Sorry, you are not eligible for a certificate!</p>
        <?php endif; ?>
    </div>

    <button class="logout-button" onclick="window.location.href='logout.php'">Log Out</button>
</div>

<script src="https://cdn.jsdelivr.net/npm/pdf-lib@1.17.1/dist/pdf-lib.min.js"></script>
<script>
    document.getElementById('generateCertificateBtn').addEventListener('click', async function () {
        const userName = "<?php echo htmlspecialchars($name); ?>";
        const { PDFDocument, rgb, StandardFonts } = PDFLib;

        try {
            // Load the template image
            const templateUrl = 'template/template.png'; // Path to your template
            const response = await fetch(templateUrl);
            if (!response.ok) throw new Error('Failed to load template image.');
            const templateImageBytes = await response.arrayBuffer();

            // Create a new PDF document
            const pdfDoc = await PDFDocument.create();

            // Embed the template image into the document
            const templateImage = await pdfDoc.embedPng(templateImageBytes);
            const { width, height } = templateImage.scale(1);

            // Add a new page with the same size as the image
            const page = pdfDoc.addPage([width, height]);
            page.drawImage(templateImage, {
                x: 0,
                y: 0,
                width: width,
                height: height,
            });

            // Embed the standard font
// Embed Times New Roman font (Times Roman in PDFLib)
            const font = await pdfDoc.embedFont(StandardFonts.TimesRoman); // Use Times New Roman font
            const fontSize = 100;
            const textWidth = font.widthOfTextAtSize(userName, fontSize);
            const centerX = (width - textWidth) / 2;
            const centerY = height / 2; // Center vertically

            // Add text to the PDF
            page.drawText(userName, {
                x: centerX,
                y: centerY,
                size: fontSize,
                color: rgb(0, 0, 0),
                font,
            });

            // Serialize the PDF to bytes
            const pdfBytes = await pdfDoc.save();

            // Create a download link
            const downloadLink = document.createElement('a');
            downloadLink.href = URL.createObjectURL(new Blob([pdfBytes], { type: 'application/pdf' }));
            downloadLink.download = `Certificate_${userName}.pdf`;
            downloadLink.click();

            // Save the certificate to the server
            const certificatePath = `certificates/Certificate_${userName}.pdf`;
            await fetch('save_certificate.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email: "<?php echo htmlspecialchars($email); ?>", path: certificatePath })
            });

        } catch (error) {
            console.error(`Error: ${error.message}`);
        }
    });
</script>
</body>
</html>
