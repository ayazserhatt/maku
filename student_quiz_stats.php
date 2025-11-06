<?php
session_start();
include "config.php";

// Check if user is a student
require_student();

$student_id = $_SESSION["user_id"];

// Get overall quiz statistics
$sql_stats = "SELECT 
              COUNT(qr.id) AS total_attempts,
              SUM(qr.is_correct) AS correct_answers
              FROM quiz_results qr
              WHERE qr.user_id = :student_id";
$stmt_stats = $pdo->prepare($sql_stats);
$stmt_stats->execute(['student_id' => $student_id]);
$stats = $stmt_stats->fetch();

$total_attempts = $stats['total_attempts'] ?? 0;
$correct_answers = $stats['correct_answers'] ?? 0;
$success_rate = ($total_attempts > 0) ? round(($correct_answers / $total_attempts) * 100, 2) : 0;

// Get statistics by course
$sql_course_stats = "SELECT 
                    c.id AS course_id, 
                    c.name AS course_name,
                    COUNT(qr.id) AS course_attempts,
                    SUM(CASE WHEN qr.is_correct THEN 1 ELSE 0 END) AS course_correct
                    FROM quiz_results qr
                    JOIN quizzes q ON qr.quiz_id = q.id
                    JOIN courses c ON q.course_id = c.id
                    WHERE qr.user_id = :student_id
                    GROUP BY c.id, c.name
                    ORDER BY c.name";
$stmt_course_stats = $pdo->prepare($sql_course_stats);
$stmt_course_stats->execute(['student_id' => $student_id]);
$course_stats = $stmt_course_stats->fetchAll();

// Get statistics by date (last 7 days)
$sql_date_stats = "SELECT 
                  DATE(qr.created_at) AS quiz_date,
                  COUNT(qr.id) AS date_attempts,
                  SUM(qr.is_correct) AS date_correct
                  FROM quiz_results qr
                  WHERE qr.user_id = :student_id
                  AND qr.created_at >= DATE(NOW()) - INTERVAL 7 DAY
                  GROUP BY DATE(qr.created_at)
                  ORDER BY quiz_date";
$stmt_date_stats = $pdo->prepare($sql_date_stats);
$stmt_date_stats->execute(['student_id' => $student_id]);
$date_stats = $stmt_date_stats->fetchAll();

// Format data for charts
$course_names = [];
$course_success_rates = [];

foreach ($course_stats as $course) {
    $course_names[] = $course['course_name'];
    $course_success_rate = ($course['course_attempts'] > 0) ? 
        round(($course['course_correct'] / $course['course_attempts']) * 100, 2) : 0;
    $course_success_rates[] = $course_success_rate;
}

$dates = [];
$date_success_rates = [];

foreach ($date_stats as $date) {
    $dates[] = date('d.m.Y', strtotime($date['quiz_date']));
    $date_success_rate = ($date['date_attempts'] > 0) ? 
        round(($date['date_correct'] / $date['date_attempts']) * 100, 2) : 0;
    $date_success_rates[] = $date_success_rate;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Mehmet Akif Ersoy Üniversitesi Öğrenci Quiz İstatistikleri">
    <meta name="keywords" content="MAKÜ, öğrenci, quiz, istatistik, başarı">
    <meta name="author" content="Mehmet Akif Ersoy Üniversitesi">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#1A3C34">
    <title>MAKÜ - Quiz İstatistiklerim</title>
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
                    <li><a href="student_dashboard.php">Anasayfa</a></li>
                    <li><a href="student_courses.php">Derslerim</a></li>
                    <li><a href="student_quiz_history.php">Quiz Geçmişim</a></li>
                    <li><a href="student_quiz_stats.php" class="active">İstatistiklerim</a></li>
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
                <li><a href="student_courses.php"><i class="icon">📚</i> Derslerim</a></li>
                <li><a href="student_quiz_history.php"><i class="icon">📝</i> Quiz Geçmişim</a></li>
                <li><a href="student_quiz_stats.php" class="active"><i class="icon">📊</i> İstatistiklerim</a></li>
                <li><a href="islem/logout.php"><i class="icon">🚪</i> Çıkış</a></li>
            </ul>
        </div>
        
        <main class="dashboard-content">
            <div class="dashboard-header">
                <h1>Quiz İstatistiklerim</h1>
                <p>Çözdüğünüz quizlerin istatistikleri ve genel performansınız.</p>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📝</div>
                    <div class="stat-details">
                        <h3>Toplam Quiz</h3>
                        <p class="stat-number"><?php echo $total_attempts; ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-details">
                        <h3>Doğru Cevap</h3>
                        <p class="stat-number"><?php echo $correct_answers; ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">❌</div>
                    <div class="stat-details">
                        <h3>Yanlış Cevap</h3>
                        <p class="stat-number"><?php echo $total_attempts - $correct_answers; ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-details">
                        <h3>Başarı Oranı</h3>
                        <p class="stat-number"><?php echo $success_rate; ?>%</p>
                    </div>
                </div>
            </div>
            
            <?php if ($total_attempts > 0): ?>
                <div class="dashboard-row">
                    <div class="dashboard-chart-container">
                        <h2>Genel Performans</h2>
                        <div class="chart-container">
                            <canvas id="overallPerformanceChart"></canvas>
                        </div>
                    </div>
                    
                    <?php if (count($course_stats) > 0): ?>
                        <div class="dashboard-chart-container">
                            <h2>Derslere Göre Başarı</h2>
                            <div class="chart-container">
                                <canvas id="coursePerformanceChart"></canvas>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if (count($date_stats) > 0): ?>
                    <div class="content-card">
                        <div class="card-header">
                            <h2>Günlük Performans (Son 7 Gün)</h2>
                        </div>
                        <div class="card-body">
                            <div class="chart-container chart-container-large">
                                <canvas id="dailyPerformanceChart"></canvas>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (count($course_stats) > 0): ?>
                    <div class="content-card">
                        <div class="card-header">
                            <h2>Derslerinizin Detaylı İstatistikleri</h2>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Ders</th>
                                            <th>Çözülen Quiz</th>
                                            <th>Doğru Cevap</th>
                                            <th>Yanlış Cevap</th>
                                            <th>Başarı Oranı</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($course_stats as $course): ?>
                                            <?php 
                                            $wrong_answers = $course['course_attempts'] - $course['course_correct'];
                                            $success_rate = ($course['course_attempts'] > 0) ? 
                                                round(($course['course_correct'] / $course['course_attempts']) * 100, 2) : 0;
                                            ?>
                                            <tr>
                                                <td><?php echo e($course['course_name']); ?></td>
                                                <td><?php echo $course['course_attempts']; ?></td>
                                                <td class="correct-count"><?php echo $course['course_correct']; ?></td>
                                                <td class="wrong-count"><?php echo $wrong_answers; ?></td>
                                                <td>
                                                    <div class="progress mini-progress">
                                                        <div class="progress-bar" style="width: <?php echo $success_rate; ?>%;">
                                                            <span><?php echo $success_rate; ?>%</span>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="content-card">
                    <div class="card-body">
                        <div class="no-data">
                            <p>Henüz hiç quiz çözmediniz. İstatistikler görüntülemek için önce derslere katılıp quizleri çözmelisiniz.</p>
                            <div class="no-data-actions">
                                <a href="student_courses.php" class="btn-action">Derslere Git</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
    
    <?php if ($total_attempts > 0): ?>
        <script>
            // Overall Performance Chart (Donut)
            const overallCtx = document.getElementById('overallPerformanceChart').getContext('2d');
            const overallChart = new Chart(overallCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Doğru Cevaplar', 'Yanlış Cevaplar'],
                    datasets: [{
                        data: [<?php echo $correct_answers; ?>, <?php echo $total_attempts - $correct_answers; ?>],
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
            
            <?php if (count($course_stats) > 0): ?>
                // Course Performance Chart (Bar)
                const courseCtx = document.getElementById('coursePerformanceChart').getContext('2d');
                const courseChart = new Chart(courseCtx, {
                    type: 'bar',
                    data: {
                        labels: <?php echo json_encode($course_names); ?>,
                        datasets: [{
                            label: 'Başarı Oranı (%)',
                            data: <?php echo json_encode($course_success_rates); ?>,
                            backgroundColor: '#1A3C34',
                            borderWidth: 0,
                            borderRadius: 5
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                ticks: {
                                    callback: function(value) {
                                        return value + '%';
                                    }
                                }
                            }
                        }
                    }
                });
            <?php endif; ?>
            
            <?php if (count($date_stats) > 0): ?>
                // Daily Performance Chart (Line)
                const dailyCtx = document.getElementById('dailyPerformanceChart').getContext('2d');
                const dailyChart = new Chart(dailyCtx, {
                    type: 'line',
                    data: {
                        labels: <?php echo json_encode($dates); ?>,
                        datasets: [{
                            label: 'Günlük Başarı Oranı (%)',
                            data: <?php echo json_encode($date_success_rates); ?>,
                            fill: true,
                            backgroundColor: 'rgba(26, 60, 52, 0.2)',
                            borderColor: '#1A3C34',
                            tension: 0.4,
                            pointBackgroundColor: '#1A3C34',
                            pointBorderColor: '#fff',
                            pointRadius: 5,
                            pointHoverRadius: 7
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                ticks: {
                                    callback: function(value) {
                                        return value + '%';
                                    }
                                }
                            }
                        }
                    }
                });
            <?php endif; ?>
        </script>
    <?php endif; ?>
    <script src="js/main.js"></script>
</body>
</html>