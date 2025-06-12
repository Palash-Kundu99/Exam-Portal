<?php
session_start();
session_destroy();
header('Location: auth.php'); // Redirect to login page after logout
exit;
?>
