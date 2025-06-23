<?php
require_once 'db_config.php';

// Check if user is logged in and is student
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
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

// Handle quiz submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_quiz'])) {
    $student_id = $_SESSION['user_id'];
    $answers = $_POST['answers'];
    
    // Get all questions
    $stmt = $pdo->prepare("SELECT * FROM questions LIMIT 10");
    $stmt->execute();
    $questions = $stmt->fetchAll();
    
    $correct_answers = 0;
    $total_questions = count($questions);
    
    // Insert quiz result
    $stmt = $pdo->prepare("INSERT INTO quiz_results (student_id, total_questions, correct_answers, total_marks, grade, quiz_date) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$student_id, $total_questions, 0, 0, 'F']); // Will update after calculating
    $quiz_result_id = $pdo->lastInsertId();
    
    // Check answers and save individual responses
    foreach ($questions as $index => $question) {
        $selected_answer = isset($answers[$question['id']]) ? $answers[$question['id']] : '';
        $is_correct = ($selected_answer === $question['correct_answer']);
        
        if ($is_correct) {
            $correct_answers++;
        }
        
        // Save individual answer
        $stmt = $pdo->prepare("INSERT INTO quiz_answers (quiz_result_id, question_id, selected_answer, is_correct) VALUES (?, ?, ?, ?)");
        $stmt->execute([$quiz_result_id, $question['id'], $selected_answer, $is_correct]);
    }
    
    $total_marks = $correct_answers * 2; // 2 marks per question
    $grade = calculateGrade($total_marks);
    
    // Update quiz result
    $stmt = $pdo->prepare("UPDATE quiz_results SET correct_answers=?, total_marks=?, grade=? WHERE id=?");
    $stmt->execute([$correct_answers, $total_marks, $grade, $quiz_result_id]);
    
    $_SESSION['quiz_completed'] = true;
    $_SESSION['quiz_result_id'] = $quiz_result_id;
    $_SESSION['quiz_marks'] = $total_marks;
    $_SESSION['quiz_grade'] = $grade;
    $_SESSION['correct_answers'] = $correct_answers;
    $_SESSION['total_questions'] = $total_questions;
    
    header("Location: student_dashboard.php#quiz-results");
    exit();
}

// Get quiz questions for student
$stmt = $pdo->prepare("SELECT * FROM questions ORDER BY RAND() LIMIT 10");
$stmt->execute();
$current_quiz_questions = $stmt->fetchAll();

// Get student's quiz history
$stmt = $pdo->prepare("SELECT * FROM quiz_results WHERE student_id = ? ORDER BY quiz_date DESC");
$stmt->execute([$_SESSION['user_id']]);
$student_quiz_history = $stmt->fetchAll();

// Get student info
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$student_info = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - STEM Learning System</title>
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
        .quiz-result-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
            margin: 20px 0;
        }
        .grade-display {
            font-size: 3em;
            font-weight: bold;
            margin: 20px 0;
        }
        .grade-A { color: #28a745; }
        .grade-B { color: #17a2b8; }
        .grade-C { color: #ffc107; }
        .grade-D { color: #fd7e14; }
        .grade-F { color: #dc3545; }
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
        .admin-section {
            margin: 20px 0;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div id="app-screen">
        <a href="?logout=1" class="logout-btn">Logout</a>
        
        <header>
            <h1>Student Dashboard - STEM Learning System</h1>
        </header>

        <div class="container">
            <?php if ($success_message): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <div class="user-profile">
                <div class="avatar"><?php echo strtoupper(substr($student_info['firstname'], 0, 1) . substr($student_info['lastname'], 0, 1)); ?></div>
                <div class="user-info">
                    <h2><?php echo $student_info['firstname'] . ' ' . $student_info['lastname']; ?></h2>
                    <p>Student - Primary 4 | Quiz Average: 
                        <?php 
                        $avg_marks = 0;
                        if (count($student_quiz_history) > 0) {
                            // PHP 5.3+ compatible way to sum total_marks
                            $total_marks = 0;
                            foreach ($student_quiz_history as $quiz) {
                                $total_marks += $quiz['total_marks'];
                            }
                            $avg_marks = round($total_marks / count($student_quiz_history), 1);
                        }
                        echo $avg_marks;
                        ?>/20
                    </p>
                    <div class="progress-bar">
                        <div class="progress" style="width: <?php echo min(100, ($avg_marks / 20) * 100); ?>%;"></div>
                    </div>
                </div>
            </div>

            <div class="tabs">
                <div class="tab active" onclick="showTab('learn')">Learn</div>
                <div class="tab" onclick="showTab('quiz')">Take Quiz</div>
                <div class="tab" onclick="showTab('history')">Quiz History</div>
            </div>

            <!-- Learning Tab -->
            <div id="learn-tab" class="tab-content active">
                <div class="flashcard-container">
                    <div class="flashcard">
                        <div class="flashcard-inner">
                            <div class="flashcard-front">
                                <img src="images/Science_concepts.png" alt="Science concept" class="flashcard-image">
                                <h3 class="flashcard-title" id="card-front-title">States of Matter</h3>
                                <p class="flashcard-description" id="card-question">What happens when water boils?</p>
                            </div>
                            <div class="flashcard-back">
                                <h3 class="flashcard-title" id="card-back-title">Water Boiling</h3>
                                <p class="flashcard-description" id="card-answer">When water reaches 100°C, it changes from liquid to gas (water vapor). This process is called evaporation.</p>
                            </div>
                        </div>
                    </div>
                    <div class="flashcard-nav">
                        <button class="nav-btn" onclick="previousCard()">Previous</button>
                        <button class="nav-btn" onclick="flipCard()">Flip Card</button>
                        <button class="nav-btn" onclick="nextCard()">Next</button>
                    </div>
                </div>
            </div>

            <!-- Quiz Tab -->
            <div id="quiz-tab" class="tab-content">
                <?php if (isset($_SESSION['quiz_completed']) && $_SESSION['quiz_completed']): ?>
                <!-- Quiz Results -->
                <div class="quiz-result-card">
                    <h2>Quiz Completed!</h2>
                    <div class="grade-display grade-<?php echo $_SESSION['quiz_grade']; ?>">
                        Grade: <?php echo $_SESSION['quiz_grade']; ?>
                    </div>
                    <p><strong>Your Score: <?php echo $_SESSION['quiz_marks']; ?>/20</strong></p>
                    <p>Correct Answers: <?php echo $_SESSION['correct_answers']; ?>/<?php echo $_SESSION['total_questions']; ?></p>
                    
                    <!-- Show correct answers -->
                    <div style="text-align: left; margin-top: 20px;">
                        <h3>Review Answers:</h3>
                        <?php
                        $stmt = $pdo->prepare("
                            SELECT qa.*, q.question_text, q.option_a, q.option_b, q.option_c, q.option_d, q.correct_answer 
                            FROM quiz_answers qa 
                            JOIN questions q ON qa.question_id = q.id 
                            WHERE qa.quiz_result_id = ?
                        ");
                        $stmt->execute([$_SESSION['quiz_result_id']]);
                        $quiz_answers = $stmt->fetchAll();
                        
                        foreach ($quiz_answers as $index => $answer):
                        ?>
                        <div style="margin-bottom: 15px; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                            <strong>Q<?php echo $index + 1; ?>:</strong> <?php echo $answer['question_text']; ?><br>
                            <strong>Your Answer:</strong> <?php echo $answer['selected_answer']; ?> - 
                            <?php echo $answer['option_' . strtolower($answer['selected_answer'])]; ?><br>
                            <strong>Correct Answer:</strong> <?php echo $answer['correct_answer']; ?> - 
                            <?php echo $answer['option_' . strtolower($answer['correct_answer'])]; ?>
                            <?php if ($answer['is_correct']): ?>
                                <span style="color: green; font-weight: bold;"> ✓ Correct</span>
                            <?php else: ?>
                                <span style="color: red; font-weight: bold;"> ✗ Incorrect</span>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <button onclick="startNewQuiz()" class="btn">Take Another Quiz</button>
                </div>
                <?php else: ?>
                <!-- Quiz Questions -->
                <div class="quiz-container">
                    <h3>STEM Quiz - 10 Questions (2 marks each)</h3>
                    <p><strong>Instructions:</strong> Select the best answer for each question. You will see your results immediately after submission.</p>
                    
                    <form method="POST" id="quiz-form">
                        <?php foreach ($current_quiz_questions as $index => $question): ?>
                        <div class="question-block" style="margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 5px;">
                            <h4>Question <?php echo $index + 1; ?>:</h4>
                            <p><?php echo $question['question_text']; ?></p>
                            
                            <div class="quiz-options">
                                <label style="display: block; margin: 5px 0;">
                                    <input type="radio" name="answers[<?php echo $question['id']; ?>]" value="A" required>
                                    A) <?php echo $question['option_a']; ?>
                                </label>
                                <label style="display: block; margin: 5px 0;">
                                    <input type="radio" name="answers[<?php echo $question['id']; ?>]" value="B" required>
                                    B) <?php echo $question['option_b']; ?>
                                </label>
                                <label style="display: block; margin: 5px 0;">
                                    <input type="radio" name="answers[<?php echo $question['id']; ?>]" value="C" required>
                                    C) <?php echo $question['option_c']; ?>
                                </label>
                                <label style="display: block; margin: 5px 0;">
                                    <input type="radio" name="answers[<?php echo $question['id']; ?>]" value="D" required>
                                    D) <?php echo $question['option_d']; ?>
                                </label>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <div style="text-align: center; margin-top: 30px;">
                            <button type="submit" name="submit_quiz" class="btn" style="font-size: 16px; padding: 15px 30px;"
                                    onclick="return confirm('Are you sure you want to submit your quiz? You cannot change your answers after submission.')">
                                Submit Quiz
                            </button>
                        </div>
                    </form>
                </div>
                <?php endif; ?>
            </div>

            <!-- Quiz History -->
            <div id="history-tab" class="tab-content">
                <div class="admin-section">
                    <h3>Your Quiz History (<?php echo count($student_quiz_history); ?> attempts)</h3>
                    <?php if (count($student_quiz_history) > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Marks</th>
                                <th>Grade</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($student_quiz_history as $history): ?>
                            <tr>
                                <td><?php echo $history['quiz_date'] ? date('Y-m-d H:i', strtotime($history['quiz_date'])) : 'N/A'; ?></td>
                                <td><?php echo $history['total_marks']; ?>/20</td>
                                <td class="grade-<?php echo $history['grade']; ?>"><?php echo $history['grade']; ?></td>
                                <td><?php echo round(($history['total_marks'] / 20) * 100, 1); ?>%</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
                        <h4>Your Statistics:</h4>
                        <p><strong>Total Quizzes Taken:</strong> <?php echo count($student_quiz_history); ?></p>
                        <p><strong>Average Score:</strong> <?php echo $avg_marks; ?>/20 (<?php echo round(($avg_marks / 20) * 100, 1); ?>%)</p>
                        <p><strong>Best Score:</strong> 
                            <?php 
                            $best_score = 0;
                            if (count($student_quiz_history) > 0) {
                                // PHP 5.3+ compatible way to find max total_marks
                                foreach ($student_quiz_history as $quiz) {
                                    if ($quiz['total_marks'] > $best_score) {
                                        $best_score = $quiz['total_marks'];
                                    }
                                }
                            }
                            echo $best_score;
                            ?>/20
                        </p>
                    </div>
                    <?php else: ?>
                    <p style="text-align: center; padding: 40px; color: #666;">
                        You haven't taken any quizzes yet. Go to the Quiz tab to take your first quiz!
                    </p>
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

        // Start new quiz
        function startNewQuiz() {
            if (confirm('Are you ready to take a new quiz?')) {
                window.location.href = 'student_dashboard.php?new_quiz=1';
            }
        }

        // Flashcard functionality
        let currentCardIndex = 0;
        const flashcards = [
            {
                frontTitle: "States of Matter",
                question: "What happens when water boils?",
                backTitle: "Water Boiling",
                answer: "When water reaches 100°C, it changes from liquid to gas (water vapor). This process is called evaporation."
            },
            {
                frontTitle: "Plant Life Cycle",
                question: "What do plants need to grow?",
                backTitle: "Plant Growth",
                answer: "Plants need sunlight, water, air, and nutrients from soil to grow. Through photosynthesis, they use sunlight to make their own food."
            },
            {
                frontTitle: "Simple Machines",
                question: "What is a lever?",
                backTitle: "Lever",
                answer: "A lever is a simple machine that consists of a rigid bar that pivots on a fixed point called a fulcrum. It helps us lift heavy objects with less effort."
            },
            {
                frontTitle: "Solar System",
                question: "What is the closest planet to the Sun?",
                backTitle: "Mercury",
                answer: "Mercury is the closest planet to the Sun. It is a small, rocky planet with extreme temperatures - very hot during the day and very cold at night."
            },
            {
                frontTitle: "Magnets",
                question: "What materials are attracted to magnets?",
                backTitle: "Magnetic Materials",
                answer: "Magnets attract materials containing iron, nickel, or cobalt. These materials are called ferromagnetic. Materials like plastic, wood, and aluminum are not attracted to magnets."
            }
        ];

        function updateCard() {
            const card = flashcards[currentCardIndex];
            document.getElementById('card-front-title').textContent = card.frontTitle;
            document.getElementById('card-question').textContent = card.question;
            document.getElementById('card-back-title').textContent = card.backTitle;
            document.getElementById('card-answer').textContent = card.answer;
            
            // Reset card to front side
            document.querySelector('.flashcard').classList.remove('flipped');
        }

        function flipCard() {
            document.querySelector('.flashcard').classList.toggle('flipped');
        }

        function previousCard() {
            currentCardIndex = (currentCardIndex - 1 + flashcards.length) % flashcards.length;
            updateCard();
        }

        function nextCard() {
            currentCardIndex = (currentCardIndex + 1) % flashcards.length;
            updateCard();
        }

        // Initialize card
        updateCard();

        // Clear quiz completion flag when user wants new quiz
        <?php if (isset($_GET['new_quiz'])): ?>
        <?php unset($_SESSION['quiz_completed']); ?>
        window.location.href = 'student_dashboard.php';
        <?php endif; ?>
    </script>
</body>
</html>