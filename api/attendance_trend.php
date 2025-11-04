<?php
require_once '../includes/functions.php';
require_once '../includes/session_helper.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, null, 'Invalid request method');
}

initSession();
if (!validateSession() || !hasPermission('admin')) {
    http_response_code(401);
    jsonResponse(false, null, 'Unauthorized: admin access required');
}

try {
    $cacheKey = 'attendance_trend';
    $cachedData = getCache($cacheKey);
    if ($cachedData !== false) {
        jsonResponse(true, $cachedData, 'Trend retrieved from cache');
    }

    $conn = connectDB();

    // Verify admin active
    $stmt = $conn->prepare('SELECT is_active FROM admin WHERE id = ? LIMIT 1');
    $stmt->execute([$_SESSION['admin_id']]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$admin || intval($admin['is_active']) !== 1) {
        http_response_code(401);
        jsonResponse(false, null, 'Unauthorized');
    }

    // Prepare dates for the last 7 days (including today)
    $days = 7;
    $end = new DateTime();
    $start = (clone $end)->modify('-' . ($days - 1) . ' days');
    $startStr = $start->format('Y-m-d');
    $endStr = $end->format('Y-m-d');

    $sql = "SELECT date,
                SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) AS present_count,
                SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) AS absent_count
            FROM attendance
            WHERE date BETWEEN ? AND ?
            GROUP BY date
            ORDER BY date ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$startStr, $endStr]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Map results by date for easy lookup
    $map = [];
    foreach ($rows as $r) {
        $map[$r['date']] = [
            'present' => (int)$r['present_count'],
            'absent' => (int)$r['absent_count']
        ];
    }

    // Build series for each day
    $labels = [];
    $presentSeries = [];
    $absentSeries = [];
    $dt = clone $start;
    for ($i = 0; $i < $days; $i++) {
        $d = $dt->format('Y-m-d');
        $labels[] = $d;
        $presentSeries[] = $map[$d]['present'] ?? 0;
        $absentSeries[] = $map[$d]['absent'] ?? 0;
        $dt->modify('+1 day');
    }

    $data = [
        'labels' => $labels,
        'present' => $presentSeries,
        'absent' => $absentSeries,
    ];

    setCache($cacheKey, $data);
    jsonResponse(true, $data, 'Trend retrieved');
} catch (Exception $e) {
    jsonResponse(false, null, 'Error retrieving trend: ' . $e->getMessage());
}
