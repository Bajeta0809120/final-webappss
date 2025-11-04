<?php
require_once 'includes/db_connect.php';

try {
    $conn = connectDB();

    // Insert sample programs
    $programs = ['Computer Science', 'Information Technology', 'Business Administration'];
    foreach ($programs as $program) {
        $stmt = $conn->prepare("INSERT IGNORE INTO programs (name) VALUES (?)");
        $stmt->execute([$program]);
    }

    // Get program IDs
    $stmt = $conn->query("SELECT id, name FROM programs");
    $programMap = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $programMap[$row['name']] = $row['id'];
    }

    // Insert sample sections
    $sections = [
        ['A', $programMap['Computer Science']],
        ['B', $programMap['Computer Science']],
        ['A', $programMap['Information Technology']],
        ['B', $programMap['Information Technology']],
        ['A', $programMap['Business Administration']]
    ];

    foreach ($sections as $section) {
        $stmt = $conn->prepare("INSERT IGNORE INTO sections (name, program_id) VALUES (?, ?)");
        $stmt->execute($section);
    }

    // Insert sample students
    $students = [
        ['CS001', 'John Doe', 'A', $programMap['Computer Science']],
        ['CS002', 'Jane Smith', 'A', $programMap['Computer Science']],
        ['CS003', 'Bob Johnson', 'B', $programMap['Computer Science']],
        ['IT001', 'Alice Brown', 'A', $programMap['Information Technology']],
        ['IT002', 'Charlie Wilson', 'B', $programMap['Information Technology']],
        ['BA001', 'Diana Davis', 'A', $programMap['Business Administration']],
        ['CS004', 'Eve Garcia', 'A', $programMap['Computer Science']],
        ['CS005', 'Frank Miller', 'B', $programMap['Computer Science']],
        ['IT003', 'Grace Lee', 'A', $programMap['Information Technology']],
        ['BA002', 'Henry Taylor', 'A', $programMap['Business Administration']]
    ];

    foreach ($students as $student) {
        // Get section ID
        $stmt = $conn->prepare("SELECT id FROM sections WHERE name = ? AND program_id = ?");
        $stmt->execute([$student[2], $student[3]]);
        $sectionId = $stmt->fetch(PDO::FETCH_ASSOC)['id'];

        $stmt = $conn->prepare("INSERT IGNORE INTO students (student_id, name, section_id) VALUES (?, ?, ?)");
        $stmt->execute([$student[0], $student[1], $sectionId]);
    }

    // Insert sample attendance data for the last 7 days
    $stmt = $conn->query("SELECT student_id FROM students");
    $studentIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    for ($i = 0; $i < 7; $i++) {
        $date = date('Y-m-d', strtotime("-$i days"));
        foreach ($studentIds as $studentId) {
            // Random attendance: 80% present, 20% absent
            $status = (rand(1, 10) <= 8) ? 1 : 0;
            $stmt = $conn->prepare("INSERT IGNORE INTO attendance (student_id, date, status) VALUES (?, ?, ?)");
            $stmt->execute([$studentId, $date, $status]);
        }
    }

    echo "Sample data populated successfully!\n";
    echo "- 3 Programs\n";
    echo "- 5 Sections\n";
    echo "- 10 Students\n";
    echo "- 7 days of attendance data\n";

} catch (Exception $e) {
    echo "Error populating sample data: " . $e->getMessage();
}
