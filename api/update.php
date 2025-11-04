<?php
require_once '../includes/functions.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT');

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    jsonResponse(false, null, 'Invalid request method');
}

if (!hasPermission()) {
    jsonResponse(false, null, 'Unauthorized access');
}

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['id']) || !isset($data['type'])) {
        jsonResponse(false, null, 'Missing required fields');
    }

    $id = $data['id'];
    unset($data['id']);
    $data = sanitizeInput($data);
    $type = $data['type'];
    unset($data['type']);

    switch ($type) {
        case 'program':
            if (!isset($data['name'])) {
                jsonResponse(false, null, 'Program name is required');
            }
            if (updateProgram($id, $data)) {
                jsonResponse(true, null, 'Program updated successfully');
            } else {
                jsonResponse(false, null, 'Error updating program');
            }
            break;

        case 'section':
            if (!isset($data['name']) || !isset($data['program_id'])) {
                jsonResponse(false, null, 'Section name and program_id are required');
            }
            if (updateSection($id, $data)) {
                jsonResponse(true, null, 'Section updated successfully');
            } else {
                jsonResponse(false, null, 'Error updating section');
            }
            break;

        case 'student':
            if (!isset($data['student_id']) || !isset($data['name']) || !isset($data['section_id'])) {
                jsonResponse(false, null, 'Student ID, name, and section_id are required');
            }
            if (updateStudent($id, $data)) {
                jsonResponse(true, null, 'Student updated successfully');
            } else {
                jsonResponse(false, null, 'Error updating student');
            }
            break;

        default:
            jsonResponse(false, null, 'Invalid type specified');
    }
} catch (Exception $e) {
    jsonResponse(false, null, 'Error: ' . $e->getMessage());
}
?>
