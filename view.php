<?php
$log_file = "credentials.txt";
if (file_exists($log_file)) {
    header("Content-Type: text/plain");
    echo file_get_contents($log_file);
} else {
    echo "Belum ada data.";
}
?>
