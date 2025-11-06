<?php
session_start();
include "config.php";

// Check if user is a student
require_student();

$student_id = $_SESSION["user_id"];
$quiz_id = null;
$quiz_data = null;
$course_data = null;
$submitted = false;
$is_correct = false;
$user_answer = null;
$correct_option = null;
$result_saved = false;
$answer_text = "";

// Check if quiz ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: student_courses.php");
    exit;
}

$quiz_id = intval($_GET['id']);

// Get quiz and course information
$sql_quiz = "SELECT q.*, c.id as course_id, c.name as course_name
              FROM quizzes q
              JOIN courses c ON q.course_id = c.id
              WHERE q.id = :quiz_id";
$stmt_quiz = $pdo->prepare($sql_quiz);
$stmt_quiz->execute(['quiz_id' => $quiz_id]);
$quiz_data = $stmt_quiz->fetch();

if (!$quiz_data) {
    $_SESSION['error_message'] = "Quiz bulunamadı!";
    header("Location: student_courses.php");
    exit;
}

// Course data for navigation
$course_id = $quiz_data['course_id'];
$course_name = $quiz_data['course_name'];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_quiz'])) {
    $submitted = true;
    
    if (!isset($_POST['answer'])) {
        $error_message = "Lütfen bir cevap seçiniz!";
    } else {
        $user_answer = secure_input($_POST['answer']);
        $correct_option = $quiz_data['correct_option'];
        $is_correct = ($user_answer == $correct_option);
        
        // Map option letters to the full text answers
        $option_map = [
            'A' => $quiz_data['option_a'],
            'B' => $quiz_data['option_b'],
            'C' => $quiz_data['option_c'],
            'D' => $quiz_data['option_d']
        ];
        
        $answer_text = $option_map[$user_answer] ?? '';
        
        // Save quiz result
        $sql_save = "INSERT INTO quiz_results (user_id, quiz_id, user_answer, is_correct) 
                     VALUES (:user_id, :quiz_id, :user_answer, :is_correct)";
        $stmt_save = $pdo->prepare($sql_save);
        $save_params = [
            'user_id' => $student_id,
            'quiz_id' => $quiz_id,
            'user_answer' => $user_answer,
            'is_correct' => $is_correct ? 1 : 0
        ];
        
        if ($stmt_save->execute($save_params)) {
            $result_saved = true;
        } else {
            $error_message = "Sonuç kaydedilirken bir hata oluştu!";
        }
    }
}

// Get previous attempts for this quiz
$sql_attempts = "SELECT user_answer, is_correct, created_at
               FROM quiz_results
               WHERE user_id = :user_id AND quiz_id = :quiz_id
               ORDER BY created_at DESC";
$stmt_attempts = $pdo->prepare($sql_attempts);
$stmt_attempts->execute([
    'user_id' => $student_id,
    'quiz_id' => $quiz_id
]);
$previous_attempts = $stmt_attempts->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Mehmet Akif Ersoy Üniversitesi Quiz Sayfası">
    <meta name="keywords" content="MAKÜ, öğrenci, quiz, test, sınav">
    <meta name="author" content="Mehmet Akif Ersoy Üniversitesi">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#1A3C34">
    <title>MAKÜ - Quiz</title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="icon" type="image/jpeg" href="img/header-logo.jpg">
</head>
<body class="dashboard-body">
    <header id="header" class="header dashboard-header">
        <div class="container">
            <img src="img/school-logo.jpg" alt="MAKÜ Logo" class="header-logo">
            <div class="nav-toggle">☰</div>
            <nav id="navmenu" class="navmenu">
                <ul>
                    <li><a href="student_dashboard.php">Anasayfa</a></li>
                    <li><a href="student_courses.php" class="active">Derslerim</a></li>
                    <li><a href="student_quiz_history.php">Quiz Geçmişim</a></li>
                    <li><a href="student_quiz_stats.php">İstatistiklerim</a></li>
                    <li><a href="islem/logout.php" class="btn-action">Çıkış</a></li>
                </ul>
            </nav>
        </div>
    </header>
    
    <div class="dashboard-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="img/school-logo.jpg" alt="MAKÜ Logo" class="sidebar-logo">
                <h3>Öğrenci Paneli</h3>
            </div>
            <ul class="sidebar-menu">
                <li><a href="student_dashboard.php"><i class="icon">🏠</i> Ana Sayfa</a></li>
                <li><a href="student_courses.php" class="active"><i class="icon">📚</i> Derslerim</a></li>
                <li><a href="student_quiz_history.php"><i class="icon">📝</i> Quiz Geçmişim</a></li>
                <li><a href="student_quiz_stats.php"><i class="icon">📊</i> İstatistiklerim</a></li>
                <li><a href="islem/logout.php"><i class="icon">🚪</i> Çıkış</a></li>
            </ul>
        </div>
        
        <main class="dashboard-content">
            <div class="dashboard-header">
                <h1>Quiz</h1>
                <div class="breadcrumbs">
                    <a href="student_dashboard.php">Ana Sayfa</a> &gt; 
                    <a href="student_courses.php">Dersler</a> &gt; 
                    <a href="student_courses.php?course_id=<?php echo $course_id; ?>"><?php echo e($course_name); ?></a> &gt; 
                    <span>Quiz</span>
                </div>
            </div>
            
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <?php if (!$submitted): ?>
                <!-- Quiz Form -->
                <div class="content-card">
                    <div class="card-header">
                        <h2>Soruyu Cevaplayın</h2>
                        <div class="course-badge"><?php echo e($course_name); ?></div>
                    </div>
                    <div class="card-body">
                        <div class="quiz-question">
                            <p class="question-text"><?php echo e($quiz_data['question']); ?></p>
                        </div>
                        
                        <form method="POST" action="" class="quiz-form">
                            <div class="quiz-options">
                                <div class="option">
                                    <input type="radio" id="option_a" name="answer" value="A" required>
                                    <label for="option_a"><?php echo e($quiz_data['option_a']); ?></label>
                                </div>
                                
                                <div class="option">
                                    <input type="radio" id="option_b" name="answer" value="B">
                                    <label for="option_b"><?php echo e($quiz_data['option_b']); ?></label>
                                </div>
                                
                                <div class="option">
                                    <input type="radio" id="option_c" name="answer" value="C">
                                    <label for="option_c"><?php echo e($quiz_data['option_c']); ?></label>
                                </div>
                                
                                <div class="option">
                                    <input type="radio" id="option_d" name="answer" value="D">
                                    <label for="option_d"><?php echo e($quiz_data['option_d']); ?></label>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" name="submit_quiz" class="primary-button">
                                    <i class="button-icon">✅</i> Cevabı Gönder
                                </button>
                                <a href="student_courses.php?course_id=<?php echo $course_id; ?>" class="secondary-button">
                                    <i class="button-icon">🔙</i> Vazgeç
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <!-- Quiz Result -->
                <div class="content-card">
                    <div class="card-header">
                        <h2>Quiz Sonucu</h2>
                        <div class="course-badge"><?php echo e($course_name); ?></div>
                    </div>
                    <div class="card-body">
                        <div class="quiz-question">
                            <p class="question-text"><?php echo e($quiz_data['question']); ?></p>
                        </div>
                        
                        <div class="quiz-result">
                            <div class="result-icon <?php echo $is_correct ? 'correct' : 'wrong'; ?>">
                                <?php echo $is_correct ? '✅' : '❌'; ?>
                            </div>
                            
                            <div class="result-message">
                                <?php if ($is_correct): ?>
                                    <h3>Doğru Cevap!</h3>
                                    <p>Tebrikler, soruyu doğru cevapladınız.</p>
                                <?php else: ?>
                                    <h3>Yanlış Cevap</h3>
                                    <p>Üzgünüz, cevabınız yanlış.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="answer-details">
                            <div class="answer-item">
                                <h4>Sizin Cevabınız:</h4>
                                <p class="user-answer"><?php echo e($user_answer); ?> - <?php echo e($answer_text); ?></p>
                            </div>
                            
                            <?php if (!$is_correct): ?>
                                <div class="answer-item">
                                    <h4>Doğru Cevap:</h4>
                                    <p class="correct-answer"><?php echo e($correct_option); ?> - <?php echo e($quiz_data['option_' . strtolower($correct_option)]); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="action-buttons">
                            <a href="take_quiz.php?id=<?php echo $quiz_id; ?>" class="primary-button">
                                <i class="button-icon">🔄</i> Tekrar Çöz
                            </a>
                            <a href="student_courses.php?course_id=<?php echo $course_id; ?>" class="secondary-button">
                                <i class="button-icon">🔙</i> Derse Dön
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (count($previous_attempts) > 0): ?>
                <div class="content-card">
                    <div class="card-header">
                        <h2>Önceki Denemeleriniz</h2>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Cevap</th>
                                        <th>Sonuç</th>
                                        <th>Tarih</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($previous_attempts as $index => $attempt): ?>
                                        <tr>
                                            <td><?php echo count($previous_attempts) - $index; ?></td>
                                            <td><?php echo e($attempt['user_answer']); ?></td>
                                            <td class="<?php echo $attempt['is_correct'] ? 'correct-answer' : 'wrong-answer'; ?>">
                                                <?php echo $attempt['is_correct'] ? '✅ Doğru' : '❌ Yanlış'; ?>
                                            </td>
                                            <td><?php echo date('d.m.Y H:i', strtotime($attempt['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
    
    <script src="js/main.js"></script>
    <script>
        // Highlight quiz options on click
        document.addEventListener('DOMContentLoaded', function() {
            const options = document.querySelectorAll('.quiz-options .option');
            
            options.forEach(option => {
                option.addEventListener('click', function() {
                    // First remove highlight from all options
                    options.forEach(opt => opt.classList.remove('selected'));
                    
                    // Then add highlight to the clicked option
                    this.classList.add('selected');
                    
                    // And select the radio button
                    const radio = this.querySelector('input[type="radio"]');
                    if (radio) {
                        radio.checked = true;
                    }
                });
            });
        });
    </script>
</body>
</html>