<?php
session_start();
include "config.php";

// Check if user is admin
require_admin();

// Handle course deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $course_id = intval($_GET['delete']);
    
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
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        $error_message = "Hata oluştu: " . $e->getMessage();
    }
}

// Handle course creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add_course') {
    $name = secure_input(trim($_POST["name"]));
    $description = secure_input(trim($_POST["description"]));
    $teacher_id = !empty($_POST["teacher_id"]) ? intval($_POST["teacher_id"]) : null;
    
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
        } catch (Exception $e) {
            $error_message = "Ders eklenirken bir hata oluştu: " . $e->getMessage();
        }
    }
}

// Get courses from database with teacher info
$sql = "SELECT c.id, c.name, c.description, u.name AS teacher_name, u.id AS teacher_id 
        FROM courses c
        LEFT JOIN users u ON c.teacher_id = u.id
        ORDER BY c.name";
$result = $conn->query($sql);

// Get teachers for dropdown
$sql_teachers = "SELECT id, name FROM users WHERE role = 'teacher' ORDER BY name";
$result_teachers = $conn->query($sql_teachers);
$teachers = $result_teachers ? $result_teachers->fetchAll(PDO::FETCH_ASSOC) : [];
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
    <title>MAKÜ - Ders Yönetimi</title>
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
                <h1>Ders Yönetimi</h1>
                <p>Dersleri ekleyin, düzenleyin veya silin.</p>
            </div>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div class="content-card">
                <div class="card-header">
                    <h2>Yeni Ders Ekle</h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="" class="admin-form">
                        <input type="hidden" name="action" value="add_course">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Ders Adı:</label>
                                <input type="text" id="name" name="name" value="<?php echo isset($_POST['name']) ? e($_POST['name']) : ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="teacher_id">Öğretmen:</label>
                                <select id="teacher_id" name="teacher_id">
                                    <option value="">-- Öğretmen Seçin --</option>
                                    <?php foreach ($teachers as $teacher): ?>
                                        <option value="<?php echo $teacher['id']; ?>" <?php echo isset($_POST['teacher_id']) && $_POST['teacher_id'] == $teacher['id'] ? 'selected' : ''; ?>><?php echo e($teacher['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (empty($teachers)): ?>
                                    <small class="form-hint">Öğretmen bulunamadı. Önce öğretmen ekleyin.</small>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Açıklama:</label>
                            <textarea id="description" name="description" rows="4"><?php echo isset($_POST['description']) ? e($_POST['description']) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="primary-button">
                                <i class="button-icon">➕</i> Ders Ekle
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="content-card">
                <div class="card-header">
                    <h2>Ders Listesi</h2>
                    <div class="search-container">
                        <input type="text" id="courseSearch" placeholder="Ders ara..." class="search-input">
                        <span class="search-icon">🔍</span>
                    </div>
                </div>
                
                <?php if ($result && $result->rowCount() > 0): ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Ders Adı</th>
                                    <th>Açıklama</th>
                                    <th>Öğretmen</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody id="courseTableBody">
                                <?php while ($row = $result->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo e($row['name']); ?></td>
                                        <td><?php echo e($row['description'] ? $row['description'] : 'Açıklama yok'); ?></td>
                                        <td><?php echo e($row['teacher_name'] ? $row['teacher_name'] : 'Atanmamış'); ?></td>
                                        <td class="actions">
                                            <a href="admin_edit_course.php?id=<?php echo $row['id']; ?>" class="btn-edit" title="Düzenle">✏️</a>
                                            <a href="admin_manage_courses.php?delete=<?php echo $row['id']; ?>" class="btn-delete" title="Sil" onclick="return confirm('Bu dersi silmek istediğinizden emin misiniz? Bu işlem geri alınamaz ve ilgili tüm quizler silinecektir.')">🗑️</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="no-data">
                        <p>Henüz hiç ders bulunmamaktadır.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script>
        // Course search functionality
        document.getElementById('courseSearch').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const rows = document.getElementById('courseTableBody').getElementsByTagName('tr');
            
            for (let i = 0; i < rows.length; i++) {
                const nameCell = rows[i].getElementsByTagName('td')[1]; // Course name column
                const descriptionCell = rows[i].getElementsByTagName('td')[2]; // Description column
                const teacherCell = rows[i].getElementsByTagName('td')[3]; // Teacher column
                
                if (nameCell && descriptionCell && teacherCell) {
                    const nameText = nameCell.textContent || nameCell.innerText;
                    const descriptionText = descriptionCell.textContent || descriptionCell.innerText;
                    const teacherText = teacherCell.textContent || teacherCell.innerText;
                    
                    if (nameText.toLowerCase().indexOf(searchValue) > -1 || 
                        descriptionText.toLowerCase().indexOf(searchValue) > -1 || 
                        teacherText.toLowerCase().indexOf(searchValue) > -1) {
                        rows[i].style.display = "";
                    } else {
                        rows[i].style.display = "none";
                    }
                }
            }
        });
    </script>
    <script src="js/main.js"></script>
</body>
</html>