<?php
require_once '../includes/functions.php';
require_once '../includes/session_helper.php';

header('Content-Type: application/json');
// Limit to same-origin usage by default. If you need cross-origin, adjust accordingly.
// header('Access-Control-Allow-Origin: *');

// Only allow GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, null, 'Invalid request method');
}

// Require an authenticated admin session with proper role
initSession();
if (!validateSession() || !hasPermission('admin')) {
    http_response_code(401);
    jsonResponse(false, null, 'Unauthorized: admin access required');
}

try {
    $cacheKey = 'attendance_summary';
    $cachedData = getCache($cacheKey);
    if ($cachedData !== false) {
        jsonResponse(true, $cachedData, 'Summary retrieved from cache');
    }

    $conn = connectDB();

    // Total students
    $stmt = $conn->query('SELECT COUNT(*) AS cnt FROM students');
    $totalStudents = (int) ($stmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0);

    // Total programs
    $stmt = $conn->query('SELECT COUNT(*) AS cnt FROM programs');
    $totalPrograms = (int) ($stmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0);

    // Total sections
    $stmt = $conn->query('SELECT COUNT(*) AS cnt FROM sections');
    $totalSections = (int) ($stmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0);

    // Program distribution
    $stmt = $conn->query('SELECT p.name, COUNT(s.id) as count FROM programs p LEFT JOIN sections sec ON p.id = sec.program_id LEFT JOIN students s ON sec.id = s.section_id GROUP BY p.id, p.name ORDER BY count DESC');
    $programDistribution = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Section distribution
    $stmt = $conn->query('SELECT sec.name, COUNT(s.id) as count FROM sections sec LEFT JOIN students s ON sec.id = s.section_id GROUP BY sec.id, sec.name ORDER BY count DESC');
    $sectionDistribution = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Today's attendance
    $today = date('Y-m-d');
    // Verify admin is active in DB (extra validation)
    if (empty($_SESSION['admin_id'])) {
        http_response_code(401);
        jsonResponse(false, null, 'Unauthorized: admin id missing in session');
    }
    $stmt = $conn->prepare('SELECT id, is_active FROM admin WHERE id = ? LIMIT 1');
    $stmt->execute([$_SESSION['admin_id']]);
    $adminRow = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$adminRow || intval($adminRow['is_active']) !== 1) {
        http_response_code(401);
        jsonResponse(false, null, 'Unauthorized: admin account inactive');
    }

    $stmt = $conn->prepare('SELECT COUNT(*) AS present_cnt FROM attendance WHERE date = ? AND status = 1');
    $stmt->execute([$today]);
    $todaysPresent = (int) ($stmt->fetch(PDO::FETCH_ASSOC)['present_cnt'] ?? 0);

    $stmt = $conn->prepare('SELECT COUNT(*) AS recorded_cnt FROM attendance WHERE date = ?');
    $stmt->execute([$today]);
    $todaysRecorded = (int) ($stmt->fetch(PDO::FETCH_ASSOC)['recorded_cnt'] ?? 0);

    // Today's absences should be explicitly recorded as status = 0
    $stmt = $conn->prepare('SELECT COUNT(*) AS absent_cnt FROM attendance WHERE date = ? AND status = 0');
    $stmt->execute([$today]);
    $todaysAbsent = (int) ($stmt->fetch(PDO::FETCH_ASSOC)['absent_cnt'] ?? 0);

    // Total absences historically (status = 0)
    $stmt = $conn->query('SELECT COUNT(*) AS absences_cnt FROM attendance WHERE status = 0');
    $totalAbsences = (int) ($stmt->fetch(PDO::FETCH_ASSOC)['absences_cnt'] ?? 0);

    $data = [
        'total_students' => $totalStudents,
        'total_programs' => $totalPrograms,
        'total_sections' => $totalSections,
        'program_distribution' => $programDistribution,
        'section_distribution' => $sectionDistribution,
        'todays_present' => $todaysPresent,
        'todays_recorded' => $todaysRecorded,
        'todays_absent' => $todaysAbsent,
        'total_absences' => $totalAbsences,
        'date' => $today
    ];

    setCache($cacheKey, $data);
    jsonResponse(true, $data, 'Summary retrieved');
} catch (Exception $e) {
    jsonResponse(false, null, 'Error retrieving attendance summary: ' . $e->getMessage());
}
