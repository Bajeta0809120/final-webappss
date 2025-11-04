<?php
require_once 'db_connect.php';
require_once 'session_helper.php';

// Authentication Functions
function validateLogin($username, $password) {
    try {
        $conn = connectDB();

        $stmt = $conn->prepare("SELECT id, username, password, role FROM admin WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin['password'])) {
            return ['id' => $admin['id'], 'role' => $admin['role']];
        }

        return false;
    } catch (Exception $e) {
        error_log("Authentication error: " . $e->getMessage());
        throw new Exception("Authentication error occurred");
    }
}

function createAdminAccount($username, $password, $email) {
    try {
        $conn = connectDB();

        // Case-insensitive check for username
        $stmt = $conn->prepare("SELECT id FROM admin WHERE LOWER(username) = LOWER(?)");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            throw new Exception("Username '" . htmlspecialchars($username) . "' is already taken. Please choose a different username.");
        }

        // Begin transaction
        $conn->beginTransaction();

        try {
            // Create new admin account
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("
                INSERT INTO admin (username, password, role, is_active, login_attempts, created_at)
                VALUES (?, ?, 'admin', 1, 0, NOW())
            ");

            $stmt->execute([$username, $hashedPassword]);
            $conn->commit();
            return true;

        } catch (PDOException $e) {
            // Rollback on error
            $conn->rollBack();

            // Check for duplicate entry errors
            if ($e->getCode() == 23000) {
                if (stripos($e->getMessage(), 'username') !== false) {
                    throw new Exception("Username '" . htmlspecialchars($username) . "' is already taken. Please choose a different username.");
                }
            }

            throw $e;
        }
    } catch (Exception $e) {
        error_log("Account creation error: " . $e->getMessage());
        throw new Exception($e->getMessage());
    }
}

function validatePassword($password) {
    // At least 8 characters long
    if (strlen($password) < 8) {
        return "Password must be at least 8 characters long";
    }

    // Contains at least one uppercase letter
    if (!preg_match('/[A-Z]/', $password)) {
        return "Password must contain at least one uppercase letter";
    }

    // Contains at least one lowercase letter
    if (!preg_match('/[a-z]/', $password)) {
        return "Password must contain at least one lowercase letter";
    }

    // Contains at least one number
    if (!preg_match('/[0-9]/', $password)) {
        return "Password must contain at least one number";
    }

    // Contains at least one special character
    if (!preg_match('/[!@#$%^&*()\-_=+{};:,<.>]/', $password)) {
        return "Password must contain at least one special character";
    }

    return true;
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function isAccountLocked($username) {
    try {
        $conn = connectDB();
        $stmt = $conn->prepare("SELECT is_active, login_attempts FROM admin WHERE username = ?");
        $stmt->execute([$username]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result && ($result['is_active'] == 0 || $result['login_attempts'] >= 10);
    } catch (Exception $e) {
        error_log("Account lock check error: " . $e->getMessage());
        return false;
    }
}

// Program Management Functions
function getAllPrograms() {
    $conn = connectDB();
    $stmt = $conn->query("
        SELECT p.*,
               COUNT(DISTINCT s.id) as sections_count,
               COUNT(DISTINCT st.id) as students_count
        FROM programs p
        LEFT JOIN sections s ON p.id = s.program_id
        LEFT JOIN students st ON s.id = st.section_id
        GROUP BY p.id, p.name
        ORDER BY p.name
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function addProgram($data) {
    $conn = connectDB();
    $stmt = $conn->prepare("INSERT INTO programs (name) VALUES (?)");
    return $stmt->execute([$data['name']]);
}

function updateProgram($id, $data) {
    $conn = connectDB();
    $stmt = $conn->prepare("UPDATE programs SET name = ? WHERE id = ?");
    return $stmt->execute([$data['name'], $id]);
}

function deleteProgram($id) {
    $conn = connectDB();

    // Begin transaction to ensure atomicity
    $conn->beginTransaction();

    try {
        // First, get all sections associated with this program
        $stmt = $conn->prepare("SELECT id FROM sections WHERE program_id = ?");
        $stmt->execute([$id]);
        $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Delete all students and their attendance records for each section
        foreach ($sections as $section) {
            // Delete attendance records for students in this section
            $stmt = $conn->prepare("DELETE FROM attendance WHERE student_id IN (SELECT student_id FROM students WHERE section_id = ?)");
            $stmt->execute([$section['id']]);

            // Delete students in this section
            $stmt = $conn->prepare("DELETE FROM students WHERE section_id = ?");
            $stmt->execute([$section['id']]);
        }

        // Delete all sections associated with this program
        $stmt = $conn->prepare("DELETE FROM sections WHERE program_id = ?");
        $stmt->execute([$id]);

        // Finally, delete the program
        $stmt = $conn->prepare("DELETE FROM programs WHERE id = ?");
        $stmt->execute([$id]);

        // Commit the transaction
        $conn->commit();
        return true;
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollBack();
        error_log("Failed to delete program: " . $e->getMessage());
        return false;
    }
}

// Section Management Functions
function getAllSections() {
    $conn = connectDB();
    $stmt = $conn->query("
        SELECT s.*, p.name as program_name,
               COUNT(st.id) as students_count
        FROM sections s
        LEFT JOIN programs p ON s.program_id = p.id
        LEFT JOIN students st ON s.id = st.section_id
        GROUP BY s.id, s.name, s.program_id, p.name
        ORDER BY p.name, s.name
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function addSection($data) {
    $conn = connectDB();
    $stmt = $conn->prepare("INSERT INTO sections (name, program_id) VALUES (?, ?)");
    return $stmt->execute([$data['name'], $data['program_id']]);
}

function updateSection($id, $data) {
    $conn = connectDB();
    $stmt = $conn->prepare("UPDATE sections SET name = ?, program_id = ? WHERE id = ?");
    return $stmt->execute([$data['name'], $data['program_id'], $id]);
}

function deleteSection($id) {
    $conn = connectDB();

    // Begin transaction to ensure atomicity
    $conn->beginTransaction();

    try {
        // First, delete all attendance records for students in this section
        $stmt = $conn->prepare("DELETE FROM attendance WHERE student_id IN (SELECT student_id FROM students WHERE section_id = ?)");
        $stmt->execute([$id]);

        // Then delete all students associated with this section
        $stmt = $conn->prepare("DELETE FROM students WHERE section_id = ?");
        $stmt->execute([$id]);

        // Finally delete the section
        $stmt = $conn->prepare("DELETE FROM sections WHERE id = ?");
        $stmt->execute([$id]);

        // Commit the transaction
        $conn->commit();
        return true;
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollBack();
        error_log("Failed to delete section: " . $e->getMessage());
        return false;
    }
}

function getSectionIdByName($sectionName, $programId = null) {
    $conn = connectDB();
    if ($programId) {
        $stmt = $conn->prepare("SELECT id FROM sections WHERE name = ? AND program_id = ?");
        $stmt->execute([$sectionName, $programId]);
    } else {
        $stmt = $conn->prepare("SELECT id FROM sections WHERE name = ?");
        $stmt->execute([$sectionName]);
    }
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['id'] : false;
}

function getSectionsByProgram($programId) {
    $conn = connectDB();
    $stmt = $conn->prepare("SELECT id, name FROM sections WHERE program_id = ? ORDER BY name");
    $stmt->execute([$programId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getStudentsBySection($sectionId) {
    $conn = connectDB();
    $stmt = $conn->prepare("
        SELECT s.*, sec.name as section_name, sec.program_id, p.name as program_name
        FROM students s
        LEFT JOIN sections sec ON s.section_id = sec.id
        LEFT JOIN programs p ON sec.program_id = p.id
        WHERE s.section_id = ?
        ORDER BY s.name
    ");
    $stmt->execute([$sectionId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function studentIdExists($studentId) {
    $conn = connectDB();
    $stmt = $conn->prepare("SELECT id FROM students WHERE student_id = ?");
    $stmt->execute([$studentId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
}

function getStudentById($id) {
    $conn = connectDB();
    $stmt = $conn->prepare("
        SELECT s.*, sec.name as section_name, sec.program_id, p.name as program_name
        FROM students s
        LEFT JOIN sections sec ON s.section_id = sec.id
        LEFT JOIN programs p ON sec.program_id = p.id
        WHERE s.id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Student Management Functions
function getAllStudents() {
    $conn = connectDB();
    $stmt = $conn->query("
        SELECT s.*, sec.name as section_name, sec.program_id, p.name as program_name
        FROM students s
        LEFT JOIN sections sec ON s.section_id = sec.id
        LEFT JOIN programs p ON sec.program_id = p.id
        ORDER BY s.name
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function addStudent($data) {
    $conn = connectDB();
    $stmt = $conn->prepare("INSERT INTO students (student_id, name, section_id) VALUES (?, ?, ?)");
    return $stmt->execute([$data['student_id'], $data['name'], $data['section_id']]);
}

function updateStudent($id, $data) {
    $conn = connectDB();
    $stmt = $conn->prepare("UPDATE students SET student_id = ?, name = ?, section_id = ? WHERE id = ?");
    return $stmt->execute([$data['student_id'], $data['name'], $data['section_id'], $id]);
}

function deleteStudent($id) {
    $conn = connectDB();

    // Begin transaction to ensure atomicity
    $conn->beginTransaction();

    try {
        // First, delete all attendance records for this student
        $stmt = $conn->prepare("DELETE FROM attendance WHERE student_id = (SELECT student_id FROM students WHERE id = ?)");
        $stmt->execute([$id]);

        // Then delete the student
        $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
        $stmt->execute([$id]);

        // Commit the transaction
        $conn->commit();
        return true;
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollBack();
        error_log("Failed to delete student: " . $e->getMessage());
        return false;
    }
}

// Attendance Management Functions
function ensureAttendanceTableExists() {
    try {
        $conn = connectDB();
        $sql = "CREATE TABLE IF NOT EXISTS attendance (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_id VARCHAR(50) NOT NULL,
            date DATE NOT NULL,
            status TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_student_date (student_id, date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $conn->exec($sql);
        return true;
    } catch (Exception $e) {
        error_log('Failed to create/verify attendance table: ' . $e->getMessage());
        return false;
    }
}

/**
 * Record or update attendance for a student on a given date.
 * Uses an upsert so repeated saves update the existing record.
 */
function recordAttendance($student_id, $date, $status) {
    try {
        $conn = connectDB();
        $stmt = $conn->prepare("INSERT INTO attendance (student_id, date, status) VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE status = VALUES(status), updated_at = NOW()");
        return $stmt->execute([$student_id, $date, $status]);
    } catch (Exception $e) {
        error_log('Failed to record attendance: ' . $e->getMessage());
        return false;
    }
}

function getAttendanceByDate($date) {
    $conn = connectDB();
    $stmt = $conn->prepare("
        SELECT a.*, s.name, s.section
        FROM attendance a
        JOIN students s ON a.student_id = s.student_id
        WHERE a.date = ?
    ");
    $stmt->execute([$date]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTodayAttendance() {
    $conn = connectDB();
    $today = date('Y-m-d');
    $stmt = $conn->prepare("
        SELECT a.*, s.name as student_name, p.name as program_name, sec.name as section_name
        FROM attendance a
        LEFT JOIN students s ON a.student_id = s.student_id
        LEFT JOIN sections sec ON s.section_id = sec.id
        LEFT JOIN programs p ON sec.program_id = p.id
        WHERE a.date = ?
        ORDER BY a.created_at DESC
    ");
    $stmt->execute([$today]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Input Sanitization
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Permission Check Functions
function hasPermission($requiredRole = 'admin') {
    initSession();
    if (!isset($_SESSION['role'])) {
        return false;
    }
    // Simple role check - can be extended for more complex permissions
    return $_SESSION['role'] === $requiredRole;
}

// Caching Functions
function getCache($key) {
    $cacheDir = __DIR__ . '/../cache';
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }
    $cacheFile = $cacheDir . '/' . md5($key) . '.cache';
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < 300)) { // 5 minutes
        return unserialize(file_get_contents($cacheFile));
    }
    return false;
}

function setCache($key, $data) {
    $cacheDir = __DIR__ . '/../cache';
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }
    $cacheFile = $cacheDir . '/' . md5($key) . '.cache';
    file_put_contents($cacheFile, serialize($data));
}

// Response Handling
function jsonResponse($success, $data = null, $message = '') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message
    ]);
    exit;
}
