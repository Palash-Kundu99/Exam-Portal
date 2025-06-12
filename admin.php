<?php
// session_start();
// // Define a password
// define('PALASH', 'PALASH390'); // ****

// // Check if the user is logged in
// if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
//     // Handle login
//     if ($_SERVER['REQUEST_METHOD'] == 'POST') {
//         if (isset($_POST['password']) && $_POST['password'] === "PALASH390") {
//             $_SESSION['loggedin'] = true; // Set session variable to indicate the user is logged in
//         } else {
//             $error = "Invalid password. Please try again.";
//         }
//     }
// }

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

// Handle file upload
// ... (existing file upload code)

// Handle file deletion
// ... (existing file deletion code)

// Fetch unique exam names for filtering
$examsResult = $mysqli->query("SELECT DISTINCT exam_given FROM students");

// Handle filtering by exam
$examFilter = isset($_GET['exam_name']) ? $_GET['exam_name'] : '';
$studentsQuery = "SELECT * FROM students" . ($examFilter ? " WHERE exam_given = '$examFilter'" : "");
$result = $mysqli->query($studentsQuery);

// List question files
$questionFiles = array_diff(scandir("question/"), array('..', '.'));
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin - Student Data</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 20px;
        }

        h2 {
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }

        th {
            background-color: #4CAF50;
            color: white;
        }

        img {
            max-width: 100px;
            height: auto;
        }

        .upload-form {
            margin: 20px 0;
            padding: 20px;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .file-list {
            margin: 20px 0;
            padding: 20px;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .filter-form {
            margin: 20px 0;
            padding: 10px;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .filter-form label {
            margin-right: 10px;
        }

        .filter-form select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
    </style>
</head>

<body>

    <!-- Filter Form for Exam Selection -->

    <div class="filter-form">
        <h2>Filter Candidates Based on Exam Given</h2>
        <form method="get" action="">
            <label for="exam_name">Select Exam:</label>
            <select name="exam_name" id="exam_name" onchange="this.form.submit()">
                <option value="" <?php echo ($examFilter === '') ? 'selected' : ''; ?>>All Registered Candidates</option>
                <option value="HTML/CSS/JS" <?php echo ($examFilter === 'HTML/CSS/JS') ? 'selected' : ''; ?>>HTML/CSS/JS</option>
                <option value="PHP" <?php echo ($examFilter === 'PHP') ? 'selected' : ''; ?>>PHP</option>
                <option value="Digital Marketing" <?php echo ($examFilter === 'Digital Marketing') ? 'selected' : ''; ?>>Digital Marketing</option>
            </select>
        </form>
    </div>


    <?php
    // Decode value safely from URL if needed
    $examFilter = isset($_GET['exam_name']) ? urldecode($_GET['exam_name']) : '';
    $query = "SELECT * FROM students";
    if (!empty($examFilter)) {
        $safeFilter = $mysqli->real_escape_string($examFilter);
        $query .= " WHERE exam_given = '$safeFilter'";
    }
    $result = $mysqli->query($query);
    ?>

    <h2>All Registered Students</h2>
    <table>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Phone Number</th>
            <th>Image</th>
            <th>Registration Timestamp</th>
        </tr>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['phone_number']); ?></td>
                    <td>
                        <?php if (!empty($row['captured_image'])): ?>
                            <img src="<?php echo htmlspecialchars($row['captured_image']); ?>" alt="Captured Image" />
                        <?php else: ?>
                            No Image
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($row['timestamp']); ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="5">No students found for this exam.</td>
            </tr>
        <?php endif; ?>
    </table>


    <div class="upload-form">
        <h2>Upload Questions File</h2>
        <form action="" method="post" enctype="multipart/form-data">
            <label for="qus_file">Select TXT file to upload:</label>
            <input type="file" name="qus_file" id="qus_file" required>
            <input type="submit" value="Upload File" name="submit">
        </form>
    </div>

    <div class="file-list">
        <h2>Uploaded Questions Files</h2>
        <ul>
            <?php foreach ($questionFiles as $file): ?>
                <li>
                    <?php echo htmlspecialchars($file); ?>
                    <a href="?delete=<?php echo urlencode($file); ?>" onclick="return confirm('Are you sure you want to delete this file?');">Delete</a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <?php $mysqli->close(); ?>
</body>

</html>