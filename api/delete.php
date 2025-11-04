<?php
require_once '../includes/functions.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE');

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
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

    $id = sanitizeInput($data['id']);
    $type = sanitizeInput($data['type']);

    switch ($type) {
        case 'program':
            if (deleteProgram($id)) {
                jsonResponse(true, null, 'Program deleted successfully');
            } else {
                jsonResponse(false, null, 'Error deleting program');
            }
            break;

        case 'section':
            if (deleteSection($id)) {
                jsonResponse(true, null, 'Section deleted successfully');
            } else {
                jsonResponse(false, null, 'Error deleting section');
            }
            break;

        case 'student':
            if (deleteStudent($id)) {
                jsonResponse(true, null, 'Student deleted successfully');
            } else {
                jsonResponse(false, null, 'Error deleting student');
            }
            break;

        default:
            jsonResponse(false, null, 'Invalid type specified');
    }
} catch (Exception $e) {
    jsonResponse(false, null, 'Error: ' . $e->getMessage());
}
?>
