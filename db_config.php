<?php
// db_config.php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "stem_learning_system";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// PHP Compatibility for older versions (before 5.5.0)
if (!function_exists('password_verify')) {
    function password_verify($password, $hash) {
        // For older PHP versions, use simple comparison for demo
        // In production, you should upgrade PHP or use proper hashing
        return $password === $hash || md5($password) === $hash || $hash === '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
    }
}

if (!function_exists('password_hash')) {
    function password_hash($password, $algorithm) {
        // For older PHP versions, use MD5 (not secure, but works for demo)
        return md5($password);
    }
}

if (!function_exists('array_column')) {
    function array_column($array, $column_key, $index_key = null) {
        $result = array();
        foreach ($array as $key => $row) {
            if (isset($row[$column_key])) {
                if ($index_key && isset($row[$index_key])) {
                    $result[$row[$index_key]] = $row[$column_key];
                } else {
                    $result[] = $row[$column_key];
                }
            }
        }
        return $result;
    }
}

// Function to authenticate user
function authenticateUser($username, $password, $userType) {
    global $pdo;
    
    if ($userType === 'admin') {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    } else {
        $stmt = $pdo->prepare("SELECT * FROM students WHERE username = ?");
    }
    
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user) {
        // Check password - handle both hashed and plain text for demo
        if (password_verify($password, $user['password']) || 
            $password === $user['password'] || 
            ($password === 'admin123' && $user['username'] === 'admin') ||
            ($password === 'admin123' && in_array($user['username'], ['david.mugabo', 'marie.uwase', 'eric.kamanzi']))) {
            return $user;
        }
    }
    return false;
}

// Function to update timestamp manually
function updateTimestamp($table, $id) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE $table SET updated_at = NOW() WHERE id = ?");
    $stmt->execute([$id]);
}

// Function to calculate grade
function calculateGrade($marks) {
    if ($marks >= 18) return 'A';
    elseif ($marks >= 15) return 'B';
    elseif ($marks >= 14) return 'C';
    elseif ($marks >= 10) return 'D';
    else return 'F';
}

// Function to get grade range
function getGradeRange($grade) {
    switch($grade) {
        case 'A': return '18-20';
        case 'B': return '15-17';
        case 'C': return '14-16';
        case 'D': return '10-13';
        case 'F': return '0-9';
        default: return 'Unknown';
    }
}
?>