<?php
session_start();
include "config.php";

// Check if user is a teacher
require_teacher();

$teacher_id = $_SESSION["user_id"];
$success_message = "";
$error_message = "";

// Get selected course if provided
$selected_course = null;
$selected_course_id = null;
if (isset($_GET['course_id']) && is_numeric($_GET['course_id'])) {
    $selected_course_id = intval($_GET['course_id']);
    
    // Verify the course belongs to this teacher
    $sql_check = "SELECT * FROM courses WHERE id = ? AND teacher_id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->execute([$selected_course_id, $teacher_id]);
    $selected_course = $stmt_check->fetch(PDO::FETCH_ASSOC);
    
    if (!$selected_course) {
        $error_message = "Bu derse erişim yetkiniz yok!";
        $selected_course_id = null;
    }
}

// Handle course content creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add_content') {
    $course_id = intval($_POST["course_id"]);
    $title = secure_input($_POST["title"]);
    $content = $_POST["content"]; // Allow HTML for rich content
    
    // Verify the course belongs to this teacher
    $sql_check = "SELECT id FROM courses WHERE id = ? AND teacher_id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->execute([$course_id, $teacher_id]);
    $check_result = $stmt_check->fetch(PDO::FETCH_ASSOC);
    
    if (!$check_result) {
        $error_message = "Bu derse içerik ekleme yetkiniz yok!";
    } else if (empty($title)) {
        $error_message = "İçerik başlığı boş olamaz!";
    } else {
        // Insert new course content
        $sql = "INSERT INTO course_content (course_id, title, content) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        try {
            $stmt->execute([$course_id, $title, $content]);
            $success_message = "İçerik başarıyla eklendi!";
        } catch (Exception $e) {
            $error_message = "İçerik eklenirken bir hata oluştu: " . $e->getMessage();
        }
    }
}

// Handle course deletion
if (isset($_GET['action']) && $_GET['action'] == 'delete_course' && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $course_id = intval($_GET['id']);
    
    // Verify the course belongs to this teacher
    $sql_check = "SELECT id FROM courses WHERE id = ? AND teacher_id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->execute([$course_id, $teacher_id]);
    $check_result = $stmt_check->fetch(PDO::FETCH_ASSOC);
    
    if (!$check_result) {
        $error_message = "Bu dersi silme yetkiniz yok!";
    } else {
        try {
            // Start transaction
            $conn->beginTransaction();
            
            // Delete related quiz results
            $sql_delete_results = "DELETE qr FROM quiz_results qr 
                                   JOIN quizzes q ON qr.quiz_id = q.id 
                                   WHERE q.course_id = ?";
            $stmt_delete_results = $conn->prepare($sql_delete_results);
            $stmt_delete_results->execute([$course_id]);
            
            // Delete quizzes for this course
            $stmt_delete_quizzes = $conn->prepare("DELETE FROM quizzes WHERE course_id = ?");
            $stmt_delete_quizzes->execute([$course_id]);
            
            // Delete course_content
            $stmt_delete_content = $conn->prepare("DELETE FROM course_content WHERE course_id = ?");
            $stmt_delete_content->execute([$course_id]);
            
            // Delete the course
            $stmt_delete_course = $conn->prepare("DELETE FROM courses WHERE id = ?");
            $stmt_delete_course->execute([$course_id]);
            
            // Commit the transaction
            $conn->commit();
            
            $success_message = "Ders başarıyla silindi!";
            $selected_course_id = null;
            $selected_course = null;
        } catch (Exception $e) {
            // Rollback on error
            $conn->rollback();
            $error_message = "Hata oluştu: " . $e->getMessage();
        }
    }
}

// Handle course content deletion
if (isset($_GET['action']) && $_GET['action'] == 'delete_content' && isset($_GET['content_id']) && is_numeric($_GET['content_id'])) {
    $content_id = intval($_GET['content_id']);
    
    // Verify the content belongs to this teacher's course
    $sql_check = "SELECT cc.id 
                  FROM course_content cc
                  JOIN courses c ON cc.course_id = c.id
                  WHERE cc.id = ? AND c.teacher_id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->execute([$content_id, $teacher_id]);
    $check_result = $stmt_check->fetch(PDO::FETCH_ASSOC);
    
    if (!$check_result) {
        $error_message = "Bu içeriği silme yetkiniz yok!";
    } else {
        $sql_delete = "DELETE FROM course_content WHERE id = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        try {
            $stmt_delete->execute([$content_id]);
            $success_message = "İçerik başarıyla silindi!";
        } catch (Exception $e) {
            $error_message = "İçerik silinirken bir hata oluştu: " . $e->getMessage();
        }
    }
}

// Get teacher's courses
$sql_courses = "SELECT id, name, description FROM courses WHERE teacher_id = ? ORDER BY name";
$stmt_courses = $conn->prepare($sql_courses);
$stmt_courses->execute([$teacher_id]);
$result_courses = $stmt_courses;

// If a course is selected, get its content and quizzes
if ($selected_course_id) {
    // Get course content
    $sql_content = "SELECT id, title, content FROM course_content WHERE course_id = ? ORDER BY id";
    $stmt_content = $conn->prepare($sql_content);
    $stmt_content->execute([$selected_course_id]);
    $result_content = $stmt_content;
    
    // Get course quizzes
    $sql_quizzes = "SELECT id, question FROM quizzes WHERE course_id = ? ORDER BY id";
    $stmt_quizzes = $conn->prepare($sql_quizzes);
    $stmt_quizzes->execute([$selected_course_id]);
    $result_quizzes = $stmt_quizzes;
    
    // Get students who participated in quizzes for this course
    $sql_students = "SELECT DISTINCT u.id, u.name, u.email,
                     COUNT(DISTINCT qr.id) as total_attempts,
                     SUM(qr.is_correct) as correct_answers
                     FROM users u
                     JOIN quiz_results qr ON u.id = qr.user_id
                     JOIN quizzes q ON qr.quiz_id = q.id
                     WHERE q.course_id = ? AND u.role = 'student'
                     GROUP BY u.id, u.name, u.email
                     ORDER BY u.name";
    $stmt_students = $conn->prepare($sql_students);
    $stmt_students->execute([$selected_course_id]);
    $result_students = $stmt_students;
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
                    <li><a href="teacher_dashboard.php">Anasayfa</a></li>
                    <li><a href="teacher_manage_courses.php" class="active">Derslerim</a></li>
                    <li><a href="teacher_add_course.php">Ders Ekle</a></li>
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
                <li><a href="teacher_manage_courses.php" class="active"><i class="icon">📚</i> Derslerim</a></li>
                <li><a href="teacher_add_course.php"><i class="icon">➕</i> Ders Ekle</a></li>
                <li><a href="add_quiz.php"><i class="icon">📝</i> Quiz Ekle</a></li>
                <li><a href="islem/logout.php"><i class="icon">🚪</i> Çıkış</a></li>
            </ul>
        </div>
        
        <main class="dashboard-content">
            <div class="dashboard-header">
                <h1><?php echo $selected_course ? e($selected_course['name']) : 'Derslerim'; ?></h1>
                <p><?php echo $selected_course ? e($selected_course['description']) : 'Derslerinizi yönetin, içerik ve quiz ekleyin.'; ?></p>
            </div>
            
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div class="course-manager">
                <?php if (!$selected_course): ?>
                    <!-- Course List -->
                    <div class="content-card">
                        <div class="card-header">
                            <h2>Derslerim</h2>
                            <a href="teacher_add_course.php" class="btn-action">Yeni Ders Ekle</a>
                        </div>
                        
                        <?php if ($result_courses && $result_courses->rowCount() > 0): ?>
                            <div class="course-grid">
                                <?php while ($course = $result_courses->fetch(PDO::FETCH_ASSOC)): ?>
                                    <div class="course-card">
                                        <h3><?php echo e($course['name']); ?></h3>
                                        <p><?php echo e($course['description'] ? $course['description'] : 'Açıklama yok'); ?></p>
                                        <div class="course-card-actions">
                                            <a href="teacher_manage_courses.php?course_id=<?php echo $course['id']; ?>" class="btn-view">Yönet</a>
                                            <a href="add_quiz.php?course_id=<?php echo $course['id']; ?>" class="btn-edit">Quiz Ekle</a>
                                            <a href="teacher_manage_courses.php?action=delete_course&id=<?php echo $course['id']; ?>" class="btn-delete" onclick="return confirm('Bu dersi silmek istediğinizden emin misiniz? Bu işlem geri alınamaz ve ilgili tüm quizler silinecektir.')">Sil</a>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="no-data">
                                <p>Henüz hiç ders oluşturmadınız.</p>
                                <a href="teacher_add_course.php" class="btn-action">İlk Dersinizi Ekleyin</a>
                            </div>
                        <?php endif; ?>
                    </div>
                
                <?php else: ?>
                    <!-- Single Course Management -->
                    <div class="course-tabs">
                        <div class="tab-links">
                            <button class="tab-link active" data-tab="contents">İçerikler</button>
                            <button class="tab-link" data-tab="quizzes">Quizler</button>
                            <button class="tab-link" data-tab="students">Öğrenciler</button>
                            <a href="teacher_manage_courses.php" class="btn-back">Tüm Derslerim</a>
                        </div>
                        
                        <!-- Contents Tab -->
                        <div id="contents" class="tab-content active">
                            <div class="content-card">
                                <div class="card-header">
                                    <h2>Ders İçeriği Ekle</h2>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="" class="admin-form">
                                        <input type="hidden" name="action" value="add_content">
                                        <input type="hidden" name="course_id" value="<?php echo $selected_course_id; ?>">
                                        
                                        <div class="form-group">
                                            <label for="title">İçerik Başlığı:</label>
                                            <input type="text" id="title" name="title" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="content">İçerik:</label>
                                            <textarea id="content" name="content" rows="6"></textarea>
                                            <small>HTML formatında içerik girebilirsiniz.</small>
                                        </div>
                                        
                                        <div class="form-actions">
                                            <button type="submit" class="primary-button">
                                                <i class="button-icon">➕</i> İçerik Ekle
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            
                            <div class="content-card">
                                <div class="card-header">
                                    <h2>Mevcut İçerikler</h2>
                                </div>
                                
                                <?php if ($result_content && $result_content->rowCount() > 0): ?>
                                    <div class="content-list">
                                        <?php while ($content = $result_content->fetch(PDO::FETCH_ASSOC)): ?>
                                            <div class="content-item">
                                                <div class="content-header">
                                                    <h3><?php echo e($content['title']); ?></h3>
                                                    <div class="content-actions">
                                                        <a href="teacher_manage_courses.php?action=delete_content&content_id=<?php echo $content['id']; ?>&course_id=<?php echo $selected_course_id; ?>" class="btn-delete" onclick="return confirm('Bu içeriği silmek istediğinizden emin misiniz?')">Sil</a>
                                                    </div>
                                                </div>
                                                <div class="content-body">
                                                    <?php echo $content['content']; ?>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="no-data">
                                        <p>Bu ders için henüz içerik eklenmemiş.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Quizzes Tab -->
                        <div id="quizzes" class="tab-content">
                            <div class="content-card">
                                <div class="card-header">
                                    <h2>Ders Quizleri</h2>
                                    <a href="add_quiz.php?course_id=<?php echo $selected_course_id; ?>" class="btn-action">Yeni Quiz Ekle</a>
                                </div>
                                
                                <?php if ($result_quizzes && $result_quizzes->rowCount() > 0): ?>
                                    <div class="table-responsive">
                                        <table class="data-table">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Soru</th>
                                                    <th>İşlemler</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($quiz = $result_quizzes->fetch(PDO::FETCH_ASSOC)): ?>
                                                    <tr>
                                                        <td><?php echo $quiz['id']; ?></td>
                                                        <td><?php echo e($quiz['question']); ?></td>
                                                        <td class="actions">
                                                            <a href="add_quiz.php?edit=<?php echo $quiz['id']; ?>" class="btn-edit" title="Düzenle">✏️</a>
                                                            <a href="#" class="btn-delete" title="Sil" onclick="if(confirm('Bu quizi silmek istediğinizden emin misiniz?')) window.location.href='?action=delete_quiz&id=<?php echo $quiz['id']; ?>&course_id=<?php echo $selected_course_id; ?>'">🗑️</a>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="no-data">
                                        <p>Bu ders için henüz quiz eklenmemiş.</p>
                                        <a href="add_quiz.php?course_id=<?php echo $selected_course_id; ?>" class="btn-action">İlk Quizinizi Ekleyin</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Students Tab -->
                        <div id="students" class="tab-content">
                            <div class="content-card">
                                <div class="card-header">
                                    <h2>Ders Öğrencileri</h2>
                                </div>
                                
                                <?php if ($result_students && $result_students->rowCount() > 0): ?>
                                    <div class="table-responsive">
                                        <table class="data-table">
                                            <thead>
                                                <tr>
                                                    <th>Ad Soyad</th>
                                                    <th>E-posta</th>
                                                    <th>Çözülen Quiz</th>
                                                    <th>Doğru Cevap</th>
                                                    <th>Başarı Oranı</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($student = $result_students->fetch(PDO::FETCH_ASSOC)): 
                                                    $success_rate = ($student['total_attempts'] > 0) ? round(($student['correct_answers'] / $student['total_attempts']) * 100, 2) : 0;
                                                ?>
                                                    <tr>
                                                        <td><?php echo e($student['name']); ?></td>
                                                        <td><?php echo e($student['email']); ?></td>
                                                        <td><?php echo $student['total_attempts']; ?></td>
                                                        <td><?php echo $student['correct_answers']; ?></td>
                                                        <td>
                                                            <div class="progress">
                                                                <div class="progress-bar" style="width: <?php echo $success_rate; ?>%;">
                                                                    <span><?php echo $success_rate; ?>%</span>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="no-data">
                                        <p>Bu ders için henüz öğrenci katılımı bulunmamaktadır.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script src="js/main.js"></script>
    <script>
        // Tab functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tabLinks = document.querySelectorAll('.tab-link');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabLinks.forEach(link => {
                link.addEventListener('click', function() {
                    // Remove active class from all tabs
                    tabLinks.forEach(tab => tab.classList.remove('active'));
                    tabContents.forEach(content => content.classList.remove('active'));
                    
                    // Add active class to current tab
                    this.classList.add('active');
                    const tabId = this.getAttribute('data-tab');
                    document.getElementById(tabId).classList.add('active');
                });
            });
        });
    </script>
</body>
</html>