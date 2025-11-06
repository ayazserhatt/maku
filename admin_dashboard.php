<?php
session_start();
include "config.php";

// Check if user is admin
require_admin();

// Get counts for dashboard
$sql_users = "SELECT COUNT(*) as total_users FROM users";
$sql_students = "SELECT COUNT(*) as total_students FROM users WHERE role='student'";
$sql_teachers = "SELECT COUNT(*) as total_teachers FROM users WHERE role='teacher'";
$sql_courses = "SELECT COUNT(*) as total_courses FROM courses";
$sql_quizzes = "SELECT COUNT(*) as total_quizzes FROM quizzes";
$sql_quiz_results = "SELECT COUNT(*) as total_completions FROM quiz_results";

$result_users = $conn->query($sql_users);
$result_students = $conn->query($sql_students);
$result_teachers = $conn->query($sql_teachers);
$result_courses = $conn->query($sql_courses);
$result_quizzes = $conn->query($sql_quizzes);
$result_quiz_results = $conn->query($sql_quiz_results);

$total_users = $result_users->fetch(PDO::FETCH_ASSOC)['total_users'];
$total_students = $result_students->fetch(PDO::FETCH_ASSOC)['total_students'];
$total_teachers = $result_teachers->fetch(PDO::FETCH_ASSOC)['total_teachers'];
$total_courses = $result_courses->fetch(PDO::FETCH_ASSOC)['total_courses'];
$total_quizzes = $result_quizzes->fetch(PDO::FETCH_ASSOC)['total_quizzes'];
$total_completions = $result_quiz_results->fetch(PDO::FETCH_ASSOC)['total_completions'];

// Get recent activity
$sql_recent = "SELECT u.name, u.role, qr.created_at, c.name as course_name, q.question
              FROM quiz_results qr
              JOIN users u ON qr.user_id = u.id
              JOIN quizzes q ON qr.quiz_id = q.id
              JOIN courses c ON q.course_id = c.id
              ORDER BY qr.created_at DESC
              LIMIT 5";
$result_recent = $conn->query($sql_recent);
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
    <title>MAKÜ - Yönetici Paneli</title>
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
                    <li><a href="admin_dashboard.php" class="active">Anasayfa</a></li>
                    <li><a href="admin_manage_users.php">Kullanıcı Yönetimi</a></li>
                    <li><a href="admin_manage_courses.php">Ders Yönetimi</a></li>
                    <li><a href="admin_manage_quizzes.php">Quiz Yönetimi</a></li>
                    <li><a href="admin_quiz_stats.php">İstatistikler</a></li>
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
                <li><a href="admin_dashboard.php" class="active"><i class="icon">🏠</i> Ana Sayfa</a></li>
                <li><a href="admin_manage_users.php"><i class="icon">👥</i> Kullanıcı Yönetimi</a></li>
                <li><a href="admin_manage_courses.php"><i class="icon">📚</i> Ders Yönetimi</a></li>
                <li><a href="admin_manage_quizzes.php"><i class="icon">📝</i> Quiz Yönetimi</a></li>
                <li><a href="admin_quiz_stats.php"><i class="icon">📊</i> İstatistikler</a></li>
                <li><a href="islem/logout.php"><i class="icon">🚪</i> Çıkış</a></li>
            </ul>
        </div>
        
        <main class="dashboard-content">
            <div class="dashboard-header">
                <h1>Hoş Geldin, <?php echo e($_SESSION["user_name"]); ?>!</h1>
                <p>Sistem durumunu ve istatistikleri aşağıda görebilirsiniz.</p>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">👨‍🎓</div>
                    <div class="stat-details">
                        <h3>Toplam Öğrenci</h3>
                        <p class="stat-number"><?php echo $total_students; ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">👨‍🏫</div>
                    <div class="stat-details">
                        <h3>Toplam Öğretmen</h3>
                        <p class="stat-number"><?php echo $total_teachers; ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">📚</div>
                    <div class="stat-details">
                        <h3>Toplam Ders</h3>
                        <p class="stat-number"><?php echo $total_courses; ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">📝</div>
                    <div class="stat-details">
                        <h3>Toplam Quiz</h3>
                        <p class="stat-number"><?php echo $total_quizzes; ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-details">
                        <h3>Tamamlanan Quizler</h3>
                        <p class="stat-number"><?php echo $total_completions; ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-details">
                        <h3>Toplam Kullanıcı</h3>
                        <p class="stat-number"><?php echo $total_users; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-row">
                <div class="dashboard-chart-container">
                    <h2>Kullanıcı Dağılımı</h2>
                    <div class="chart-container">
                        <canvas id="userDistributionChart"></canvas>
                    </div>
                </div>
                
                <div class="dashboard-recent-activities">
                    <h2>Son Aktiviteler</h2>
                    <div class="activity-list">
                        <?php if ($result_recent && $result_recent->rowCount() > 0): ?>
                            <?php while ($row = $result_recent->fetch(PDO::FETCH_ASSOC)): ?>
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <?php echo $row['role'] == 'student' ? '👨‍🎓' : '👨‍🏫'; ?>
                                    </div>
                                    <div class="activity-details">
                                        <h4><?php echo e($row['name']); ?> bir quiz tamamladı</h4>
                                        <p><?php echo e($row['course_name']); ?> dersinde</p>
                                        <span class="activity-time"><?php echo date('d.m.Y H:i', strtotime($row['created_at'])); ?></span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="no-activities">Henüz aktivite bulunmamaktadır.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="quick-actions">
                <h2>Hızlı İşlemler</h2>
                <div class="action-buttons">
                    <a href="admin_manage_users.php" class="action-button">
                        <i class="action-icon">👤</i>
                        <span>Yeni Kullanıcı Ekle</span>
                    </a>
                    <a href="admin_manage_courses.php" class="action-button">
                        <i class="action-icon">📚</i>
                        <span>Yeni Ders Ekle</span>
                    </a>
                    <a href="admin_manage_quizzes.php" class="action-button">
                        <i class="action-icon">📝</i>
                        <span>Quizleri Yönet</span>
                    </a>
                    <a href="admin_quiz_stats.php" class="action-button">
                        <i class="action-icon">📊</i>
                        <span>İstatistikleri Görüntüle</span>
                    </a>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        // User Distribution Chart
        const ctx = document.getElementById('userDistributionChart').getContext('2d');
        const userDistributionChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Öğrenciler', 'Öğretmenler', 'Yöneticiler'],
                datasets: [{
                    data: [
                        <?php echo $total_students; ?>, 
                        <?php echo $total_teachers; ?>, 
                        <?php echo $total_users - $total_students - $total_teachers; ?>
                    ],
                    backgroundColor: ['#1A3C34', '#800020', '#2F4F4F'],
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