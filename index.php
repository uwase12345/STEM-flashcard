<?php
require_once 'db_config.php';

$error_message = "";

// If user is already logged in, redirect to appropriate dashboard
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_type'] === 'admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: student_dashboard.php");
    }
    exit();
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $userType = $_POST['userType'];
    
    $user = authenticateUser($username, $password, $userType);
    
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_type'] = $userType;
        
        if ($userType === 'admin') {
            $_SESSION['firstname'] = 'Admin';
            $_SESSION['lastname'] = '';
            header("Location: admin_dashboard.php");
        } else {
            $_SESSION['firstname'] = $user['firstname'];
            $_SESSION['lastname'] = $user['lastname'];
            header("Location: student_dashboard.php");
        }
        exit();
    } else {
        $error_message = "Incorrect username or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STEM Learning System - Login</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .alert {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            text-align: center;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <!-- Login Screen -->
    <div id="login-screen">
        <div class="container">
            <div class="login-container">
                <h2 id="login-title">Ikaze (Welcome)!</h2>
                <p id="login-subtitle" class="form-footer">Please login to continue your STEM learning journey</p>
                
                <?php if ($error_message): ?>
                    <div class="alert alert-error"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <div class="role-toggle">
                    <div class="role-option active" data-role="student" id="student-toggle">Student</div>
                    <div class="role-option" data-role="admin" id="admin-toggle">Admin</div>
                </div>
                
                <form method="POST">
                    <input type="hidden" name="userType" id="userType" value="student">
                    <div class="form-group">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" name="username" id="username" class="form-input" placeholder="Enter your username" required>
                    </div>
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" name="password" id="password" class="form-input" placeholder="Enter your password" required>
                    </div>
                    <button type="submit" name="login" class="btn-login">Login</button>
                </form>
                
                <div class="form-footer">
                   <p id="demo-login">Try demo: <a href="#" id="demo-student">Student</a> | <a href="#" id="demo-teacher">Teacher</a></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('student-toggle').addEventListener('click', function() {
            document.getElementById('userType').value = 'student';
            this.classList.add('active');
            document.getElementById('admin-toggle').classList.remove('active');
        });
        
        document.getElementById('admin-toggle').addEventListener('click', function() {
            document.getElementById('userType').value = 'admin';
            this.classList.add('active');
            document.getElementById('student-toggle').classList.remove('active');
        });
    </script>
</body>
</html>