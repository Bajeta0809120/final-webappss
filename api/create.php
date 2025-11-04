<?php
require_once '../includes/functions.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, null, 'Invalid request method');
}

if (!hasPermission()) {
    jsonResponse(false, null, 'Unauthorized access');
}

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['type'])) {
        jsonResponse(false, null, 'Missing required fields');
    }

    $data = sanitizeInput($data);
    $type = $data['type'];

    switch ($type) {
        case 'program':
            if (!isset($data['name'])) {
                jsonResponse(false, null, 'Program name is required');
            }
            if (addProgram($data)) {
                jsonResponse(true, null, 'Program added successfully');
            } else {
                jsonResponse(false, null, 'Error adding program');
            }
            break;

        case 'section':
            if (!isset($data['name']) || !isset($data['program_id'])) {
                jsonResponse(false, null, 'Section name and program_id are required');
            }
            if (addSection($data)) {
                jsonResponse(true, null, 'Section added successfully');
            } else {
                jsonResponse(false, null, 'Error adding section');
            }
            break;

        case 'student':
            if (!isset($data['student_id']) || !isset($data['name']) || !isset($data['section_id'])) {
                jsonResponse(false, null, 'Student ID, name, and section_id are required');
            }
            if (addStudent($data)) {
                jsonResponse(true, null, 'Student added successfully');
            } else {
                jsonResponse(false, null, 'Error adding student');
            }
            break;

        default:
            jsonResponse(false, null, 'Invalid type specified');
    }
} catch (Exception $e) {
    jsonResponse(false, null, 'Error: ' . $e->getMessage());
}
?>
