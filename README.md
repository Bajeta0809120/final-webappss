# Classroom Management System (CMS)

A web-based Classroom Management System for managing student attendance and records.

## Features

- Secure Admin Authentication
- Student Management (CRUD operations)
- Attendance Tracking
- Dashboard with Analytics
- REST API Endpoints
- Responsive User Interface

## Setup Instructions

1. Clone the repository to your local XAMPP htdocs folder:
   ```bash
   git clone https://github.com/your-username/CMS.git
   ```

2. Import the database:
   - Open phpMyAdmin
   - Create a new database named 'cms_db'
   - Import the database.sql file

3. Configure database connection:
   - Open includes/db_connect.php
   - Update database credentials if needed

4. Access the application:
   - Open your browser
   - Navigate to http://localhost/CMS
   - Login with default credentials:
     - Username: admin
     - Password: admin123

## Database Structure

### Tables

1. admin
   - id (INT, AUTO_INCREMENT, PRIMARY KEY)
   - username (VARCHAR(50), UNIQUE)
   - password (VARCHAR(255))
   - created_at (TIMESTAMP)

2. students
   - id (INT, AUTO_INCREMENT, PRIMARY KEY)
   - student_id (VARCHAR(20), UNIQUE)
   - name (VARCHAR(100))
   - section (VARCHAR(50))
   - created_at (TIMESTAMP)

3. attendance
   - id (INT, AUTO_INCREMENT, PRIMARY KEY)
   - student_id (VARCHAR(20), FOREIGN KEY)
   - date (DATE)
   - status (ENUM: 'present', 'absent', 'late')
   - created_at (TIMESTAMP)

## PHP Functions

### Authentication
- validateLogin() - Verify admin credentials

### Student Management
- getAllStudents() - Retrieve all students
- addStudent() - Add new student
- updateStudent() - Update student information
- deleteStudent() - Remove student record

### Attendance Management
- recordAttendance() - Record student attendance
- getAttendanceByDate() - Get attendance records for a specific date

### Utilities
- connectDB() - Establish database connection
- sanitizeInput() - Clean user input
- jsonResponse() - Format API responses

## API Endpoints

1. GET /api/read.php
   - Retrieve all students
   - Returns JSON array of student records

2. POST /api/create.php
   - Add new student
   - Required fields: student_id, name, section

3. PUT /api/update.php
   - Update student information
   - Required fields: id, student_id, name, section

4. DELETE /api/delete.php
   - Remove student record
   - Required field: id

## Technologies Used

- PHP 7.4+
- MySQL 5.7+
- HTML5
- CSS3 (Bootstrap 5)
- JavaScript
- XAMPP Server

## Security Features

- Password Hashing
- Input Sanitization
- Prepared Statements
- Session Management
- CSRF Protection