<?php
session_start();
include "config.php";

// Check if user is a student
require_student();

$student_id = $_SESSION["user_id"];
$selected_course = null;

// Get all courses with quizzes
$sql_courses = "SELECT DISTINCT c.id, c.name, c.description, u.name AS teacher_name,
                (SELECT COUNT(*) FROM quizzes WHERE course_id = c.id) AS quiz_count
                FROM courses c
                JOIN quizzes q ON q.course_id = c.id
                LEFT JOIN users u ON c.teacher_id = u.id
                ORDER BY c.name";
$stmt_courses = $pdo->prepare($sql_courses);
$stmt_courses->execute();
$courses = $stmt_courses->fetchAll();

// If a course is selected
if (isset($_GET['course_id']) && is_numeric($_GET['course_id'])) {
    $course_id = intval($_GET['course_id']);
    
    // Get course details
    $sql_course = "SELECT c.*, u.name AS teacher_name
                  FROM courses c
                  LEFT JOIN users u ON c.teacher_id = u.id
                  WHERE c.id = :course_id";
    $stmt_course = $pdo->prepare($sql_course);
    $stmt_course->execute(['course_id' => $course_id]);
    $selected_course = $stmt_course->fetch();
    
    if ($selected_course) {
        // Get quizzes for this course
        $sql_quizzes = "SELECT q.id, q.question, 
                       (SELECT COUNT(*) FROM quiz_results WHERE quiz_id = q.id AND user_id = :student_id) AS attempted
                       FROM quizzes q
                       WHERE q.course_id = :course_id
                       ORDER BY q.id";
        $stmt_quizzes = $pdo->prepare($sql_quizzes);
        $stmt_quizzes->execute([
            'course_id' => $course_id,
            'student_id' => $student_id
        ]);
        $quizzes = $stmt_quizzes->fetchAll();
        
        // Get student's performance in this course
        $sql_performance = "SELECT 
                          COUNT(qr.id) AS total_attempts,
                          SUM(CASE WHEN qr.is_correct THEN 1 ELSE 0 END) AS correct_answers
                          FROM quiz_results qr
                          JOIN quizzes q ON qr.quiz_id = q.id
                          WHERE q.course_id = :course_id AND qr.user_id = :student_id";
        $stmt_performance = $pdo->prepare($sql_performance);
        $stmt_performance->execute([
            'course_id' => $course_id,
            'student_id' => $student_id
        ]);
        $performance = $stmt_performance->fetch();
        
        $total_attempts = $performance['total_attempts'] ?? 0;
        $correct_answers = $performance['correct_answers'] ?? 0;
        $success_rate = ($total_attempts > 0) ? round(($correct_answers / $total_attempts) * 100, 2) : 0;
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Mehmet Akif Ersoy Üniversitesi Öğrenci Dersler Sayfası">
    <meta name="keywords" content="MAKÜ, öğrenci, dersler, kurslar, quiz">
    <meta name="author" content="Mehmet Akif Ersoy Üniversitesi">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#1A3C34">
    <title>MAKÜ - Derslerim</title>
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
                <h1>Derslerim</h1>
                <p>Tüm dersleri görüntüleyebilir ve quizlere katılabilirsiniz.</p>
            </div>
            
            <div class="dashboard-row">
                <div class="courses-sidebar">
                    <h2>Tüm Dersler</h2>
                    <input type="text" class="search-input" placeholder="Ders ara..." id="courseSearch">
                    
                    <div class="course-list-sidebar">
                        <?php if (count($courses) > 0): ?>
                            <?php foreach ($courses as $course): ?>
                                <a href="student_courses.php?course_id=<?php echo $course['id']; ?>" class="course-list-item <?php echo (isset($_GET['course_id']) && $_GET['course_id'] == $course['id']) ? 'active' : ''; ?>">
                                    <h3><?php echo e($course['name']); ?></h3>
                                    <p class="course-teacher">Öğretmen: <?php echo e($course['teacher_name'] ?: 'Atanmamış'); ?></p>
                                    <span class="quiz-count">Quiz: <?php echo $course['quiz_count']; ?></span>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="no-courses">Aktif ders bulunmamaktadır.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="course-content">
                    <?php if ($selected_course): ?>
                        <div class="course-details">
                            <h2><?php echo e($selected_course['name']); ?></h2>
                            <p class="course-instructor">Öğretmen: <?php echo e($selected_course['teacher_name'] ?: 'Atanmamış'); ?></p>
                            <div class="course-description">
                                <h3>Ders Açıklaması</h3>
                                <p><?php echo e($selected_course['description'] ?: 'Bu ders için açıklama bulunmamaktadır.'); ?></p>
                            </div>
                            
                            <div class="course-progress">
                                <h3>Ders İlerleme Durumu</h3>
                                <div class="progress-stats">
                                    <div class="progress-stat">
                                        <span class="stat-label">Toplam Quiz Sayısı:</span>
                                        <span class="stat-value"><?php echo count($quizzes); ?></span>
                                    </div>
                                    <div class="progress-stat">
                                        <span class="stat-label">Tamamlanan Quiz:</span>
                                        <span class="stat-value"><?php echo $total_attempts; ?></span>
                                    </div>
                                    <div class="progress-stat">
                                        <span class="stat-label">Doğru Cevaplar:</span>
                                        <span class="stat-value"><?php echo $correct_answers; ?></span>
                                    </div>
                                    <div class="progress-stat">
                                        <span class="stat-label">Başarı Oranı:</span>
                                        <span class="stat-value"><?php echo $success_rate; ?>%</span>
                                    </div>
                                </div>
                                
                                <div class="progress-bar-container">
                                    <div class="progress-label">Genel İlerleme</div>
                                    <div class="progress">
                                        <?php 
                                        $progress_percentage = 0;
                                        if (count($quizzes) > 0) {
                                            $completed_count = 0;
                                            foreach ($quizzes as $quiz) {
                                                if ($quiz['attempted'] > 0) {
                                                    $completed_count++;
                                                }
                                            }
                                            $progress_percentage = round(($completed_count / count($quizzes)) * 100);
                                        }
                                        ?>
                                        <div class="progress-bar" style="width: <?php echo $progress_percentage; ?>%;">
                                            <span class="progress-text"><?php echo $progress_percentage; ?>%</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="progress-bar-container">
                                    <div class="progress-label">Başarı Oranı</div>
                                    <div class="progress">
                                        <div class="progress-bar success-bar" style="width: <?php echo $success_rate; ?>%;">
                                            <span class="progress-text"><?php echo $success_rate; ?>%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="course-quizzes">
                                <h3>Quizler</h3>
                                <?php if (count($quizzes) > 0): ?>
                                    <div class="quiz-list">
                                        <?php foreach ($quizzes as $index => $quiz): ?>
                                            <div class="quiz-item <?php echo $quiz['attempted'] > 0 ? 'completed' : ''; ?>">
                                                <div class="quiz-number"><?php echo $index + 1; ?></div>
                                                <div class="quiz-details">
                                                    <h4><?php echo e(substr($quiz['question'], 0, 80) . (strlen($quiz['question']) > 80 ? '...' : '')); ?></h4>
                                                    <div class="quiz-status">
                                                        <?php if ($quiz['attempted'] > 0): ?>
                                                            <span class="status completed">Tamamlandı</span>
                                                        <?php else: ?>
                                                            <span class="status pending">Bekliyor</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="quiz-actions">
                                                    <a href="take_quiz.php?id=<?php echo $quiz['id']; ?>" class="btn-action">
                                                        <?php echo $quiz['attempted'] > 0 ? 'Tekrar Çöz' : 'Quizi Çöz'; ?>
                                                    </a>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="no-quizzes">Bu ders için quiz bulunmamaktadır.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="course-welcome">
                            <div class="welcome-icon">📚</div>
                            <h2>Ders Seçiniz</h2>
                            <p>Detayları görüntülemek ve quizlere katılmak için soldaki listeden bir ders seçiniz.</p>
                            <?php if (count($courses) == 0): ?>
                                <div class="alert alert-info">
                                    <p>Sistemde henüz aktif bir ders bulunmuyor.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <script src="js/main.js"></script>
    <script>
        // Search functionality for courses
        document.getElementById('courseSearch').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const courseItems = document.querySelectorAll('.course-list-item');
            
            courseItems.forEach(item => {
                const courseText = item.textContent.toLowerCase();
                if (courseText.includes(searchTerm)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>