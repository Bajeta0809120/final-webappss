<?php
require_once '../includes/functions.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, null, 'Invalid request method');
}

if (!hasPermission()) {
    jsonResponse(false, null, 'Unauthorized access');
}

try {
    $type = isset($_GET['type']) ? $_GET['type'] : 'students';

    switch ($type) {
        case 'programs':
            $data = getAllPrograms();
            $message = 'Programs retrieved successfully';
            break;

        case 'sections':
            $data = getAllSections();
            $message = 'Sections retrieved successfully';
            break;

        case 'sections_by_program':
            if (!isset($_GET['program_id']) || !is_numeric($_GET['program_id'])) {
                jsonResponse(false, null, 'Program ID is required and must be numeric');
            }
            $data = getSectionsByProgram((int)$_GET['program_id']);
            $message = 'Sections retrieved successfully';
            break;

        case 'attendance_today':
            $data = getTodayAttendance();
            $message = 'Today\'s attendance retrieved successfully';
            break;

        case 'students':
        default:
            $data = getAllStudents();
            $message = 'Students retrieved successfully';
            break;
    }

    jsonResponse(true, $data, $message);
} catch (Exception $e) {
    jsonResponse(false, null, 'Error retrieving data: ' . $e->getMessage());
}
?>
