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
        header('Location: sections.php');
        exit;
    }

    $action = $_POST['action'];

    switch ($action) {
        case 'add':
            if (isset($_POST['name'], $_POST['program_id'])) {
                $data = [
                    'name' => sanitizeInput($_POST['name']),
                    'program_id' => (int)$_POST['program_id']
                ];
                if (addSection($data)) {
                    $_SESSION['success'] = 'Section added successfully';
                } else {
                    $_SESSION['error'] = 'Failed to add section';
                }
            }
            break;

        case 'update':
            if (isset($_POST['id'], $_POST['name'], $_POST['program_id'])) {
                $data = [
                    'name' => sanitizeInput($_POST['name']),
                    'program_id' => (int)$_POST['program_id']
                ];
                if (updateSection($_POST['id'], $data)) {
                    $_SESSION['success'] = 'Section updated successfully';
                } else {
                    $_SESSION['error'] = 'Failed to update section';
                }
            }
            break;

        case 'delete':
            if (isset($_POST['id'])) {
                if (deleteSection($_POST['id'])) {
                    $_SESSION['success'] = 'Section deleted successfully';
                } else {
                    $_SESSION['error'] = 'Failed to delete section';
                }
            }
            break;
    }

    // Redirect to prevent form resubmission
    header('Location: sections.php');
    exit;
}

// Get all sections
$sections = getAllSections();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS - Sections</title>
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
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="students.php">Students</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="programs.php">Programs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="sections.php">Sections</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="attendance.php">Attendance</a>
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
                <h2>Section Management</h2>
            </div>
            <div class="col-md-6 text-end">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSectionModal">
                    <i class="bi bi-plus-circle"></i> Add New Section
                </button>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="input-group">
                    <input type="text" class="form-control" id="searchInput" placeholder="Search sections...">
                    <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="programFilter">
                    <option value="">All Programs</option>
                    <?php
                    $programs = getAllPrograms();
                    foreach($programs as $program):
                    ?>
                        <option value="<?php echo $program['id']; ?>">
                            <?php echo htmlspecialchars($program['name']); ?>
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
                    <table class="table table-hover" id="sectionsTable">
                        <thead class="table-light">
                            <tr>
                                <th onclick="sortTable(0)" style="cursor: pointer;">Section Name <i class="bi bi-arrow-down-up"></i></th>
                                <th onclick="sortTable(1)" style="cursor: pointer;">Program <i class="bi bi-arrow-down-up"></i></th>
                                <th>Students Count</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($sections)): ?>
                                <tr>
                                    <td colspan="4" class="text-center">No sections found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($sections as $section): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($section['name']); ?></td>
                                        <td>
                                            <span class="badge bg-primary">
                                                <?php echo htmlspecialchars($section['program_name'] ?? 'N/A'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">
                                                <?php echo htmlspecialchars($section['students_count'] ?? 0); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editSectionModal"
                                                    data-id="<?php echo $section['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($section['name']); ?>"
                                                    data-program-id="<?php echo $section['program_id']; ?>">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteSectionModal"
                                                    data-id="<?php echo $section['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($section['name']); ?>">
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
                                <td colspan="4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>Total Sections: <span class="badge bg-primary"><?php echo count($sections); ?></span></div>
                                    </div>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Section Modal -->
    <div class="modal fade" id="addSectionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Section</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="post" class="needs-validation" novalidate>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                        <div class="mb-3">
                            <label for="name" class="form-label">Section Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-diagram-2"></i></span>
                                <input type="text" class="form-control" id="name" name="name"
                                    pattern="[A-Za-z0-9\s]+" required
                                    title="Section name can only contain letters, numbers, and spaces">
                                <div class="invalid-feedback">Please enter a valid section name</div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="program_id" class="form-label">Program <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-building"></i></span>
                                <select class="form-select" id="program_id" name="program_id" required>
                                    <option value="">Choose program...</option>
                                    <?php
                                    foreach($programs as $program):
                                    ?>
                                        <option value="<?php echo $program['id']; ?>"><?php echo htmlspecialchars($program['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Please select a program</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Close
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Add Section
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Section Modal -->
    <div class="modal fade" id="editSectionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Section</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="post">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" id="edit_id">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Section Name</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_program_id" class="form-label">Program</label>
                            <select class="form-select" id="edit_program_id" name="program_id" required>
                                <?php
                                foreach($programs as $program):
                                ?>
                                    <option value="<?php echo $program['id']; ?>"><?php echo htmlspecialchars($program['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update Section</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Section Modal -->
    <div class="modal fade" id="deleteSectionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Section</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="post">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="delete_id">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                        <p>Are you sure you want to delete <span id="delete_name"></span>? This will also remove all associated students.</p>
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
        const programFilter = document.getElementById('programFilter');
        const tableBody = document.querySelector('#sectionsTable tbody');

        function filterTable() {
            const searchTerm = searchInput.value.toLowerCase();
            const selectedProgram = programFilter.value;
            const rows = tableBody.getElementsByTagName('tr');

            Array.from(rows).forEach(row => {
                const name = row.cells[0]?.textContent.toLowerCase() || '';
                const programId = row.querySelector('[data-program-id]')?.getAttribute('data-program-id') || '';

                const matchesSearch = name.includes(searchTerm);
                const matchesProgram = !selectedProgram || programId === selectedProgram;

                row.style.display = (matchesSearch && matchesProgram) ? '' : 'none';
            });
        }

        searchInput.addEventListener('keyup', filterTable);
        programFilter.addEventListener('change', filterTable);

        // Sort table
        function sortTable(columnIndex) {
            const table = document.getElementById('sectionsTable');
            const rows = Array.from(table.querySelectorAll('tbody tr'));

            rows.sort((a, b) => {
                const aValue = a.cells[columnIndex].textContent.trim();
                const bValue = b.cells[columnIndex].textContent.trim();

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

        // Handle edit section modal
        document.querySelectorAll('[data-bs-target="#editSectionModal"]').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const programId = this.getAttribute('data-program-id');

                document.getElementById('edit_id').value = id;
                document.getElementById('edit_name').value = name;
                document.getElementById('edit_program_id').value = programId;
            });
        });

        // Handle delete section modal
        document.querySelectorAll('[data-bs-target="#deleteSectionModal"]').forEach(button => {
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
