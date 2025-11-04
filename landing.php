<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS - College Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            color: white;
        }
        .hero-content {
            text-align: center;
            max-width: 800px;
            margin: 0 auto;
        }
        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        .hero-subtitle {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin: 1rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-5px);
        }
        .feature-icon {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 1rem;
        }
        .stats-section {
            background: #f8f9fa;
            padding: 4rem 0;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #667eea;
        }
        .stat-label {
            color: #6c757d;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="landing.php">
                <i class="bi bi-mortarboard-fill me-2"></i>
                CMS
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-light ms-3" href="index.php">Admin Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">College Management System</h1>
                <p class="hero-subtitle">
                    Streamline your educational institution with our comprehensive management solution.
                    Manage students, attendance, programs, and more with ease.
                </p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="index.php" class="btn btn-light btn-lg px-4">
                        <i class="bi bi-box-arrow-in-right me-2"></i>
                        Admin Login
                    </a>
                    <a href="#features" class="btn btn-outline-light btn-lg px-4">
                        <i class="bi bi-info-circle me-2"></i>
                        Learn More
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-4 fw-bold">Powerful Features</h2>
                <p class="lead text-muted">Everything you need to manage your college efficiently</p>
            </div>
            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <h4 class="fw-bold">Student Management</h4>
                        <p class="text-muted">Comprehensive student records with program and section tracking</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <h4 class="fw-bold">Attendance Tracking</h4>
                        <p class="text-muted">Automated attendance recording with detailed analytics and trends</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="bi bi-bar-chart-line"></i>
                        </div>
                        <h4 class="fw-bold">Analytics Dashboard</h4>
                        <p class="text-muted">Real-time insights with interactive charts and comprehensive reporting</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h4 class="fw-bold">Secure Access</h4>
                        <p class="text-muted">Role-based permissions with secure authentication and session management</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="bi bi-gear"></i>
                        </div>
                        <h4 class="fw-bold">Program Management</h4>
                        <p class="text-muted">Organize programs and sections with dynamic relationships</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="bi bi-speedometer2"></i>
                        </div>
                        <h4 class="fw-bold">Performance Optimized</h4>
                        <p class="text-muted">Server-side caching ensures fast loading and optimal performance</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row text-center">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-number" id="totalStudents">12</div>
                    <div class="stat-label">Students Enrolled</div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-number" id="totalPrograms">6</div>
                    <div class="stat-label">Programs Offered</div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-number" id="totalSections">12</div>
                    <div class="stat-label">Sections Available</div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-number" id="attendanceRate">92%</div>
                    <div class="stat-label">Average Attendance</div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-5 bg-light">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h2 class="display-5 fw-bold mb-4">About Our System</h2>
                    <p class="lead mb-4">
                        Our College Management System is designed to simplify administrative tasks
                        and provide valuable insights for educational institutions.
                    </p>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> User-friendly interface</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> Real-time data updates</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> Comprehensive reporting</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> Mobile responsive design</li>
                    </ul>
                </div>
                <div class="col-lg-6">
                    <img src="https://via.placeholder.com/600x400/667eea/white?text=CMS+Dashboard" alt="CMS Dashboard" class="img-fluid rounded shadow">
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <h5 class="fw-bold">College Management System</h5>
                    <p class="mb-0">Empowering education through technology</p>
                </div>
                <div class="col-lg-6 text-lg-end">
                    <p class="mb-0">&copy; 2024 CMS. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Load stats from API if available
        fetch('api/attendance_summary.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('totalStudents').textContent = data.data.total_students;
                    // Calculate attendance rate
                    const total = data.data.todays_present + data.data.todays_absent;
                    if (total > 0) {
                        const rate = Math.round((data.data.todays_present / total) * 100);
                        document.getElementById('attendanceRate').textContent = rate + '%';
                    }
                }
            })
            .catch(() => {
                // Keep default values if API fails
            });
    </script>
</body>
</html>
