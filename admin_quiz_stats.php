<?php
session_start();
include "config.php";

// Check if user is admin
require_admin();

// Get overall quiz statistics
$sql_total = "SELECT COUNT(*) AS total_attempts FROM quiz_results";
$stmt_total = $pdo->query($sql_total);
$total_attempts = $stmt_total->fetch()["total_attempts"];

// Get correct answers count
$sql_success = "SELECT COUNT(*) AS correct_answers FROM quiz_results WHERE is_correct = 1";
$stmt_success = $pdo->query($sql_success);
$correct_answers = $stmt_success->fetch()["correct_answers"];

// Calculate success rate
$success_rate = ($total_attempts > 0) ? round(($correct_answers / $total_attempts) * 100, 2) : 0;

// Get course-based success rates
$sql_courses = "SELECT 
                    c.id,
                    c.name AS course_name, 
                    COUNT(qr.id) AS attempts, 
                    SUM(qr.is_correct) AS correct_answers,
                    (SELECT COUNT(DISTINCT user_id) FROM quiz_results qr2 
                     JOIN quizzes q2 ON qr2.quiz_id = q2.id 
                     WHERE q2.course_id = c.id) AS student_count
                FROM courses c
                LEFT JOIN quizzes q ON q.course_id = c.id
                LEFT JOIN quiz_results qr ON qr.quiz_id = q.id
                GROUP BY c.id, c.name
                ORDER BY attempts DESC";

$stmt_courses = $pdo->query($sql_courses);
$courses_data = $stmt_courses->fetchAll();

// Get top students based on success rate
$sql_top_students = "SELECT 
                        u.id,
                        u.name, 
                        COUNT(qr.id) AS attempts, 
                        SUM(qr.is_correct) AS correct,
                        (SUM(qr.is_correct) / COUNT(qr.id) * 100) AS success_rate
                     FROM users u
                     JOIN quiz_results qr ON u.id = qr.user_id
                     WHERE u.role = 'student'
                     GROUP BY u.id, u.name
                     HAVING attempts > 0
                     ORDER BY success_rate DESC, attempts DESC
                     LIMIT 5";
$stmt_top_students = $pdo->query($sql_top_students);
$top_students = $stmt_top_students->fetchAll();

// Get recent quiz results
$sql_recent = "SELECT 
                u.name AS student_name, 
                c.name AS course_name, 
                q.question, 
                qr.is_correct, 
                qr.created_at
              FROM quiz_results qr
              JOIN users u ON qr.user_id = u.id
              JOIN quizzes q ON qr.quiz_id = q.id
              JOIN courses c ON q.course_id = c.id
              ORDER BY qr.created_at DESC
              LIMIT 10";
$stmt_recent = $pdo->query($sql_recent);
$recent_results = $stmt_recent->fetchAll();

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Mehmet Akif Ersoy Üniversitesi Yönetici Yönetim Paneli">
    <meta name="keywords" content="MAKÜ, yönetici, yönetim paneli">
    <meta name="author" content="Mehmet Akif Ersoy Üniversitesi">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#1A3C34">
    <title>MAKÜ - Quiz İstatistikleri</title>
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
                    <li><a href="admin_dashboard.php">Anasayfa</a></li>
                    <li><a href="admin_manage_users.php">Kullanıcı Yönetimi</a></li>
                    <li><a href="admin_manage_courses.php">Ders Yönetimi</a></li>
                    <li><a href="admin_manage_quizzes.php">Quiz Yönetimi</a></li>
                    <li><a href="admin_quiz_stats.php" class="active">İstatistikler</a></li>
                    <li><a href="islem/logout.php" class="btn-action">Çıkış</a></li>
                </ul>
            </nav>
        </div>
    </header>
    
    <div class="dashboard-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="img/school-logo.jpg" alt="MAKÜ Logo" class="sidebar-logo">
                <h3>Yönetici Paneli</h3>
            </div>
            <ul class="sidebar-menu">
                <li><a href="admin_dashboard.php"><i class="icon">🏠</i> Ana Sayfa</a></li>
                <li><a href="admin_manage_users.php"><i class="icon">👥</i> Kullanıcı Yönetimi</a></li>
                <li><a href="admin_manage_courses.php"><i class="icon">📚</i> Ders Yönetimi</a></li>
                <li><a href="admin_manage_quizzes.php"><i class="icon">📝</i> Quiz Yönetimi</a></li>
                <li><a href="admin_quiz_stats.php" class="active"><i class="icon">📊</i> İstatistikler</a></li>
                <li><a href="islem/logout.php"><i class="icon">🚪</i> Çıkış</a></li>
            </ul>
        </div>
        
        <main class="dashboard-content">
            <div class="dashboard-header">
                <h1>Quiz İstatistikleri</h1>
                <p>Tüm quizlerin başarı oranlarını ve istatistiklerini görüntüleyin.</p>
            </div>
            
            <div class="stats-summary">
                <div class="stat-card">
                    <div class="stat-icon">📝</div>
                    <div class="stat-details">
                        <h3>Toplam Quiz Çözüm Sayısı</h3>
                        <p class="stat-number"><?php echo $total_attempts; ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-details">
                        <h3>Doğru Cevaplar</h3>
                        <p class="stat-number"><?php echo $correct_answers; ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-details">
                        <h3>Genel Başarı Oranı</h3>
                        <p class="stat-number"><?php echo $success_rate; ?>%</p>
                    </div>
                </div>
            </div>
            
            <div class="chart-row">
                <div class="chart-container">
                    <h2>Genel Başarı Oranı</h2>
                    <canvas id="successRateChart"></canvas>
                </div>
                
                <div class="chart-container">
                    <h2>Ders Bazlı Başarı Oranları</h2>
                    <canvas id="courseSuccessChart"></canvas>
                </div>
            </div>
            
            <div class="content-card">
                <div class="card-header">
                    <h2>Ders Bazlı İstatistikler</h2>
                </div>
                
                <?php if (!empty($courses_data)): ?>
                    <div class="course-stats">
                        <?php foreach ($courses_data as $row): ?>
                            <?php 
                                $course_success_rate = ($row['attempts'] > 0) ? round(($row['correct_answers'] / $row['attempts']) * 100, 2) : 0;
                                $completion_width = $course_success_rate . '%';
                            ?>
                            <div class="course-stat-item">
                                <h3><?php echo e($row['course_name']); ?></h3>
                                <div class="course-stat-meta">
                                    <span>Katılımcı Öğrenci: <?php echo $row['student_count'] ?: 0; ?></span>
                                    <span>Toplam Çözüm: <?php echo $row['attempts'] ?: 0; ?></span>
                                    <span>Başarı Oranı: <?php echo $course_success_rate; ?>%</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar" style="width: <?php echo $completion_width; ?>;">
                                        <span><?php echo $course_success_rate; ?>%</span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-data">
                        <p>Henüz hiç quiz sonucu bulunmamaktadır.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="dashboard-row">
                <div class="content-card">
                    <div class="card-header">
                        <h2>En Başarılı Öğrenciler</h2>
                    </div>
                    
                    <?php if (!empty($top_students)): ?>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Öğrenci</th>
                                        <th>Toplam Quiz</th>
                                        <th>Doğru Sayısı</th>
                                        <th>Başarı Oranı</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($top_students as $row): ?>
                                        <tr>
                                            <td><?php echo e($row['name']); ?></td>
                                            <td><?php echo $row['attempts']; ?></td>
                                            <td><?php echo $row['correct']; ?></td>
                                            <td>
                                                <div class="progress">
                                                    <div class="progress-bar" style="width: <?php echo round($row['success_rate']); ?>%;">
                                                        <span><?php echo round($row['success_rate'], 2); ?>%</span>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="no-data">
                            <p>Henüz hiç öğrenci quiz çözmemiştir.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="content-card">
                    <div class="card-header">
                        <h2>Son Quiz Sonuçları</h2>
                    </div>
                    
                    <?php if (!empty($recent_results)): ?>
                        <div class="recent-results">
                            <?php foreach ($recent_results as $row): ?>
                                <div class="recent-result-item">
                                    <div class="result-icon <?php echo $row['is_correct'] ? 'correct' : 'wrong'; ?>">
                                        <?php echo $row['is_correct'] ? '✅' : '❌'; ?>
                                    </div>
                                    <div class="result-details">
                                        <p class="result-course"><?php echo e($row['course_name']); ?></p>
                                        <p class="result-question"><?php echo e($row['question']); ?></p>
                                        <div class="result-meta">
                                            <span class="result-student"><?php echo e($row['student_name']); ?></span>
                                            <span class="result-time"><?php echo date('d.m.Y H:i', strtotime($row['created_at'])); ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-data">
                            <p>Henüz hiç quiz sonucu bulunmamaktadır.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        // Overall Success Rate Chart
        const successRateCtx = document.getElementById('successRateChart').getContext('2d');
        const successRateChart = new Chart(successRateCtx, {
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
        
        // Course Success Chart
        const courseCtx = document.getElementById('courseSuccessChart').getContext('2d');
        const courseSuccessChart = new Chart(courseCtx, {
            type: 'bar',
            data: {
                labels: [
                    <?php 
                    $result_courses->data_seek(0);
                    while ($row = $result_courses->fetch_assoc()) {
                        echo "'" . addslashes($row['course_name']) . "', ";
                    }
                    ?>
                ],
                datasets: [{
                    label: 'Başarı Oranı (%)',
                    data: [
                        <?php 
                        $result_courses->data_seek(0);
                        while ($row = $result_courses->fetch_assoc()) {
                            $rate = ($row['attempts'] > 0) ? round(($row['correct_answers'] / $row['attempts']) * 100, 2) : 0;
                            echo $rate . ", ";
                        }
                        ?>
                    ],
                    backgroundColor: '#1A3C34',
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
                        title: {
                            display: true,
                            text: 'Başarı Oranı (%)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Dersler'
                        }
                    }
                }
            }
        });
    </script>
    <script src="js/main.js"></script>
    <script src="js/stats.js"></script>
</body>
</html>
