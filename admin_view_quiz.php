<?php
session_start();
include "config.php";

// Check if user is admin
require_admin();

// Get quiz ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin_manage_quizzes.php");
    exit;
}

$quiz_id = intval($_GET['id']);

// Get quiz details with course information
$sql_quiz = "SELECT q.*, c.name AS course_name, u.name AS teacher_name
             FROM quizzes q
             JOIN courses c ON q.course_id = c.id
             LEFT JOIN users u ON c.teacher_id = u.id
             WHERE q.id = :quiz_id";
$stmt_quiz = $pdo->prepare($sql_quiz);
$stmt_quiz->execute(['quiz_id' => $quiz_id]);
$quiz = $stmt_quiz->fetch();

// If quiz not found, redirect
if (!$quiz) {
    header("Location: admin_manage_quizzes.php");
    exit;
}

// Get quiz statistics
$sql_stats = "SELECT 
                COUNT(*) AS total_attempts,
                SUM(CASE WHEN is_correct = true THEN 1 ELSE 0 END) AS correct_answers,
                COUNT(DISTINCT user_id) AS unique_students
              FROM quiz_results
              WHERE quiz_id = :quiz_id";
$stmt_stats = $pdo->prepare($sql_stats);
$stmt_stats->execute(['quiz_id' => $quiz_id]);
$stats = $stmt_stats->fetch();

$success_rate = ($stats['total_attempts'] > 0) ? round(($stats['correct_answers'] / $stats['total_attempts']) * 100, 2) : 0;

// Get student results for this quiz
$sql_results = "SELECT 
                    u.name AS student_name,
                    u.email AS student_email,
                    qr.user_answer,
                    qr.is_correct,
                    qr.created_at
                FROM quiz_results qr
                JOIN users u ON qr.user_id = u.id
                WHERE qr.quiz_id = :quiz_id
                ORDER BY qr.created_at DESC";
$stmt_results = $pdo->prepare($sql_results);
$stmt_results->execute(['quiz_id' => $quiz_id]);
$results = $stmt_results->fetchAll();

// Get answer distribution
$sql_distribution = "SELECT 
                        user_answer,
                        COUNT(*) AS count
                     FROM quiz_results
                     WHERE quiz_id = :quiz_id
                     GROUP BY user_answer
                     ORDER BY user_answer";
$stmt_distribution = $pdo->prepare($sql_distribution);
$stmt_distribution->execute(['quiz_id' => $quiz_id]);
$distribution = $stmt_distribution->fetchAll();

$answer_counts = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0];
foreach ($distribution as $dist) {
    $answer_counts[$dist['user_answer']] = $dist['count'];
}

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Mehmet Akif Ersoy Üniversitesi Yönetici Quiz Detayı">
    <meta name="keywords" content="MAKÜ, yönetici, quiz detay">
    <meta name="author" content="Mehmet Akif Ersoy Üniversitesi">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#1A3C34">
    <title>MAKÜ - Quiz Detayı</title>
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
                    <li><a href="admin_manage_quizzes.php" class="active">Quiz Yönetimi</a></li>
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
                <li><a href="admin_dashboard.php"><i class="icon">🏠</i> Ana Sayfa</a></li>
                <li><a href="admin_manage_users.php"><i class="icon">👥</i> Kullanıcı Yönetimi</a></li>
                <li><a href="admin_manage_courses.php"><i class="icon">📚</i> Ders Yönetimi</a></li>
                <li><a href="admin_manage_quizzes.php" class="active"><i class="icon">📝</i> Quiz Yönetimi</a></li>
                <li><a href="admin_quiz_stats.php"><i class="icon">📊</i> İstatistikler</a></li>
                <li><a href="islem/logout.php"><i class="icon">🚪</i> Çıkış</a></li>
            </ul>
        </div>
        
        <main class="dashboard-content">
            <div class="dashboard-header">
                <div>
                    <h1>Quiz Detayı</h1>
                    <p><?php echo e($quiz['course_name']); ?> - ID: #<?php echo $quiz_id; ?></p>
                </div>
                <div class="action-buttons">
                    <a href="edit_quiz.php?id=<?php echo $quiz_id; ?>" class="btn btn-warning">✏️ Düzenle</a>
                    <a href="delete_quiz.php?id=<?php echo $quiz_id; ?>" class="btn btn-danger" onclick="return confirm('Bu quiz\'i silmek istediğinizden emin misiniz?')">🗑️ Sil</a>
                    <a href="admin_manage_quizzes.php" class="btn btn-secondary">← Geri</a>
                </div>
            </div>

            <!-- Quiz Question Card -->
            <div class="content-card">
                <div class="card-header">
                    <h2>Soru</h2>
                </div>
                <div class="quiz-question-display">
                    <p class="question-text"><?php echo e($quiz['question']); ?></p>
                    
                    <div class="quiz-options">
                        <div class="option-item <?php echo ($quiz['correct_option'] === 'A') ? 'correct-answer' : ''; ?>">
                            <span class="option-label">A)</span>
                            <span class="option-text"><?php echo e($quiz['option_a']); ?></span>
                            <?php if ($quiz['correct_option'] === 'A'): ?>
                                <span class="correct-badge">✓ Doğru Cevap</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="option-item <?php echo ($quiz['correct_option'] === 'B') ? 'correct-answer' : ''; ?>">
                            <span class="option-label">B)</span>
                            <span class="option-text"><?php echo e($quiz['option_b']); ?></span>
                            <?php if ($quiz['correct_option'] === 'B'): ?>
                                <span class="correct-badge">✓ Doğru Cevap</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="option-item <?php echo ($quiz['correct_option'] === 'C') ? 'correct-answer' : ''; ?>">
                            <span class="option-label">C)</span>
                            <span class="option-text"><?php echo e($quiz['option_c']); ?></span>
                            <?php if ($quiz['correct_option'] === 'C'): ?>
                                <span class="correct-badge">✓ Doğru Cevap</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="option-item <?php echo ($quiz['correct_option'] === 'D') ? 'correct-answer' : ''; ?>">
                            <span class="option-label">D)</span>
                            <span class="option-text"><?php echo e($quiz['option_d']); ?></span>
                            <?php if ($quiz['correct_option'] === 'D'): ?>
                                <span class="correct-badge">✓ Doğru Cevap</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-details">
                        <h3><?php echo $stats['total_attempts']; ?></h3>
                        <p>Toplam Deneme</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-details">
                        <h3><?php echo $stats['unique_students']; ?></h3>
                        <p>Benzersiz Öğrenci</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-details">
                        <h3><?php echo $stats['correct_answers']; ?></h3>
                        <p>Doğru Cevap</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">📈</div>
                    <div class="stat-details">
                        <h3><?php echo $success_rate; ?>%</h3>
                        <p>Başarı Oranı</p>
                    </div>
                </div>
            </div>

            <!-- Answer Distribution Chart -->
            <?php if ($stats['total_attempts'] > 0): ?>
            <div class="content-card">
                <div class="card-header">
                    <h2>Cevap Dağılımı</h2>
                </div>
                <div class="chart-container">
                    <canvas id="answerDistributionChart"></canvas>
                </div>
            </div>
            <?php endif; ?>

            <!-- Student Results Table -->
            <div class="content-card">
                <div class="card-header">
                    <h2>Öğrenci Sonuçları</h2>
                </div>
                
                <?php if (!empty($results)): ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Öğrenci Adı</th>
                                    <th>Email</th>
                                    <th>Verilen Cevap</th>
                                    <th>Sonuç</th>
                                    <th>Tarih</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($results as $result): ?>
                                <tr>
                                    <td><?php echo e($result['student_name']); ?></td>
                                    <td><?php echo e($result['student_email']); ?></td>
                                    <td>
                                        <span class="answer-badge <?php echo ($result['user_answer'] === $quiz['correct_option']) ? 'badge-success' : 'badge-danger'; ?>">
                                            <?php echo e($result['user_answer']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($result['is_correct']): ?>
                                            <span class="badge badge-success">✓ Doğru</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">✗ Yanlış</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d.m.Y H:i', strtotime($result['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <p>🎯 Bu quiz henüz hiçbir öğrenci tarafından çözülmedi.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Quiz Information -->
            <div class="content-card">
                <div class="card-header">
                    <h2>Quiz Bilgileri</h2>
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Ders:</span>
                        <span class="info-value"><?php echo e($quiz['course_name']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Öğretmen:</span>
                        <span class="info-value"><?php echo e($quiz['teacher_name'] ?? 'Belirtilmemiş'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Oluşturulma Tarihi:</span>
                        <span class="info-value"><?php echo date('d.m.Y H:i', strtotime($quiz['created_at'])); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Son Güncellenme:</span>
                        <span class="info-value"><?php echo date('d.m.Y H:i', strtotime($quiz['updated_at'])); ?></span>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="js/main.js"></script>
    <script>
        // Answer Distribution Chart
        <?php if ($stats['total_attempts'] > 0): ?>
        const ctx = document.getElementById('answerDistributionChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['A', 'B', 'C', 'D'],
                datasets: [{
                    label: 'Seçilen Cevap Sayısı',
                    data: [
                        <?php echo $answer_counts['A']; ?>,
                        <?php echo $answer_counts['B']; ?>,
                        <?php echo $answer_counts['C']; ?>,
                        <?php echo $answer_counts['D']; ?>
                    ],
                    backgroundColor: [
                        '<?php echo ($quiz["correct_option"] === "A") ? "#4CAF50" : "#FF6384"; ?>',
                        '<?php echo ($quiz["correct_option"] === "B") ? "#4CAF50" : "#FF6384"; ?>',
                        '<?php echo ($quiz["correct_option"] === "C") ? "#4CAF50" : "#FF6384"; ?>',
                        '<?php echo ($quiz["correct_option"] === "D") ? "#4CAF50" : "#FF6384"; ?>'
                    ],
                    borderColor: [
                        '<?php echo ($quiz["correct_option"] === "A") ? "#45a049" : "#ff4567"; ?>',
                        '<?php echo ($quiz["correct_option"] === "B") ? "#45a049" : "#ff4567"; ?>',
                        '<?php echo ($quiz["correct_option"] === "C") ? "#45a049" : "#ff4567"; ?>',
                        '<?php echo ($quiz["correct_option"] === "D") ? "#45a049" : "#ff4567"; ?>'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Hangi Şık Kaç Kez Seçildi? (Yeşil = Doğru Cevap)'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>
