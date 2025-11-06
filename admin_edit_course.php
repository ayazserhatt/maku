<?php
session_start();
include "config.php";

// Check if user is admin
require_admin();

// Check if course ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin_manage_courses.php");
    exit;
}
$course_id = intval($_GET['id']);

// Fetch course details
$sql_course = "SELECT c.id, c.name, c.description, c.teacher_id 
               FROM courses c 
               WHERE c.id = ?";
$stmt_course = $conn->prepare($sql_course);
$stmt_course->execute([$course_id]);
$course = $stmt_course->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    header("Location: admin_manage_courses.php");
    exit;
}

// Fetch teachers for dropdown
$sql_teachers = "SELECT id, name FROM users WHERE role = 'teacher' ORDER BY name";
$result_teachers = $conn->query($sql_teachers);
$teachers = $result_teachers ? $result_teachers->fetchAll(PDO::FETCH_ASSOC) : [];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'edit_course') {
    $name = secure_input(trim($_POST["name"]));
    $description = secure_input(trim($_POST["description"]));
    $teacher_id = !empty($_POST["teacher_id"]) ? intval($_POST["teacher_id"]) : null;
    
    // Validate input
    if (empty($name)) {
        $error_message = "Ders adı boş olamaz!";
    } else {
        try {
            // Update course
            $sql = "UPDATE courses SET name = ?, description = ?, teacher_id = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$name, $description, $teacher_id, $course_id]);
            
            $success_message = "Ders başarıyla güncellendi!";
            // Refresh course data
            $stmt_course->execute([$course_id]);
            $course = $stmt_course->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $error_message = "Ders güncellenirken bir hata oluştu: " . $e->getMessage();
        }
    }
}
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
    <title>MAKÜ - Ders Düzenle</title>
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
                    <li><a href="admin_dashboard.php">Anasayfa</a></li>
                    <li><a href="admin_manage_users.php">Kullanıcı Yönetimi</a></li>
                    <li><a href="admin_manage_courses.php" class="active">Ders Yönetimi</a></li>
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
                <li><a href="admin_dashboard.php"><i class="icon">🏠</i> Ana Sayfa</a></li>
                <li><a href="admin_manage_users.php"><i class="icon">👥</i> Kullanıcı Yönetimi</a></li>
                <li><a href="admin_manage_courses.php" class="active"><i class="icon">📚</i> Ders Yönetimi</a></li>
                <li><a href="admin_manage_quizzes.php"><i class="icon">📝</i> Quiz Yönetimi</a></li>
                <li><a href="admin_quiz_stats.php"><i class="icon">📊</i> İstatistikler</a></li>
                <li><a href="islem/logout.php"><i class="icon">🚪</i> Çıkış</a></li>
            </ul>
        </div>
        
        <main class="dashboard-content">
            <div class="dashboard-header">
                <h1>Ders Düzenle</h1>
                <p>Ders bilgilerini aşağıda düzenleyebilirsiniz.</p>
            </div>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div class="content-card">
                <div class="card-header">
                    <h2>Ders Bilgilerini Düzenle</h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="" class="admin-form">
                        <input type="hidden" name="action" value="edit_course">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Ders Adı:</label>
                                <input type="text" id="name" name="name" value="<?php echo e($course['name']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="teacher_id">Öğretmen:</label>
                                <select id="teacher_id" name="teacher_id">
                                    <option value="">-- Öğretmen Seçin --</option>
                                    <?php foreach ($teachers as $teacher): ?>
                                        <option value="<?php echo $teacher['id']; ?>" <?php echo $course['teacher_id'] == $teacher['id'] ? 'selected' : ''; ?>><?php echo e($teacher['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (empty($teachers)): ?>
                                    <small class="form-hint">Öğretmen bulunamadı. Önce öğretmen ekleyin.</small>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Açıklama:</label>
                            <textarea id="description" name="description" rows="4"><?php echo e($course['description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="primary-button">
                                <i class="button-icon">💾</i> Değişiklikleri Kaydet
                            </button>
                            <a href="admin_manage_courses.php" class="secondary-button">
                                <i class="button-icon">🔙</i> Geri Dön
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    
    <script src="js/main.js"></script>
</body>
</html>