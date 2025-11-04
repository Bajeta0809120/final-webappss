<?php
require_once 'includes/session_helper.php';
require_once 'includes/functions.php';

// Initialize session
initSession();

// Check if user is logged in
if (!validateSession()) {
    header('Location: index.php');
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // CSRF protection
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        $_SESSION['error'] = 'Invalid CSRF token. Please try again.';
        header('Location: students.php');
        exit;
    }

    $action = $_POST['action'];
    
    switch ($action) {
        case 'add':
            if (isset($_POST['student_id'], $_POST['name'], $_POST['program'], $_POST['section'])) {
                $studentId = sanitizeInput($_POST['student_id']);

                // Check if student ID already exists
                if (studentIdExists($studentId)) {
                    $_SESSION['error'] = 'Student ID already exists. Please use a different Student ID.';
                    break;
                }

                $programId = (int)$_POST['program'];
                $sectionId = (int)$_POST['section'];

                if ($programId && $sectionId) {
                    // Validate that the section belongs to the selected program
                    $conn = connectDB();
                    $stmt = $conn->prepare("SELECT id FROM sections WHERE id = ? AND program_id = ?");
                    $stmt->execute([$sectionId, $programId]);
                    if ($stmt->fetch()) {
                        $data = [
                            'student_id' => $studentId,
                            'name' => sanitizeInput($_POST['name']),
                            'section_id' => $sectionId
                        ];
                        if (addStudent($data)) {
                            $_SESSION['success'] = 'Student added successfully';
                        } else {
                            $_SESSION['error'] = 'Failed to add student';
                        }
                    } else {
                        $_SESSION['error'] = 'Invalid section selected for the program';
                    }
                } else {
                    $_SESSION['error'] = 'Invalid program or section';
                }
            }
            break;
            
        case 'update':
            if (isset($_POST['id'], $_POST['student_id'], $_POST['name'], $_POST['program'], $_POST['section'])) {
                $studentId = sanitizeInput($_POST['student_id']);
                $currentStudent = getStudentById($_POST['id']);

                // Check if student ID is being changed and if it already exists
                if ($currentStudent && $currentStudent['student_id'] !== $studentId && studentIdExists($studentId)) {
                    $_SESSION['error'] = 'Student ID already exists. Please use a different Student ID.';
                    break;
                }

                $programId = (int)$_POST['program'];
                $sectionId = (int)$_POST['section'];

                if ($programId && $sectionId) {
                    // Validate that the section belongs to the selected program
                    $conn = connectDB();
                    $stmt = $conn->prepare("SELECT id FROM sections WHERE id = ? AND program_id = ?");
                    $stmt->execute([$sectionId, $programId]);
                    if ($stmt->fetch()) {
                        $data = [
                            'student_id' => $studentId,
                            'name' => sanitizeInput($_POST['name']),
                            'section_id' => $sectionId
                        ];
                        if (updateStudent($_POST['id'], $data)) {
                            $_SESSION['success'] = 'Student updated successfully';
                        } else {
                            $_SESSION['error'] = 'Failed to update student';
                        }
                    } else {
                        $_SESSION['error'] = 'Invalid section selected for the program';
                    }
                } else {
                    $_SESSION['error'] = 'Invalid program or section';
                }
            }
            break;
            
        case 'delete':
            if (isset($_POST['id'])) {
                if (deleteStudent($_POST['id'])) {
                    $_SESSION['success'] = 'Student deleted successfully';
                } else {
                    $_SESSION['error'] = 'Failed to delete student';
                }
            }
            break;
    }
    
    // Redirect to prevent form resubmission
    header('Location: students.php');
    exit;
}

// Get all students
$students = getAllStudents();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS - Students</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .table th {
            background-color: #f8f9fa;
        }
        .badge {
            font-size: 0.9em;
        }
        .btn-group {
            gap: 5px;
        }
        .table tbody tr:hover {
            background-color: #f8f9fa;
        }
        .sorting-header {
            cursor: pointer;
        }
        .sorting-header:hover {
            background-color: #e9ecef;
        }
        .input-group-text {
            background-color: #f8f9fa;
        }
        .form-control:focus, .form-select:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">CMS</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul  class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="students.php">Students</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="attendance.php">Attendance</a>
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
        <div class="row mb-4">
            <div class="col-md-6">
                <h2>Student Management</h2>
            </div>
            <div class="col-md-6 text-end">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                    <i class="bi bi-plus-circle"></i> Add New Student
                </button>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="input-group">
                    <input type="text" class="form-control" id="searchInput" placeholder="Search students...">
                    <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="sectionFilter">
                    <option value="">All Sections</option>
                    <?php
                    $sections = array_unique(array_column($students, 'section_name'));
                    foreach($sections as $section):
                    ?>
                        <option value="<?php echo htmlspecialchars($section); ?>">
                            <?php echo htmlspecialchars($section); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                echo htmlspecialchars($_SESSION['success']);
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo htmlspecialchars($_SESSION['error']);
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="studentsTable">
                        <thead class="table-light">
                            <tr>
                                <th onclick="sortTable(0)" style="cursor: pointer;">Student ID <i class="bi bi-arrow-down-up"></i></th>
                                <th onclick="sortTable(1)" style="cursor: pointer;">Name <i class="bi bi-arrow-down-up"></i></th>
                                <th onclick="sortTable(2)" style="cursor: pointer;">Program <i class="bi bi-arrow-down-up"></i></th>
                                <th onclick="sortTable(3)" style="cursor: pointer;">Section <i class="bi bi-arrow-down-up"></i></th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($students)): ?>
                                <tr>
                                    <td colspan="5" class="text-center">No students found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($students as $student): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                        <td><?php echo htmlspecialchars($student['name']); ?></td>
                                        <td>
                                            <span class="badge bg-primary">
                                                <?php echo htmlspecialchars($student['program_name'] ?? 'N/A'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info text-dark">
                                                <?php echo htmlspecialchars($student['section_name'] ?? 'N/A'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editStudentModal"
                                                    data-id="<?php echo $student['id']; ?>"
                                                    data-student-id="<?php echo htmlspecialchars($student['student_id']); ?>"
                                                    data-name="<?php echo htmlspecialchars($student['name']); ?>"
                                                    data-program="<?php echo htmlspecialchars($student['program_id'] ?? ''); ?>"
                                                    data-section="<?php echo htmlspecialchars($student['section_id'] ?? ''); ?>">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteStudentModal"
                                                    data-id="<?php echo $student['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($student['name']); ?>">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>Total Students: <span class="badge bg-primary"><?php echo count($students); ?></span></div>
                                    </div>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Student Modal -->
    <div class="modal fade" id="addStudentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="post" class="needs-validation" novalidate>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                        <div class="mb-3">
                            <label for="student_id" class="form-label">Student ID <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                <input type="text" class="form-control" id="student_id" name="student_id"
                                    pattern="[A-Za-z0-9\-]+" required
                                    title="Student ID can only contain letters, numbers, and hyphens">
                                <div class="invalid-feedback">Please enter a valid Student ID</div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control" id="name" name="name" 
                                    pattern="[A-Za-z\s]+" required
                                    title="Name can only contain letters and spaces">
                                <div class="invalid-feedback">Please enter a valid name</div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="program" class="form-label">Program <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-building"></i></span>
                                <select class="form-select" id="program" name="program" required>
                                    <option value="">Choose program...</option>
                                    <?php
                                    $programs = getAllPrograms();
                                    foreach($programs as $program):
                                    ?>
                                        <option value="<?php echo $program['id']; ?>"><?php echo htmlspecialchars($program['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Please select a program</div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="section" class="form-label">Section <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-diagram-2"></i></span>
                                <select class="form-select" id="section" name="section" required>
                                    <option value="">Choose section...</option>
                                </select>
                                <a href="sections.php" target="_blank" class="btn btn-outline-secondary" title="Manage Sections">
                                    <i class="bi bi-plus-circle"></i>
                                </a>
                                <div class="invalid-feedback">Please select a section</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Close
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Add Student
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Student Modal -->
    <div class="modal fade" id="editStudentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="post">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" id="edit_id">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                        <div class="mb-3">
                            <label for="edit_student_id" class="form-label">Student ID <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                <input type="text" class="form-control" id="edit_student_id" name="student_id"
                                    pattern="[A-Za-z0-9\-]+" required
                                    title="Student ID can only contain letters, numbers, and hyphens">
                                <div class="invalid-feedback">Please enter a valid Student ID</div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control" id="edit_name" name="name"
                                    pattern="[A-Za-z\s]+" required
                                    title="Name can only contain letters and spaces">
                                <div class="invalid-feedback">Please enter a valid name</div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_program" class="form-label">Program <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-building"></i></span>
                                <select class="form-select" id="edit_program" name="program" required>
                                    <option value="">Choose program...</option>
                                    <?php
                                    $programs = getAllPrograms();
                                    foreach($programs as $program):
                                    ?>
                                        <option value="<?php echo $program['id']; ?>"><?php echo htmlspecialchars($program['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Please select a program</div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_section" class="form-label">Section <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-diagram-2"></i></span>
                                <select class="form-select" id="edit_section" name="section" required>
                                    <option value="">Choose section...</option>
                                </select>
                                <a href="sections.php" target="_blank" class="btn btn-outline-secondary" title="Manage Sections">
                                    <i class="bi bi-plus-circle"></i>
                                </a>
                                <div class="invalid-feedback">Please select a section</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Close
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-pencil"></i> Update Student
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Student Modal -->
    <div class="modal fade" id="deleteStudentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="post">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="delete_id">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                        <p>Are you sure you want to delete <span id="delete_name"></span>?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        document.querySelectorAll('.needs-validation').forEach(form => {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            });
        });

        // Search functionality
        const searchInput = document.getElementById('searchInput');
        const sectionFilter = document.getElementById('sectionFilter');
        const tableBody = document.querySelector('#studentsTable tbody');

        function filterTable() {
            const searchTerm = searchInput.value.toLowerCase();
            const selectedSection = sectionFilter.value.toLowerCase();
            const rows = tableBody.getElementsByTagName('tr');

            Array.from(rows).forEach(row => {
                const studentId = row.cells[0]?.textContent.toLowerCase() || '';
                const name = row.cells[1]?.textContent.toLowerCase() || '';
                const section = row.cells[2]?.textContent.toLowerCase() || '';

                const matchesSearch = studentId.includes(searchTerm) ||
                                    name.includes(searchTerm) ||
                                    section.includes(searchTerm);
                const matchesSection = !selectedSection || section.includes(selectedSection);

                row.style.display = (matchesSearch && matchesSection) ? '' : 'none';
            });
        }

        searchInput.addEventListener('keyup', filterTable);
        sectionFilter.addEventListener('change', filterTable);

        // Sort table
        function sortTable(columnIndex) {
            const table = document.getElementById('studentsTable');
            const rows = Array.from(table.querySelectorAll('tbody tr'));
            const isNumeric = columnIndex === 0; // Student ID column

            rows.sort((a, b) => {
                const aValue = a.cells[columnIndex].textContent.trim();
                const bValue = b.cells[columnIndex].textContent.trim();

                if (isNumeric) {
                    return aValue.localeCompare(bValue, undefined, {numeric: true});
                }
                return aValue.localeCompare(bValue);
            });

            // Toggle sort direction
            if (table.dataset.sortDirection === 'asc') {
                rows.reverse();
                table.dataset.sortDirection = 'desc';
            } else {
                table.dataset.sortDirection = 'asc';
            }

            // Update table
            const tbody = table.querySelector('tbody');
            tbody.innerHTML = '';
            rows.forEach(row => tbody.appendChild(row));
        }

        // (export removed)

        // Load sections based on selected program
        function loadSections(programSelectId, sectionSelectId) {
            const programSelect = document.getElementById(programSelectId);
            const sectionSelect = document.getElementById(sectionSelectId);

            programSelect.addEventListener('change', function() {
                const programId = this.value;
                sectionSelect.innerHTML = '<option value="">Choose section...</option>';

                if (programId) {
                    fetch('api/read.php?type=sections_by_program&program_id=' + programId)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                data.data.forEach(section => {
                                    const option = document.createElement('option');
                                    option.value = section.id;
                                    option.textContent = section.name;
                                    sectionSelect.appendChild(option);
                                });
                            }
                        })
                        .catch(error => console.error('Error loading sections:', error));
                }
            });
        }

        // Initialize section loading for add and edit modals
        loadSections('program', 'section');
        loadSections('edit_program', 'edit_section');

        // Load sections when add student modal is shown
        document.getElementById('addStudentModal').addEventListener('show.bs.modal', function() {
            fetch('api/read.php?type=sections')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const sectionSelect = document.getElementById('section');
                        sectionSelect.innerHTML = '<option value="">Choose section...</option>';
                        data.data.forEach(section => {
                            const option = document.createElement('option');
                            option.value = section.id;
                            option.textContent = section.name;
                            sectionSelect.appendChild(option);
                        });
                    } else {
                        console.error('Failed to load sections:', data.message);
                    }
                })
                .catch(error => console.error('Error loading sections:', error));
        });

        // Handle edit student modal
        document.querySelectorAll('[data-bs-target="#editStudentModal"]').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const studentId = this.getAttribute('data-student-id');
                const name = this.getAttribute('data-name');
                const program = this.getAttribute('data-program');
                const section = this.getAttribute('data-section');

                document.getElementById('edit_id').value = id;
                document.getElementById('edit_student_id').value = studentId;
                document.getElementById('edit_name').value = name;
                document.getElementById('edit_program').value = program;

                // Load sections for the selected program and set the selected section
                const editSectionSelect = document.getElementById('edit_section');
                editSectionSelect.innerHTML = '<option value="">Choose section...</option>';

                if (program) {
                    fetch('api/read.php?type=sections_by_program&program_id=' + program)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                data.data.forEach(sec => {
                                    const option = document.createElement('option');
                                    option.value = sec.id;
                                    option.textContent = sec.name;
                                    editSectionSelect.appendChild(option);
                                });
                            }
                            // Set the selected section after loading options
                            document.getElementById('edit_section').value = section;
                        })
                        .catch(error => console.error('Error loading sections:', error));
                }
            });
        });

        // Handle delete student modal
        document.querySelectorAll('[data-bs-target="#deleteStudentModal"]').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');

                document.getElementById('delete_id').value = id;
                document.getElementById('delete_name').textContent = name;
            });
        });
    </script>
</body>
</html>