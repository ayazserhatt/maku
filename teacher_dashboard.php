<?php
session_start();
include "config.php";

// Check if user is a teacher
require_teacher();

$teacher_id = $_SESSION["user_id"];

// Get teacher's courses
$sql_courses = "SELECT COUNT(*) as total_courses FROM courses WHERE teacher_id = :teacher_id";
$stmt_courses = $pdo->prepare($sql_courses);
$stmt_courses->execute(['teacher_id' => $teacher_id]);
$total_courses = $stmt_courses->fetch()['total_courses'];

// Get teacher's quizzes
$sql_quizzes = "SELECT COUNT(*) as total_quizzes 
                FROM quizzes q 
                JOIN courses c ON q.course_id = c.id 
                WHERE c.teacher_id = :teacher_id";
$stmt_quizzes = $pdo->prepare($sql_quizzes);
$stmt_quizzes->execute(['teacher_id' => $teacher_id]);
$total_quizzes = $stmt_quizzes->fetch()['total_quizzes'];

// Get total students who completed teacher's quizzes
$sql_students = "SELECT COUNT(DISTINCT qr.user_id) as total_students 
                FROM quiz_results qr
                JOIN quizzes q ON qr.quiz_id = q.id
                JOIN courses c ON q.course_id = c.id
                WHERE c.teacher_id = :teacher_id";
$stmt_students = $pdo->prepare($sql_students);
$stmt_students->execute(['teacher_id' => $teacher_id]);
$total_students = $stmt_students->fetch()['total_students'];

// Get recent quiz results
$sql_recent = "SELECT 
                u.name AS student_name, 
                c.name AS course_name, 
                q.question, 
                qr.is_correct, 
                qr.created_at,
                qr.user_answer
              FROM quiz_results qr
              JOIN users u ON qr.user_id = u.id
              JOIN quizzes q ON qr.quiz_id = q.id
              JOIN courses c ON q.course_id = c.id
              WHERE c.teacher_id = :teacher_id
              ORDER BY qr.created_at DESC
              LIMIT 5";
$stmt_recent = $pdo->prepare($sql_recent);
$stmt_recent->execute(['teacher_id' => $teacher_id]);
$result_recent = $stmt_recent->fetchAll();

// Get teacher's courses for course list
$sql_teacher_courses = "SELECT id, name, description FROM courses WHERE teacher_id = :teacher_id ORDER BY name";
$stmt_teacher_courses = $pdo->prepare($sql_teacher_courses);
$stmt_teacher_courses->execute(['teacher_id' => $teacher_id]);
$result_teacher_courses = $stmt_teacher_courses->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Mehmet Akif Ersoy Üniversitesi Öğretmen Yönetim Paneli">
    <meta name="keywords" content="MAKÜ, öğretmen, yönetim paneli">
    <meta name="author" content="Mehmet Akif Ersoy Üniversitesi">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#1A3C34">
    <title>MAKÜ - Öğretmen Paneli</title>
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
                    <li><a href="teacher_dashboard.php" class="active">Anasayfa</a></li>
                    <li><a href="teacher_manage_courses.php">Derslerim</a></li>
                    <li><a href="add_quiz.php">Quiz Ekle</a></li>
                    <li><a href="islem/logout.php" class="btn-action">Çıkış</a></li>
                </ul>
            </nav>
        </div>
    </header>
    
    <div class="dashboard-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="img/school-logo.jpg" alt="MAKÜ Logo" class="sidebar-logo">
                <h3>Öğretmen Paneli</h3>
            </div>
            <ul class="sidebar-menu">
                <li><a href="teacher_dashboard.php" class="active"><i class="icon">🏠</i> Ana Sayfa</a></li>
                <li><a href="teacher_manage_courses.php"><i class="icon">📚</i> Derslerim</a></li>
                <li><a href="teacher_add_course.php"><i class="icon">➕</i> Ders Ekle</a></li>
                <li><a href="add_quiz.php"><i class="icon">📝</i> Quiz Ekle</a></li>
                <li><a href="islem/logout.php"><i class="icon">🚪</i> Çıkış</a></li>
            </ul>
        </div>
        
        <main class="dashboard-content">
            <div class="dashboard-header">
                <h1>Hoş Geldin, <?php echo e($_SESSION["user_name"]); ?>!</h1>
                <p>Öğrencileriniz, dersleriniz ve quizleriniz hakkında genel bilgileri aşağıda görebilirsiniz.</p>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📚</div>
                    <div class="stat-details">
                        <h3>Derslerim</h3>
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
                    <div class="stat-icon">👨‍🎓</div>
                    <div class="stat-details">
                        <h3>Öğrenci Sayısı</h3>
                        <p class="stat-number"><?php echo $total_students; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-row">
                <div class="dashboard-recent-activities">
                    <h2>Son Quiz Sonuçları</h2>
                    <div class="activity-list">
                        <?php if ($result_recent && count($result_recent) > 0): ?>
                            <?php foreach ($result_recent as $row): ?>
                                <div class="activity-item">
                                    <div class="activity-icon <?php echo $row['is_correct'] ? 'correct' : 'wrong'; ?>">
                                        <?php echo $row['is_correct'] ? '✅' : '❌'; ?>
                                    </div>
                                    <div class="activity-details">
                                        <h4><?php echo e($row['student_name']); ?> - <?php echo e($row['course_name']); ?></h4>
                                        <p><?php echo e($row['question']); ?></p>
                                        <p class="activity-answer">Cevap: <?php echo e($row['user_answer']); ?></p>
                                        <span class="activity-time"><?php echo date('d.m.Y H:i', strtotime($row['created_at'])); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="no-activities">Henüz hiç quiz sonucu bulunmamaktadır.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="dashboard-course-list">
                    <h2>Derslerim</h2>
                    <div class="course-list">
                        <?php if ($result_teacher_courses && count($result_teacher_courses) > 0): ?>
                            <?php foreach ($result_teacher_courses as $row): ?>
                                <div class="course-item">
                                    <h3><?php echo e($row['name']); ?></h3>
                                    <p><?php echo e($row['description'] ? $row['description'] : 'Açıklama yok'); ?></p>
                                    <div class="course-actions">
                                        <a href="teacher_manage_courses.php?course_id=<?php echo $row['id']; ?>" class="btn-action">Yönet</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-courses">
                                <p>Henüz hiç dersiniz yok.</p>
                                <a href="teacher_add_course.php" class="btn-action">Ders Ekle</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="quick-actions">
                <h2>Hızlı İşlemler</h2>
                <div class="action-buttons">
                    <a href="teacher_add_course.php" class="action-button">
                        <i class="action-icon">📚</i>
                        <span>Yeni Ders Ekle</span>
                    </a>
                    <a href="add_quiz.php" class="action-button">
                        <i class="action-icon">📝</i>
                        <span>Yeni Quiz Oluştur</span>
                    </a>
                    <a href="teacher_manage_courses.php" class="action-button">
                        <i class="action-icon">📊</i>
                        <span>Derslerimi Yönet</span>
                    </a>
                </div>
            </div>
        </main>
    </div>
    
    <script src="js/main.js"></script>
</body>
</html>
