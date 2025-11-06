<?php
session_start();
include "config.php";

// Check if user is admin
require_admin();

// Handle quiz deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $quiz_id = intval($_GET['delete']);
    
    try {
        // Start transaction
        $conn->beginTransaction();
        
        // Delete quiz results first (maintain referential integrity)
        $stmt_delete_results = $conn->prepare("DELETE FROM quiz_results WHERE quiz_id = ?");
        $stmt_delete_results->execute([$quiz_id]);
        
        // Then delete the quiz
        $stmt_delete_quiz = $conn->prepare("DELETE FROM quizzes WHERE id = ?");
        $stmt_delete_quiz->execute([$quiz_id]);
        
        // Commit the transaction
        $conn->commit();
        
        $success_message = "Quiz başarıyla silindi!";
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        $error_message = "Hata oluştu: " . $e->getMessage();
    }
}

// Get quizzes from database with course and teacher info
$sql = "SELECT q.id, q.question, q.correct_option, c.name AS course_name, u.name AS teacher_name
        FROM quizzes q
        JOIN courses c ON q.course_id = c.id
        JOIN users u ON c.teacher_id = u.id
        ORDER BY c.name, q.id DESC";
$result = $conn->query($sql);
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
    <title>MAKÜ - Quiz Yönetimi</title>
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
                <h1>Quiz Yönetimi</h1>
                <p>Tüm quizleri görüntüleyin, düzenleyin veya silin.</p>
            </div>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div class="content-card">
                <div class="card-header">
                    <h2>Tüm Quizler</h2>
                    <div class="search-container">
                        <input type="text" id="quizSearch" placeholder="Quiz ara..." class="search-input">
                        <span class="search-icon">🔍</span>
                    </div>
                </div>
                
                <?php if ($result && $result->rowCount() > 0): ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Ders</th>
                                    <th>Soru</th>
                                    <th>Doğru Cevap</th>
                                    <th>Öğretmen</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody id="quizTableBody">
                                <?php while ($row = $result->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo e($row['course_name']); ?></td>
                                        <td><?php echo e($row['question']); ?></td>
                                        <td><?php echo $row['correct_option']; ?></td>
                                        <td><?php echo e($row['teacher_name']); ?></td>
                                        <td class="actions">
                                            <a href="admin_view_quiz.php?id=<?php echo $row['id']; ?>" class="btn-view" title="Görüntüle">👁️</a>
                                            <a href="admin_manage_quizzes.php?delete=<?php echo $row['id']; ?>" class="btn-delete" title="Sil" onclick="return confirm('Bu quizi silmek istediğinizden emin misiniz?')">🗑️</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="no-data">
                        <p>Henüz hiç quiz bulunmamaktadır.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="action-buttons centered">
                <a href="admin_dashboard.php" class="secondary-button">
                    <i class="button-icon">🔙</i> Geri Dön
                </a>
            </div>
        </main>
    </div>
    
    <script>
        // Quiz search functionality
        document.getElementById('quizSearch').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const rows = document.getElementById('quizTableBody').getElementsByTagName('tr');
            
            for (let i = 0; i < rows.length; i++) {
                const questionCell = rows[i].getElementsByTagName('td')[2]; // Question column
                const courseCell = rows[i].getElementsByTagName('td')[1]; // Course column
                
                if (questionCell && courseCell) {
                    const questionText = questionCell.textContent || questionCell.innerText;
                    const courseText = courseCell.textContent || courseCell.innerText;
                    
                    if (questionText.toLowerCase().indexOf(searchValue) > -1 || 
                        courseText.toLowerCase().indexOf(searchValue) > -1) {
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