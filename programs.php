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
        header('Location: programs.php');
        exit;
    }

    $action = $_POST['action'];

    switch ($action) {
        case 'add':
            if (isset($_POST['name'])) {
                $data = [
                    'name' => sanitizeInput($_POST['name'])
                ];
                if (addProgram($data)) {
                    $_SESSION['success'] = 'Program added successfully';
                } else {
                    $_SESSION['error'] = 'Failed to add program';
                }
            }
            break;

        case 'update':
            if (isset($_POST['id'], $_POST['name'])) {
                $data = [
                    'name' => sanitizeInput($_POST['name'])
                ];
                if (updateProgram($_POST['id'], $data)) {
                    $_SESSION['success'] = 'Program updated successfully';
                } else {
                    $_SESSION['error'] = 'Failed to update program';
                }
            }
            break;

        case 'delete':
            if (isset($_POST['id'])) {
                if (deleteProgram($_POST['id'])) {
                    $_SESSION['success'] = 'Program deleted successfully';
                } else {
                    $_SESSION['error'] = 'Failed to delete program';
                }
            }
            break;
    }

    // Redirect to prevent form resubmission
    header('Location: programs.php');
    exit;
}

// Get all programs
$programs = getAllPrograms();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS - Programs</title>
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
                        <a class="nav-link active" href="programs.php">Programs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="sections.php">Sections</a>
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
                <h2>Program Management</h2>
            </div>
            <div class="col-md-6 text-end">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProgramModal">
                    <i class="bi bi-plus-circle"></i> Add New Program
                </button>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="input-group">
                    <input type="text" class="form-control" id="searchInput" placeholder="Search programs...">
                    <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
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
                    <table class="table table-hover" id="programsTable">
                        <thead class="table-light">
                            <tr>
                                <th onclick="sortTable(0)" style="cursor: pointer;">Program Name <i class="bi bi-arrow-down-up"></i></th>
                                <th>Sections Count</th>
                                <th>Students Count</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($programs)): ?>
                                <tr>
                                    <td colspan="4" class="text-center">No programs found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($programs as $program): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($program['name']); ?></td>
                                        <td>
                                            <span class="badge bg-info text-dark">
                                                <?php echo htmlspecialchars($program['sections_count'] ?? 0); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">
                                                <?php echo htmlspecialchars($program['students_count'] ?? 0); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editProgramModal"
                                                    data-id="<?php echo $program['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($program['name']); ?>">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteProgramModal"
                                                    data-id="<?php echo $program['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($program['name']); ?>">
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
                                        <div>Total Programs: <span class="badge bg-primary"><?php echo count($programs); ?></span></div>
                                    </div>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Program Modal -->
    <div class="modal fade" id="addProgramModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Program</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="post" class="needs-validation" novalidate>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                        <div class="mb-3">
                            <label for="name" class="form-label">Program Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-building"></i></span>
                                <input type="text" class="form-control" id="name" name="name"
                                    pattern="[A-Za-z\s]+" required
                                    title="Program name can only contain letters and spaces">
                                <div class="invalid-feedback">Please enter a valid program name</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Close
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Add Program
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Program Modal -->
    <div class="modal fade" id="editProgramModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Program</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="post">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" id="edit_id">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Program Name</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update Program</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Program Modal -->
    <div class="modal fade" id="deleteProgramModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Program</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="post">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="delete_id">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                        <p>Are you sure you want to delete <span id="delete_name"></span>? This will also delete all associated sections and students.</p>
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
        const tableBody = document.querySelector('#programsTable tbody');

        function filterTable() {
            const searchTerm = searchInput.value.toLowerCase();
            const rows = tableBody.getElementsByTagName('tr');

            Array.from(rows).forEach(row => {
                const name = row.cells[0]?.textContent.toLowerCase() || '';

                const matchesSearch = name.includes(searchTerm);

                row.style.display = matchesSearch ? '' : 'none';
            });
        }

        searchInput.addEventListener('keyup', filterTable);

        // Sort table
        function sortTable(columnIndex) {
            const table = document.getElementById('programsTable');
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

        // Handle edit program modal
        document.querySelectorAll('[data-bs-target="#editProgramModal"]').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');

                document.getElementById('edit_id').value = id;
                document.getElementById('edit_name').value = name;
            });
        });

        // Handle delete program modal
        document.querySelectorAll('[data-bs-target="#deleteProgramModal"]').forEach(button => {
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
