echo "<h3>Security Testing:</h3>";
    echo "<div style='background: #f0f0f0; padding: 15px; border-radius: 5px;'>";
    echo "<p><strong>Test Session Protection:</strong></p>";
    echo "<ol>";
    echo "<li>Try accessing <a href='admin_dashboard.php' target='_blank'>admin_dashboard.php</a> - Should redirect to login</li>";
    echo "<li>Try accessing <a href='student_dashboard.php' target='_blank'>student_dashboard.php</a> - Should redirect to login</li>";
    echo "<li>Login and then try logout - Should not be able to go back</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<h3>Next Steps:</h3>";
    echo "<ol>";
    echo "<li>If all tests pass, your system is ready!</li>";
    echo "<li>Access your login page at: <a href='index.php'>index.php</a></li>";
    echo "<li>Login as admin to manage students and questions</li>";
    echo "<li>Login as student to learn and take quizzes</li>";
    echo "<li>Test logout functionality to ensure security</li>";
    echo "</ol>";<?php
// test_connection.php
// Simple test script to verify database connection and setup

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "stem_learning_system";

echo "<h2>STEM Learning System - Connection Test</h2>";

try {
    // Test database connection
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✓ Database connection successful!</p>";
    
    // Test tables exist
    $tables = ['users', 'students', 'questions', 'quiz_results', 'quiz_answers'];
    echo "<h3>Checking Tables:</h3>";
    
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "<p style='color: green;'>✓ Table '$table' exists with $count records</p>";
        } catch (PDOException $e) {
            echo "<p style='color: red;'>✗ Table '$table' not found or error: " . $e->getMessage() . "</p>";
        }
    }
    
    // Test admin user
    echo "<h3>Checking Default Users:</h3>";
    $stmt = $pdo->prepare("SELECT username FROM users WHERE username = 'admin'");
    $stmt->execute();
    if ($stmt->fetch()) {
        echo "<p style='color: green;'>✓ Admin user exists</p>";
    } else {
        echo "<p style='color: red;'>✗ Admin user not found</p>";
    }
    
    // Test sample students
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM students");
    $stmt->execute();
    $student_count = $stmt->fetchColumn();
    echo "<p style='color: green;'>✓ Found $student_count sample students</p>";
    
    // Test sample questions
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM questions");
    $stmt->execute();
    $question_count = $stmt->fetchColumn();
    echo "<p style='color: green;'>✓ Found $question_count sample questions</p>";
    
    echo "<h3>System Status:</h3>";
    if ($question_count >= 10) {
        echo "<p style='color: green;'>✓ Sufficient questions for quiz (minimum 10 required)</p>";
    } else {
        echo "<p style='color: orange;'>⚠ Only $question_count questions found. Add more questions for proper quiz functionality.</p>";
    }
    
    echo "<h3>Test Login Credentials:</h3>";
    echo "<div style='background: #f0f0f0; padding: 15px; border-radius: 5px;'>";
    echo "<strong>Admin Login:</strong><br>";
    echo "Username: admin<br>";
    echo "Password: admin123<br><br>";
    echo "<strong>Sample Student Login:</strong><br>";
    echo "Username: david.mugabo<br>";
    echo "Password: admin123<br>";
    echo "</div>";
    
    echo "<h3>Testing File Structure:</h3>";
    
    $required_files = [
        'index.php' => 'Login page',
        'admin_dashboard.php' => 'Admin dashboard',
        'student_dashboard.php' => 'Student dashboard',
        'db_config.php' => 'Database configuration',
        'session_check.php' => 'Session protection',
        'style.css' => 'CSS styles'
    ];
    
    foreach ($required_files as $file => $description) {
        if (file_exists($file)) {
            echo "<p style='color: green;'>✓ $file ($description) - Found</p>";
        } else {
            echo "<p style='color: red;'>✗ $file ($description) - Missing</p>";
        }
    }
    
    echo "<h3>Testing Session Management:</h3>";
    
    // Test session functions
    if (file_exists('session_check.php')) {
        include_once 'session_check.php';
        echo "<p style='color: green;'>✓ Session protection loaded</p>";
        
        if (function_exists('checkLogin')) {
            echo "<p style='color: green;'>✓ Login check function available</p>";
        }
        
        if (function_exists('checkAdmin')) {
            echo "<p style='color: green;'>✓ Admin check function available</p>";
        }
        
        if (function_exists('checkStudent')) {
            echo "<p style='color: green;'>✓ Student check function available</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Session protection not found</p>";
    }
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</p>";
    echo "<h3>Troubleshooting:</h3>";
    echo "<ul>";
    echo "<li>Check if MySQL service is running</li>";
    echo "<li>Verify database name 'stem_learning_system' exists</li>";
    echo "<li>Check username and password in db_config.php</li>";
    echo "<li>Ensure database was created using the provided SQL script</li>";
    echo "</ul>";
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    background-color: #f5f5f5;
}

h2 {
    color: #333;
    border-bottom: 2px solid #4a90e2;
    padding-bottom: 10px;
}

h3 {
    color: #555;
    margin-top: 25px;
}

p {
    margin: 8px 0;
    font-size: 14px;
}

ol, ul {
    margin-left: 20px;
}

a {
    color: #4a90e2;
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}
</style>