<?php
session_start();
include "config.php";

// Check if user is a teacher
require_teacher();

$teacher_id = $_SESSION["user_id"];
$error_message = "";

// Check if quiz ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: add_quiz.php");
    exit;
}

$quiz_id = intval($_GET['id']);

// Verify that the quiz belongs to a course taught by this teacher
$sql_verify = "SELECT q.id 
               FROM quizzes q 
               JOIN courses c ON q.course_id = c.id 
               WHERE q.id = :quiz_id AND c.teacher_id = :teacher_id";
$stmt_verify = $pdo->prepare($sql_verify);
$stmt_verify->execute([
    'quiz_id' => $quiz_id,
    'teacher_id' => $teacher_id
]);
$quiz_exists = $stmt_verify->fetch();

if (!$quiz_exists) {
    $_SESSION['error_message'] = "Bu quizi silmek için yetkili değilsiniz!";
    header("Location: add_quiz.php");
    exit;
}

// If confirmed, delete the quiz
if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
    $sql_delete = "DELETE FROM quizzes WHERE id = :quiz_id";
    $stmt_delete = $pdo->prepare($sql_delete);
    
    if ($stmt_delete->execute(['quiz_id' => $quiz_id])) {
        $_SESSION['success_message'] = "Quiz başarıyla silindi!";
    } else {
        $_SESSION['error_message'] = "Quiz silinirken bir hata oluştu!";
    }
    
    header("Location: add_quiz.php");
    exit;
}

// Get quiz details for confirmation
$sql_quiz = "SELECT q.question, c.name as course_name 
             FROM quizzes q 
             JOIN courses c ON q.course_id = c.id 
             WHERE q.id = :quiz_id";
$stmt_quiz = $pdo->prepare($sql_quiz);
$stmt_quiz->execute(['quiz_id' => $quiz_id]);
$quiz = $stmt_quiz->fetch();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Mehmet Akif Ersoy Üniversitesi Quiz Silme Sayfası">
    <meta name="keywords" content="MAKÜ, quiz, sınav, öğretmen, silme">
    <meta name="author" content="Mehmet Akif Ersoy Üniversitesi">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#1A3C34">
    <title>MAKÜ - Quiz Sil</title>
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
                    <li><a href="teacher_dashboard.php">Anasayfa</a></li>
                    <li><a href="teacher_manage_courses.php">Derslerim</a></li>
                    <li><a href="add_quiz.php" class="active">Quiz Ekle</a></li>
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
                <li><a href="teacher_dashboard.php"><i class="icon">🏠</i> Ana Sayfa</a></li>
                <li><a href="teacher_manage_courses.php"><i class="icon">📚</i> Derslerim</a></li>
                <li><a href="teacher_add_course.php"><i class="icon">➕</i> Ders Ekle</a></li>
                <li><a href="add_quiz.php" class="active"><i class="icon">📝</i> Quiz Ekle</a></li>
                <li><a href="islem/logout.php"><i class="icon">🚪</i> Çıkış</a></li>
            </ul>
        </div>
        
        <main class="dashboard-content">
            <div class="dashboard-header">
                <h1>Quiz Sil</h1>
                <p>Bu quizi silmek istediğinizden emin misiniz?</p>
            </div>
            
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div class="content-card">
                <div class="card-header">
                    <h2>Silme Onayı</h2>
                </div>
                <div class="card-body">
                    <div class="delete-confirmation">
                        <div class="warning-icon">⚠️</div>
                        <p class="warning-message">
                            <strong>DİKKAT:</strong> "<span class="highlight"><?php echo e($quiz['question']); ?></span>" sorusunu 
                            <strong><?php echo e($quiz['course_name']); ?></strong> dersinden silmek üzeresiniz. 
                            Bu işlem geri alınamaz ve tüm öğrenci yanıtları da silinecektir.
                        </p>
                        
                        <div class="confirmation-actions">
                            <a href="delete_quiz.php?id=<?php echo $quiz_id; ?>&confirm=yes" class="primary-button danger-button">
                                <i class="button-icon">🗑️</i> Evet, Sil
                            </a>
                            <a href="add_quiz.php" class="secondary-button">
                                <i class="button-icon">✖️</i> İptal
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="js/main.js"></script>
</body>
</html>