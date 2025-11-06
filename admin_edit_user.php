<?php
session_start();
include "config.php";

// Check if user is admin
require_admin();

$success_message = "";
$error_message = "";
$user_data = null;

// Check if user ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin_manage_users.php");
    exit;
}

$user_id = intval($_GET['id']);

// Get user data
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $error_message = "Kullanıcı bulunamadı!";
} else {
    $user_data = $result->fetch_assoc();
}
$stmt->close();

// Handle user update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update_user') {
    $name = secure_input($_POST["name"]);
    $email = secure_input($_POST["email"]);
    $role = secure_input($_POST["role"]);
    $new_password = $_POST["new_password"];
    
    // Validate input
    if (empty($name) || empty($email) || empty($role)) {
        $error_message = "Ad Soyad, E-posta ve Rol alanları boş olamaz!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Geçerli bir e-posta adresi giriniz!";
    } else {
        // Check if email already exists for another user
        if ($email != $user_data['email']) {
            $check_sql = "SELECT id FROM users WHERE email = ? AND id != ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("si", $email, $user_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $error_message = "Bu e-posta adresi başka bir kullanıcı tarafından kullanılıyor!";
                $check_stmt->close();
                goto skip_update;
            }
            $check_stmt->close();
        }
        
        // If password is provided, update it with salt
        if (!empty($new_password)) {
            if (strlen($new_password) < 6) {
                $error_message = "Şifre en az 6 karakter olmalıdır!";
                goto skip_update;
            }
            
            $salt = generate_salt();
            $hashed_password = hash_password($new_password, $salt);
            
            $update_sql = "UPDATE users SET name = ?, email = ?, role = ?, password = ?, salt = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("sssssi", $name, $email, $role, $hashed_password, $salt, $user_id);
        } else {
            // If no password provided, just update other fields
            $update_sql = "UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("sssi", $name, $email, $role, $user_id);
        }
        
        if ($update_stmt->execute()) {
            $success_message = "Kullanıcı bilgileri başarıyla güncellendi!";
            // Refresh user data
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user_data = $result->fetch_assoc();
            $stmt->close();
        } else {
            $error_message = "Kullanıcı güncellenirken bir hata oluştu: " . $conn->error;
        }
        
        if (isset($update_stmt)) {
            $update_stmt->close();
        }
    }
}

skip_update:
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
    <title>MAKÜ - Kullanıcı Düzenle</title>
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
                    <li><a href="admin_manage_users.php" class="active">Kullanıcı Yönetimi</a></li>
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
                <li><a href="admin_dashboard.php"><i class="icon">🏠</i> Ana Sayfa</a></li>
                <li><a href="admin_manage_users.php" class="active"><i class="icon">👥</i> Kullanıcı Yönetimi</a></li>
                <li><a href="admin_manage_courses.php"><i class="icon">📚</i> Ders Yönetimi</a></li>
                <li><a href="admin_manage_quizzes.php"><i class="icon">📝</i> Quiz Yönetimi</a></li>
                <li><a href="admin_quiz_stats.php"><i class="icon">📊</i> İstatistikler</a></li>
                <li><a href="islem/logout.php"><i class="icon">🚪</i> Çıkış</a></li>
            </ul>
        </div>
        
        <main class="dashboard-content">
            <div class="dashboard-header">
                <h1>Kullanıcı Düzenle</h1>
                <p>Kullanıcı bilgilerini güncelleyin.</p>
            </div>
            
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <?php if ($user_data): ?>
                <div class="content-card">
                    <div class="card-header">
                        <h2>Kullanıcı Bilgileri</h2>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="" class="admin-form">
                            <input type="hidden" name="action" value="update_user">
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="name">Ad Soyad:</label>
                                    <input type="text" id="name" name="name" value="<?php echo e($user_data['name']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">E-posta:</label>
                                    <input type="email" id="email" name="email" value="<?php echo e($user_data['email']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="role">Rol:</label>
                                    <select id="role" name="role" required>
                                        <option value="student" <?php echo $user_data['role'] == 'student' ? 'selected' : ''; ?>>Öğrenci</option>
                                        <option value="teacher" <?php echo $user_data['role'] == 'teacher' ? 'selected' : ''; ?>>Öğretmen</option>
                                        <option value="admin" <?php echo $user_data['role'] == 'admin' ? 'selected' : ''; ?>>Yönetici</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="new_password">Yeni Şifre (Değiştirmek istemiyorsanız boş bırakın):</label>
                                    <input type="password" id="new_password" name="new_password">
                                    <small>En az 6 karakter</small>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Son Güncelleme:</label>
                                    <input type="text" value="<?php echo date('d.m.Y H:i', strtotime($user_data['updated_at'])); ?>" readonly>
                                </div>
                                
                                <div class="form-group">
                                    <label>Kayıt Tarihi:</label>
                                    <input type="text" value="<?php echo date('d.m.Y H:i', strtotime($user_data['created_at'])); ?>" readonly>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="primary-button">
                                    <i class="button-icon">✅</i> Değişiklikleri Kaydet
                                </button>
                                <a href="admin_manage_users.php" class="secondary-button">
                                    <i class="button-icon">🔙</i> Geri Dön
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <?php if ($user_data['role'] == 'student'): ?>
                    <div class="content-card">
                        <div class="card-header">
                            <h2>Öğrenci Quiz Performansı</h2>
                        </div>
                        <?php
                        // Get student quiz statistics
                        $sql_quiz_stats = "SELECT 
                                            COUNT(qr.id) as total_attempts,
                                            SUM(qr.is_correct) as correct_answers
                                           FROM quiz_results qr
                                           WHERE qr.user_id = ?";
                        $stmt_quiz_stats = $conn->prepare($sql_quiz_stats);
                        $stmt_quiz_stats->bind_param("i", $user_id);
                        $stmt_quiz_stats->execute();
                        $quiz_stats = $stmt_quiz_stats->get_result()->fetch_assoc();
                        $stmt_quiz_stats->close();
                        
                        $total_attempts = $quiz_stats['total_attempts'] ?? 0;
                        $correct_answers = $quiz_stats['correct_answers'] ?? 0;
                        $success_rate = ($total_attempts > 0) ? round(($correct_answers / $total_attempts) * 100, 2) : 0;
                        ?>
                        
                        <div class="stats-summary">
                            <div class="stat-card">
                                <div class="stat-icon">📝</div>
                                <div class="stat-details">
                                    <h3>Çözülen Quiz</h3>
                                    <p class="stat-number"><?php echo $total_attempts; ?></p>
                                </div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-icon">✅</div>
                                <div class="stat-details">
                                    <h3>Doğru Cevap</h3>
                                    <p class="stat-number"><?php echo $correct_answers; ?></p>
                                </div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-icon">📊</div>
                                <div class="stat-details">
                                    <h3>Başarı Oranı</h3>
                                    <p class="stat-number"><?php echo $success_rate; ?>%</p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php elseif ($user_data['role'] == 'teacher'): ?>
                    <div class="content-card">
                        <div class="card-header">
                            <h2>Öğretmen Kursları</h2>
                        </div>
                        <?php
                        // Get teacher's courses
                        $sql_courses = "SELECT id, name, description, 
                                        (SELECT COUNT(*) FROM quizzes WHERE course_id = courses.id) as quiz_count
                                        FROM courses 
                                        WHERE teacher_id = ?
                                        ORDER BY name";
                        $stmt_courses = $conn->prepare($sql_courses);
                        $stmt_courses->bind_param("i", $user_id);
                        $stmt_courses->execute();
                        $result_courses = $stmt_courses->get_result();
                        $stmt_courses->close();
                        ?>
                        
                        <?php if ($result_courses && $result_courses->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Kurs Adı</th>
                                            <th>Açıklama</th>
                                            <th>Quiz Sayısı</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $result_courses->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo e($row['name']); ?></td>
                                                <td><?php echo e($row['description'] ? $row['description'] : 'Açıklama yok'); ?></td>
                                                <td><?php echo $row['quiz_count']; ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="no-data">
                                <p>Bu öğretmenin henüz kursu bulunmamaktadır.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="alert alert-danger">
                    <p>Kullanıcı bulunamadı.</p>
                    <div class="alert-actions">
                        <a href="admin_manage_users.php" class="btn-action">Kullanıcı Listesine Dön</a>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
    
    <script src="js/main.js"></script>
</body>
</html>
