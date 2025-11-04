<?php
require_once 'includes/db_connect.php';

try {
    $conn = connectDB();

    // Read the SQL file
    $sql = file_get_contents('database.sql');

    // Split into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^--/', $statement)) {
            $conn->exec($statement);
        }
    }

    echo "Database populated successfully with sample data!";
} catch (Exception $e) {
    echo "Error populating database: " . $e->getMessage();
}
?>
