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

// Get the data from the AJAX request
$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'];
$certificatePath = $data['path'];

// Update the certificate path in the database
$stmt = $mysqli->prepare("UPDATE students SET certificate = ? WHERE email = ?");
$stmt->bind_param("ss", $certificatePath, $email);
$stmt->execute();
$stmt->close();

$mysqli->close();
echo json_encode(['status' => 'success']);
?>
