<?php
require_once 'includes/session_helper.php';
require_once 'includes/functions.php';

// Initialize session and validate
initSession();
if (!validateSession()) {
    header('Location: index.php');
    exit;
}

// Ensure attendance table exists
ensureAttendanceTableExists();

// Get selected section filter
$selectedSection = isset($_GET['section']) ? (int)$_GET['section'] : 0;

// Simple attendance save handler (now persists to DB)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_attendance'])) {
    // CSRF protection
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        $_SESSION['error'] = 'Invalid CSRF token. Please try again.';
        header('Location: attendance.php' . ($selectedSection ? '?section=' . $selectedSection : ''));
        exit;
    }

    $date = isset($_POST['date']) ? sanitizeInput($_POST['date']) : date('Y-m-d');
    $present = isset($_POST['present']) && is_array($_POST['present']) ? $_POST['present'] : [];

    try {
        $students = $selectedSection ? getStudentsBySection($selectedSection) : getAllStudents();
        $saved = 0;
        foreach ($students as $s) {
            $studentId = $s['student_id'];
            // We keyed the checkbox by internal id; check by that key
            $isPresent = isset($present[(int)$s['id']]) ? 1 : 0;
            if (recordAttendance($studentId, $date, $isPresent)) {
                $saved++;
            }
        }

        $_SESSION['success'] = "Attendance saved for {$saved} students.";
    } catch (Exception $e) {
        error_log('Attendance save error: ' . $e->getMessage());
        $_SESSION['error'] = 'Failed to save attendance. Check logs for details.';
    }

    header('Location: attendance.php' . ($selectedSection ? '?section=' . $selectedSection : ''));
    exit;
}

// Get students based on section filter
$students = $selectedSection ? getStudentsBySection($selectedSection) : getAllStudents();

// Get all sections for the filter dropdown
$sections = getAllSections();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS - Attendance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">CMS</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="students.php">Students</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="attendance.php">Attendance</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="programs.php">Programs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="sections.php">Sections</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="includes/logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row mb-3">
            <div class="col-md-8">
                <h2>Take Attendance</h2>
            </div>
            <div class="col-md-4 text-end">
                <a class="btn btn-secondary" href="students.php"><i class="bi bi-people"></i> Manage Students</a>
            </div>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Section Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="get" class="row g-3">
                    <div class="col-md-6">
                        <label for="section" class="form-label">Filter by Section (Optional)</label>
                        <select name="section" id="section" class="form-select" onchange="this.form.submit()">
                            <option value="">All Sections</option>
                            <?php foreach ($sections as $section): ?>
                                <option value="<?php echo $section['id']; ?>" <?php echo $selectedSection == $section['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($section['program_name'] . ' - ' . $section['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <?php if ($selectedSection): ?>
                            <a href="attendance.php" class="btn btn-outline-secondary">Clear Filter</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="post">
                    <div class="mb-3 row">
                        <label class="col-sm-2 col-form-label">Date</label>
                        <div class="col-sm-4">
                            <input type="date" name="date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Present</th>
                                    <th>Student ID</th>
                                    <th>Name</th>
                                    <th>Program</th>
                                    <th>Section</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($students)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">
                                            <?php echo $selectedSection ? 'No students found in selected section' : 'No students available'; ?>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($students as $s): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="present[<?php echo (int)$s['id']; ?>]" value="1">
                                        </td>
                                        <td><?php echo htmlspecialchars($s['student_id']); ?></td>
                                        <td><?php echo htmlspecialchars($s['name']); ?></td>
                                        <td><?php echo htmlspecialchars($s['program_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($s['section_name'] ?? 'N/A'); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-start mt-3">
                        <div>
                            <button type="submit" name="save_attendance" class="btn btn-primary"><i class="bi bi-check2-square"></i> Save Attendance</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
