<?php
require_once 'includes/db_connect.php';

$conn = connectDB();
echo "=== DATABASE CONTENTS ===\n\n";

// Programs
echo "PROGRAMS:\n";
$stmt = $conn->query('SELECT * FROM programs');
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "- {$row['name']} (ID: {$row['id']})\n";
}

// Sections
echo "\nSECTIONS:\n";
$stmt = $conn->query('SELECT s.*, p.name as program_name FROM sections s JOIN programs p ON s.program_id = p.id');
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "- {$row['name']} (Program: {$row['program_name']})\n";
}

// Students
echo "\nSTUDENTS:\n";
$stmt = $conn->query('SELECT COUNT(*) as count FROM students');
$count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
echo "Total: $count students\n";

// Attendance Summary
echo "\nATTENDANCE SUMMARY:\n";
$today = date('Y-m-d');
$stmt = $conn->prepare('SELECT COUNT(*) as present FROM attendance WHERE date = ? AND status = 1');
$stmt->execute([$today]);
$present = $stmt->fetch(PDO::FETCH_ASSOC)['present'];

$stmt = $conn->prepare('SELECT COUNT(*) as absent FROM attendance WHERE date = ? AND status = 0');
$stmt->execute([$today]);
$absent = $stmt->fetch(PDO::FETCH_ASSOC)['absent'];

$stmt = $conn->query('SELECT COUNT(*) as total_absences FROM attendance WHERE status = 0');
$total_absences = $stmt->fetch(PDO::FETCH_ASSOC)['total_absences'];

echo "Today ($today):\n";
echo "- Present: $present\n";
echo "- Absent: $absent\n";
echo "- Total historical absences: $total_absences\n";
