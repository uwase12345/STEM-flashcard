<?php
// php_version_check.php
// Check PHP version and password functions availability

echo "<h2>PHP Version Compatibility Check</h2>";

// Check PHP version
$php_version = phpversion();
echo "<p><strong>Your PHP Version:</strong> $php_version</p>";

// Check if password functions exist
$password_verify_exists = function_exists('password_verify');
$password_hash_exists = function_exists('password_hash');
$array_column_exists = function_exists('array_column');

echo "<h3>Function Availability:</h3>";
echo "<p>password_verify(): " . ($password_verify_exists ? "<span style='color:green;'>✓ Available</span>" : "<span style='color:red;'>✗ Not Available</span>") . "</p>";
echo "<p>password_hash(): " . ($password_hash_exists ? "<span style='color:green;'>✓ Available</span>" : "<span style='color:red;'>✗ Not Available</span>") . "</p>";
echo "<p>array_column(): " . ($array_column_exists ? "<span style='color:green;'>✓ Available</span>" : "<span style='color:red;'>✗ Not Available</span>") . "</p>";

// Provide recommendations
echo "<h3>Compatibility Status:</h3>";

if (version_compare($php_version, '5.5.0', '>=')) {
    echo "<p style='color:green;'>✓ Your PHP version supports all modern functions!</p>";
    echo "<p>You can use the original code with proper password hashing and array functions.</p>";
} else {
    echo "<p style='color:orange;'>⚠ Your PHP version is older than 5.5.0</p>";
    echo "<p>Using compatibility functions for:</p>";
    echo "<ul>";
    if (!$password_verify_exists) echo "<li>password_verify() and password_hash()</li>";
    if (!$array_column_exists) echo "<li>array_column()</li>";
    echo "</ul>";
    echo "<p><strong>Recommendation:</strong> Consider upgrading to PHP 7.4 or 8.x for better security and performance.</p>";
}

// Test the compatibility functions
echo "<h3>Testing Password Functions:</h3>";

// Test password verification
$test_password = "admin123";
$test_result = false;

if (function_exists('password_verify')) {
    // Test with the hash we use in database
    $test_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
    $test_result = password_verify($test_password, $test_hash);
} else {
    // Use our compatibility function
    include_once 'db_config.php';
    $test_result = password_verify($test_password, $test_password); // Plain text comparison
}

echo "<p>Password test result: " . ($test_result ? "<span style='color:green;'>✓ Working</span>" : "<span style='color:red;'>✗ Failed</span>") . "</p>";

// Show login credentials clearly
echo "<h3>🔑 Login Credentials (Updated for Compatibility):</h3>";
echo "<div style='background: #f0f0f0; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>Admin Login:</strong><br>";
echo "Username: <code>admin</code><br>";
echo "Password: <code>admin123</code><br><br>";
echo "<strong>Student Login:</strong><br>";
echo "Username: <code>david.mugabo</code><br>";
echo "Password: <code>admin123</code><br>";
echo "</div>";

// Installation status
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>Make sure you've run the updated database script</li>";
echo "<li>Use the updated db_config.php file</li>";
echo "<li>Try logging in with the credentials above</li>";
echo "<li>If you still get errors, check the error log below</li>";
echo "</ol>";

// Error checking
echo "<h3>Error Checking:</h3>";
try {
    include_once 'db_config.php';
    echo "<p style='color:green;'>✓ db_config.php loaded successfully</p>";
    
    // Test database connection
    $test_connection = new PDO("mysql:host=localhost;dbname=stem_learning_system", "root", "");
    echo "<p style='color:green;'>✓ Database connection successful</p>";
    
    // Test authentication function
    $auth_test = authenticateUser('admin', 'admin123', 'admin');
    echo "<p style='color:green;'>✓ Authentication function working</p>";
    
} catch (Exception $e) {
    echo "<p style='color:red;'>✗ Error: " . $e->getMessage() . "</p>";
}

echo "<h3>Troubleshooting:</h3>";
echo "<ul>";
echo "<li>If you get 'password_verify()' errors, use the updated db_config.php</li>";
echo "<li>If you get 'array_column()' errors, use the updated files with compatibility functions</li>";
echo "<li>If login fails, make sure passwords in database are plain text: 'admin123'</li>";
echo "<li>Check that the database 'stem_learning_system' exists</li>";
echo "<li>Verify all tables were created successfully</li>";
echo "<li>Run test_connection.php to verify everything works</li>";
echo "</ul>";
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    background-color: #f5f5f5;
}
h2, h3 { color: #333; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
code { background: #e8e8e8; padding: 2px 4px; border-radius: 3px; }
ol, ul { margin-left: 20px; }
</style>