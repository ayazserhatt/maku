<?php
session_start();
include "config.php";

// Check if user is a teacher
require_teacher();

$teacher_id = $_SESSION["user_id"];
$success_message = "";
$error_message = "";

// Handle course creation
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = secure_input(trim($_POST["name"]));
    $description = secure_input(trim($_POST["description"]));
    
    // Validate input
    if (empty($name)) {
        $error_message = "Ders adı boş olamaz!";
    } else {
        try {
            // Insert new course
            $sql = "INSERT INTO courses (name, description, teacher_id) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$name, $description, $teacher_id]);
            
            $success_message = "Ders başarıyla eklendi!";
            $course_id = $conn->lastInsertId();
        } catch (Exception $e) {
            $error_message = "Ders eklenirken bir hata oluştu: " . $e->getMessage();
        }
    }
}
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
    <title>MAKÜ - Ders Ekle</title>
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
                    <li><a href="teacher_add_course.php" class="active">Ders Ekle</a></li>
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
                <li><a href="teacher_dashboard.php"><i class="icon">🏠</i> Ana Sayfa</a></li>
                <li><a href="teacher_manage_courses.php"><i class="icon">📚</i> Derslerim</a></li>
                <li><a href="teacher_add_course.php" class="active"><i class="icon">➕</i> Ders Ekle</a></li>
                <li><a href="add_quiz.php"><i class="icon">📝</i> Quiz Ekle</a></li>
                <li><a href="islem/logout.php"><i class="icon">🚪</i> Çıkış</a></li>
            </ul>
        </div>
        
        <main class="dashboard-content">
            <div class="dashboard-header">
                <h1>Yeni Ders Ekle</h1>
                <p>Sisteme yeni bir ders ekleyebilirsiniz.</p>
            </div>
            
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <?php echo $success_message; ?>
                    <div class="alert-actions">
                        <a href="teacher_manage_courses.php" class="btn-action">Derslerime Git</a>
                        <a href="add_quiz.php?course_id=<?php echo $course_id; ?>" class="btn-action">Quiz Ekle</a>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div class="content-card">
                <div class="card-header">
                    <h2>Ders Bilgileri</h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="" class="admin-form">
                        <div class="form-group">
                            <label for="name">Ders Adı:</label>
                            <input type="text" id="name" name="name" value="<?php echo isset($_POST['name']) ? e($_POST['name']) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Açıklama:</label>
                            <textarea id="description" name="description" rows="5"><?php echo isset($_POST['description']) ? e($_POST['description']) : ''; ?></textarea>
                            <small>Dersin içeriği ve amacı hakkında kısa bir açıklama yazın.</small>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="primary-button">
                                <i class="button-icon">➕</i> Dersi Ekle
                            </button>
                            <a href="teacher_dashboard.php" class="secondary-button">
                                <i class="button-icon">🔙</i> Geri Dön
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="info-section">
                <h3><i class="info-icon">ℹ️</i> Bilgi</h3>
                <p>Ders ekledikten sonra, dersleriniz sayfasından içerik ve quiz ekleyebilirsiniz. Derslerinize öğrenciler sistem üzerinden erişebilir.</p>
            </div>
        </main>
    </div>
    
    <script src="js/main.js"></script>
</body>
</html>