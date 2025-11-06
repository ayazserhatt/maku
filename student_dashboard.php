<?php
session_start();
include "config.php";

// Check if user is a student
require_student();

$student_id = $_SESSION["user_id"];

// Get student's course and quiz statistics
$sql_courses = "SELECT COUNT(DISTINCT c.id) as total_courses 
                FROM courses c
                JOIN quizzes q ON q.course_id = c.id
                JOIN quiz_results qr ON qr.quiz_id = q.id
                WHERE qr.user_id = :student_id";
$stmt_courses = $pdo->prepare($sql_courses);
$stmt_courses->execute(['student_id' => $student_id]);
$total_courses = $stmt_courses->fetch()['total_courses'] ?? 0;

// Get quiz statistics
$sql_quizzes = "SELECT 
                COUNT(qr.id) as total_attempts,
                SUM(CASE WHEN qr.is_correct THEN 1 ELSE 0 END) as correct_answers
                FROM quiz_results qr
                WHERE qr.user_id = :student_id";
$stmt_quizzes = $pdo->prepare($sql_quizzes);
$stmt_quizzes->execute(['student_id' => $student_id]);
$quiz_stats = $stmt_quizzes->fetch();

$total_attempts = $quiz_stats['total_attempts'] ?? 0;
$correct_answers = $quiz_stats['correct_answers'] ?? 0;
$success_rate = ($total_attempts > 0) ? round(($correct_answers / $total_attempts) * 100, 2) : 0;

// Get recent quiz results
$sql_recent = "SELECT 
                c.name AS course_name, 
                q.question, 
                qr.is_correct, 
                qr.created_at
              FROM quiz_results qr
              JOIN quizzes q ON qr.quiz_id = q.id
              JOIN courses c ON q.course_id = c.id
              WHERE qr.user_id = :student_id
              ORDER BY qr.created_at DESC
              LIMIT 5";
$stmt_recent = $pdo->prepare($sql_recent);
$stmt_recent->execute(['student_id' => $student_id]);
$recent_results = $stmt_recent->fetchAll();

// Get courses with active quizzes
$sql_active_courses = "SELECT DISTINCT c.id, c.name, c.description, u.name AS teacher_name,
                      (SELECT COUNT(*) FROM quizzes WHERE course_id = c.id) AS quiz_count
                      FROM courses c
                      JOIN quizzes q ON q.course_id = c.id
                      LEFT JOIN users u ON c.teacher_id = u.id
                      ORDER BY c.name
                      LIMIT 3";
$stmt_active_courses = $pdo->prepare($sql_active_courses);
$stmt_active_courses->execute();
$active_courses = $stmt_active_courses->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Mehmet Akif Ersoy Üniversitesi Öğrenci Yönetim Paneli">
    <meta name="keywords" content="MAKÜ, öğrenci, yönetim paneli">
    <meta name="author" content="Mehmet Akif Ersoy Üniversitesi">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#1A3C34">
    <title>MAKÜ - Öğrenci Paneli</title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="icon" type="image/jpeg" href="img/header-logo.jpg">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="dashboard-body">
    <header id="header" class="header dashboard-header">
        <div class="container">
            <img src="img/school-logo.jpg" alt="MAKÜ Logo" class="header-logo">
            <div class="nav-toggle">☰</div>
            <nav id="navmenu" class="navmenu">
                <ul>
                    <li><a href="student_dashboard.php" class="active">Anasayfa</a></li>
                    <li><a href="student_courses.php">Derslerim</a></li>
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
                <li><a href="student_dashboard.php" class="active"><i class="icon">🏠</i> Ana Sayfa</a></li>
                <li><a href="student_courses.php"><i class="icon">📚</i> Derslerim</a></li>
                <li><a href="student_quiz_history.php"><i class="icon">📝</i> Quiz Geçmişim</a></li>
                <li><a href="student_quiz_stats.php"><i class="icon">📊</i> İstatistiklerim</a></li>
                <li><a href="islem/logout.php"><i class="icon">🚪</i> Çıkış</a></li>
            </ul>
        </div>
        
        <main class="dashboard-content">
            <div class="dashboard-header">
                <h1>Hoş Geldin, <?php echo e($_SESSION["user_name"]); ?>!</h1>
                <p>Online eğitim platformunda derslerinizi takip edebilir ve quizleri çözebilirsiniz.</p>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📚</div>
                    <div class="stat-details">
                        <h3>Katıldığım Dersler</h3>
                        <p class="stat-number"><?php echo $total_courses; ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">📝</div>
                    <div class="stat-details">
                        <h3>Çözdüğüm Quizler</h3>
                        <p class="stat-number"><?php echo $total_attempts; ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-details">
                        <h3>Doğru Cevaplarım</h3>
                        <p class="stat-number"><?php echo $correct_answers; ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-details">
                        <h3>Başarı Oranım</h3>
                        <p class="stat-number"><?php echo $success_rate; ?>%</p>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-row">
                <div class="dashboard-chart-container">
                    <h2>Quiz Performansım</h2>
                    <div class="chart-container">
                        <canvas id="quizPerformanceChart"></canvas>
                    </div>
                </div>
                
                <div class="dashboard-recent-activities">
                    <h2>Son Quiz Sonuçlarım</h2>
                    <div class="activity-list">
                        <?php if ($recent_results && count($recent_results) > 0): ?>
                            <?php foreach ($recent_results as $row): ?>
                                <div class="activity-item">
                                    <div class="activity-icon <?php echo $row['is_correct'] ? 'correct' : 'wrong'; ?>">
                                        <?php echo $row['is_correct'] ? '✅' : '❌'; ?>
                                    </div>
                                    <div class="activity-details">
                                        <h4><?php echo e($row['course_name']); ?></h4>
                                        <p><?php echo e($row['question']); ?></p>
                                        <span class="activity-time"><?php echo date('d.m.Y H:i', strtotime($row['created_at'])); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="no-activities">Henüz quiz çözmediniz.</p>
                            <a href="student_courses.php" class="btn-action">Dersleri Görüntüle</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="content-card">
                <div class="card-header">
                    <h2>Aktif Dersler</h2>
                    <a href="student_courses.php" class="btn-action">Tüm Dersleri Gör</a>
                </div>
                
                <?php if ($active_courses && count($active_courses) > 0): ?>
                    <div class="course-grid">
                        <?php foreach ($active_courses as $course): ?>
                            <div class="course-card">
                                <h3><?php echo e($course['name']); ?></h3>
                                <p class="course-instructor">Öğretmen: <?php echo e($course['teacher_name'] ?: 'Atanmamış'); ?></p>
                                <p class="course-quiz-count">Quiz Sayısı: <?php echo $course['quiz_count']; ?></p>
                                <p><?php echo e(substr($course['description'], 0, 100) . (strlen($course['description']) > 100 ? '...' : '')); ?></p>
                                <div class="course-card-actions">
                                    <a href="student_courses.php?course_id=<?php echo $course['id']; ?>" class="btn-view">Detayları Gör</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-data">
                        <p>Henüz aktif ders bulunmamaktadır.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="quick-actions">
                <h2>Hızlı İşlemler</h2>
                <div class="action-buttons">
                    <a href="student_courses.php" class="action-button">
                        <i class="action-icon">📚</i>
                        <span>Derslerime Git</span>
                    </a>
                    <a href="student_quiz_history.php" class="action-button">
                        <i class="action-icon">📝</i>
                        <span>Quiz Geçmişim</span>
                    </a>
                    <a href="student_quiz_stats.php" class="action-button">
                        <i class="action-icon">📊</i>
                        <span>İstatistiklerimi Gör</span>
                    </a>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        // Quiz Performance Chart
        const ctx = document.getElementById('quizPerformanceChart').getContext('2d');
        const quizPerformanceChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Doğru', 'Yanlış'],
                datasets: [{
                    data: [
                        <?php echo $correct_answers; ?>, 
                        <?php echo $total_attempts - $correct_answers; ?>
                    ],
                    backgroundColor: ['#1A3C34', '#800020'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
    <script src="js/main.js"></script>
</body>
</html>
