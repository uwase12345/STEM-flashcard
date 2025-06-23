<?php
require_once 'db_config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$error_message = "";
$success_message = "";

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// Handle student registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_student'])) {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    $username = $_POST['reg_username'];
    $password = $_POST['reg_password'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO students (firstname, lastname, gender, dob, username, password, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$firstname, $lastname, $gender, $dob, $username, $password]);
        $success_message = "Student registered successfully!";
    } catch (PDOException $e) {
        $error_message = "Error registering student: " . $e->getMessage();
    }
}

// Handle add question
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_question'])) {
    $question_text = $_POST['question_text'];
    $option_a = $_POST['option_a'];
    $option_b = $_POST['option_b'];
    $option_c = $_POST['option_c'];
    $option_d = $_POST['option_d'];
    $correct_answer = $_POST['correct_answer'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO questions (question_text, option_a, option_b, option_c, option_d, correct_answer, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$question_text, $option_a, $option_b, $option_c, $option_d, $correct_answer]);
        $success_message = "Question added successfully!";
    } catch (PDOException $e) {
        $error_message = "Error adding question: " . $e->getMessage();
    }
}

// Handle update question
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_question'])) {
    $question_id = $_POST['question_id'];
    $question_text = $_POST['question_text'];
    $option_a = $_POST['option_a'];
    $option_b = $_POST['option_b'];
    $option_c = $_POST['option_c'];
    $option_d = $_POST['option_d'];
    $correct_answer = $_POST['correct_answer'];
    
    try {
        $stmt = $pdo->prepare("UPDATE questions SET question_text=?, option_a=?, option_b=?, option_c=?, option_d=?, correct_answer=?, updated_at=NOW() WHERE id=?");
        $stmt->execute([$question_text, $option_a, $option_b, $option_c, $option_d, $correct_answer, $question_id]);
        $success_message = "Question updated successfully!";
    } catch (PDOException $e) {
        $error_message = "Error updating question: " . $e->getMessage();
    }
}

// Handle delete student
if (isset($_GET['delete_student'])) {
    $student_id = $_GET['delete_student'];
    try {
        $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
        $stmt->execute([$student_id]);
        $success_message = "Student deleted successfully!";
    } catch (PDOException $e) {
        $error_message = "Error deleting student: " . $e->getMessage();
    }
}

// Get all students
$stmt = $pdo->prepare("SELECT * FROM students ORDER BY lastname, firstname");
$stmt->execute();
$students = $stmt->fetchAll();

// Get all questions
$stmt = $pdo->prepare("SELECT * FROM questions ORDER BY id");
$stmt->execute();
$questions = $stmt->fetchAll();

// Get quiz results with student names
$stmt = $pdo->prepare("SELECT qr.*, s.firstname, s.lastname FROM quiz_results qr JOIN students s ON qr.student_id = s.id ORDER BY qr.quiz_date DESC");
$stmt->execute();
$quiz_results = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - STEM Learning System</title>
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
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .admin-section {
            margin: 20px 0;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .form-row {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }
        .form-row input, .form-row select, .form-row textarea {
            flex: 1;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .btn {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            margin: 5px;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .btn-danger {
            background-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .table th {
            background-color: #f8f9fa;
        }
        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background-color: #dc3545;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 4px;
        }
        .grade-A { color: #28a745; }
        .grade-B { color: #17a2b8; }
        .grade-C { color: #ffc107; }
        .grade-D { color: #fd7e14; }
        .grade-F { color: #dc3545; }
    </style>
</head>
<body>
    <div id="app-screen">
        <a href="?logout=1" class="logout-btn">Logout</a>
        
        <header>
            <h1>Admin Dashboard - STEM Learning System</h1>
        </header>

        <div class="container">
            <?php if ($success_message): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <div class="user-profile">
                <div class="avatar">A</div>
                <div class="user-info">
                    <h2>Admin - <?php echo $_SESSION['username']; ?></h2>
                    <p>Administrator - Kalisimbi Valley Academy</p>
                </div>
            </div>

            <div class="tabs">
                <div class="tab active" onclick="showTab('students')">Manage Students</div>
                <div class="tab" onclick="showTab('questions')">Manage Questions</div>
                <div class="tab" onclick="showTab('results')">View Results</div>
                <div class="tab" onclick="showTab('reports')">Generate Reports</div>
            </div>

            <!-- Students Management -->
            <div id="students-tab" class="tab-content active">
                <div class="admin-section">
                    <h3>Register New Student</h3>
                    <form method="POST">
                        <div class="form-row">
                            <input type="text" name="firstname" placeholder="First Name" required>
                            <input type="text" name="lastname" placeholder="Last Name" required>
                            <select name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="form-row">
                            <input type="date" name="dob" required>
                            <input type="text" name="reg_username" placeholder="Username" required>
                            <input type="password" name="reg_password" placeholder="Password" required>
                        </div>
                        <button type="submit" name="register_student" class="btn">Register Student</button>
                    </form>
                </div>

                <div class="admin-section">
                    <h3>All Students (<?php echo count($students); ?> total)</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Gender</th>
                                <th>Date of Birth</th>
                                <th>Username</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo $student['firstname'] . ' ' . $student['lastname']; ?></td>
                                <td><?php echo $student['gender']; ?></td>
                                <td><?php echo $student['dob']; ?></td>
                                <td><?php echo $student['username']; ?></td>
                                <td>
                                    <a href="?delete_student=<?php echo $student['id']; ?>" 
                                       onclick="return confirm('Are you sure you want to delete this student?')" 
                                       class="btn btn-danger">Delete</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Questions Management -->
            <div id="questions-tab" class="tab-content">
                <div class="admin-section">
                    <h3>Add New Question</h3>
                    <form method="POST">
                        <div class="form-row">
                            <textarea name="question_text" placeholder="Question Text" rows="3" style="width:100%;" required></textarea>
                        </div>
                        <div class="form-row">
                            <input type="text" name="option_a" placeholder="Option A" required>
                            <input type="text" name="option_b" placeholder="Option B" required>
                        </div>
                        <div class="form-row">
                            <input type="text" name="option_c" placeholder="Option C" required>
                            <input type="text" name="option_d" placeholder="Option D" required>
                        </div>
                        <div class="form-row">
                            <select name="correct_answer" required>
                                <option value="">Select Correct Answer</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                                <option value="D">D</option>
                            </select>
                        </div>
                        <button type="submit" name="add_question" class="btn">Add Question</button>
                    </form>
                </div>

                <div class="admin-section">
                    <h3>All Questions (<?php echo count($questions); ?> total)</h3>
                    <?php foreach ($questions as $question): ?>
                    <div style="border:1px solid #ddd; padding:15px; margin:10px 0; border-radius:5px;">
                        <strong>Q<?php echo $question['id']; ?>:</strong> <?php echo $question['question_text']; ?>
                        <br><br>
                        A) <?php echo $question['option_a']; ?><br>
                        B) <?php echo $question['option_b']; ?><br>
                        C) <?php echo $question['option_c']; ?><br>
                        D) <?php echo $question['option_d']; ?><br>
                        <strong>Correct Answer: <?php echo $question['correct_answer']; ?></strong>
                        
                        <div style="margin-top:10px;">
                            <button onclick="editQuestion(<?php echo $question['id']; ?>)" class="btn">Edit</button>
                        </div>
                        
                        <!-- Edit form (hidden by default) -->
                        <div id="edit-<?php echo $question['id']; ?>" style="display:none; margin-top:15px; border-top:1px solid #eee; padding-top:15px;">
                            <form method="POST">
                                <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
                                <textarea name="question_text" style="width:100%; margin-bottom:10px;" rows="3"><?php echo $question['question_text']; ?></textarea>
                                <div class="form-row">
                                    <input type="text" name="option_a" value="<?php echo $question['option_a']; ?>">
                                    <input type="text" name="option_b" value="<?php echo $question['option_b']; ?>">
                                </div>
                                <div class="form-row">
                                    <input type="text" name="option_c" value="<?php echo $question['option_c']; ?>">
                                    <input type="text" name="option_d" value="<?php echo $question['option_d']; ?>">
                                </div>
                                <select name="correct_answer">
                                    <option value="A" <?php echo $question['correct_answer'] === 'A' ? 'selected' : ''; ?>>A</option>
                                    <option value="B" <?php echo $question['correct_answer'] === 'B' ? 'selected' : ''; ?>>B</option>
                                    <option value="C" <?php echo $question['correct_answer'] === 'C' ? 'selected' : ''; ?>>C</option>
                                    <option value="D" <?php echo $question['correct_answer'] === 'D' ? 'selected' : ''; ?>>D</option>
                                </select>
                                <button type="submit" name="update_question" class="btn">Update Question</button>
                                <button type="button" onclick="cancelEdit(<?php echo $question['id']; ?>)" class="btn btn-danger">Cancel</button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Results View -->
            <div id="results-tab" class="tab-content">
                <div class="admin-section">
                    <h3>Quiz Results (<?php echo count($quiz_results); ?> total)</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Marks</th>
                                <th>Grade</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($quiz_results as $result): ?>
                            <tr>
                                <td><?php echo $result['firstname'] . ' ' . $result['lastname']; ?></td>
                                <td><?php echo $result['total_marks']; ?>/20</td>
                                <td class="grade-<?php echo $result['grade']; ?>"><?php echo $result['grade']; ?></td>
                                <td><?php echo $result['quiz_date'] ? date('Y-m-d H:i', strtotime($result['quiz_date'])) : 'N/A'; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Reports -->
            <div id="reports-tab" class="tab-content">
                <div class="admin-section">
                    <h3>Generate Reports by Grade</h3>
                    <form method="GET">
                        <input type="hidden" name="report" value="1">
                        <div class="form-row">
                            <select name="grade">
                                <option value="">Select Grade</option>
                                <option value="A">Grade A (18-20 marks)</option>
                                <option value="B">Grade B (15-17 marks)</option>
                                <option value="C">Grade C (14-16 marks)</option>
                                <option value="D">Grade D (10-13 marks)</option>
                                <option value="F">Grade F (Below 10 marks)</option>
                            </select>
                            <button type="submit" class="btn">Generate Report</button>
                        </div>
                    </form>
                    
                    <?php if (isset($_GET['report']) && isset($_GET['grade'])): ?>
                    <?php
                    $selected_grade = $_GET['grade'];
                    $stmt = $pdo->prepare("SELECT qr.*, s.firstname, s.lastname FROM quiz_results qr JOIN students s ON qr.student_id = s.id WHERE qr.grade = ? ORDER BY qr.total_marks DESC");
                    $stmt->execute([$selected_grade]);
                    $report_results = $stmt->fetchAll();
                    ?>
                    
                    <h4>Students with Grade <?php echo $selected_grade; ?> (<?php echo getGradeRange($selected_grade); ?> marks)</h4>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Marks</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($report_results as $result): ?>
                            <tr>
                                <td><?php echo $result['firstname'] . ' ' . $result['lastname']; ?></td>
                                <td><?php echo $result['total_marks']; ?>/20</td>
                                <td><?php echo $result['quiz_date'] ? date('Y-m-d H:i', strtotime($result['quiz_date'])) : 'N/A'; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <footer>
            <p>© 2025 Kalisimbi Valley Academy - STEM Learning System</p>
        </footer>
    </div>

    <script>
        // Tab switching functionality
        function showTab(tabName) {
            // Hide all tab contents
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Remove active class from all tabs
            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(tab => tab.classList.remove('active'));
            
            // Show selected tab content
            document.getElementById(tabName + '-tab').classList.add('active');
            
            // Add active class to clicked tab
            event.target.classList.add('active');
        }

        // Question editing functionality
        function editQuestion(questionId) {
            document.getElementById('edit-' + questionId).style.display = 'block';
        }

        function cancelEdit(questionId) {
            document.getElementById('edit-' + questionId).style.display = 'none';
        }
    </script>
</body>
</html>