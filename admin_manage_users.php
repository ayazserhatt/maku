<?php
session_start();
include "config.php";

// Check if user is admin
require_admin();

// Handle user deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $user_id = intval($_GET['delete']);
    
    // Don't allow admin to delete themselves
    if ($user_id == $_SESSION['user_id']) {
        $error_message = "Kendi hesabınızı silemezsiniz!";
    } else {
        try {
            // Start transaction
            $conn->beginTransaction();
            
            // Delete related quiz results
            $stmt_delete_results = $conn->prepare("DELETE FROM quiz_results WHERE user_id = ?");
            $stmt_delete_results->execute([$user_id]);
            
            // For teachers, set their courses' teacher_id to NULL
            $stmt_update_courses = $conn->prepare("UPDATE courses SET teacher_id = NULL WHERE teacher_id = ?");
            $stmt_update_courses->execute([$user_id]);
            
            // Finally delete the user
            $stmt_delete_user = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt_delete_user->execute([$user_id]);
            
            // Commit the transaction
            $conn->commit();
            
            $success_message = "Kullanıcı başarıyla silindi!";
        } catch (Exception $e) {
            // Rollback on error
            $conn->rollback();
            $error_message = "Hata oluştu: " . $e->getMessage();
        }
    }
}

// Handle user creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add_user') {
    $name = secure_input($_POST["name"]);
    $email = secure_input($_POST["email"]);
    $password = $_POST["password"];
    $role = $_POST["role"];
    
    // Validate input
    if (empty($name) || empty($email) || empty($password) || empty($role)) {
        $error_message = "Tüm alanları doldurunuz!";
    } elseif (strlen($password) < 6) {
        $error_message = "Şifre en az 6 karakter olmalıdır!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Geçerli bir e-posta adresi giriniz!";
    } else {
        // Check if email already exists
        $check_stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $check_stmt->execute([$email]);
        $check_result = $check_stmt->fetchAll();
        
        if (count($check_result) > 0) {
            $error_message = "Bu e-posta adresi zaten kayıtlı!";
        } else {
            // Generate salt and hash password
            $salt = generate_salt();
            $hashed_password = hash_password($password, $salt);
            
            // Insert new user
            $insert_stmt = $conn->prepare("INSERT INTO users (name, email, password, salt, role) VALUES (?, ?, ?, ?, ?)");
            if ($insert_stmt->execute([$name, $email, $hashed_password, $salt, $role])) {
                $success_message = "Kullanıcı başarıyla oluşturuldu!";
            } else {
                $error_message = "Kullanıcı oluşturulurken bir hata oluştu.";
            }
        }
    }
}

// Get users from database
$sql = "SELECT id, name, email, role FROM users ORDER BY role, name";
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
    <title>MAKÜ - Kullanıcı Yönetimi</title>
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
                <h1>Kullanıcı Yönetimi</h1>
                <p>Kullanıcıları ekleyin, düzenleyin veya silin.</p>
            </div>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div class="content-card">
                <div class="card-header">
                    <h2>Yeni Kullanıcı Ekle</h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="" class="admin-form">
                        <input type="hidden" name="action" value="add_user">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Ad Soyad:</label>
                                <input type="text" id="name" name="name" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">E-posta:</label>
                                <input type="email" id="email" name="email" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="password">Şifre:</label>
                                <input type="password" id="password" name="password" required>
                                <small>En az 6 karakter</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="role">Rol:</label>
                                <select id="role" name="role" required>
                                    <option value="student">Öğrenci</option>
                                    <option value="teacher">Öğretmen</option>
                                    <option value="admin">Yönetici</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="primary-button">
                                <i class="button-icon">➕</i> Kullanıcı Ekle
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="content-card">
                <div class="card-header">
                    <h2>Kullanıcı Listesi</h2>
                    <div class="search-container">
                        <input type="text" id="userSearch" placeholder="Kullanıcı ara..." class="search-input">
                        <span class="search-icon">🔍</span>
                    </div>
                </div>
                
                <?php if ($result && $result->rowCount() > 0): ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Ad Soyad</th>
                                    <th>E-posta</th>
                                    <th>Rol</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody id="userTableBody">
                                <?php while ($row = $result->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo e($row['name']); ?></td>
                                        <td><?php echo e($row['email']); ?></td>
                                        <td>
                                            <?php 
                                                switch($row['role']) {
                                                    case 'admin':
                                                        echo '<span class="badge badge-admin">Yönetici</span>';
                                                        break;
                                                    case 'teacher':
                                                        echo '<span class="badge badge-teacher">Öğretmen</span>';
                                                        break;
                                                    case 'student':
                                                        echo '<span class="badge badge-student">Öğrenci</span>';
                                                        break;
                                                    default:
                                                        echo $row['role'];
                                                }
                                            ?>
                                        </td>
                                        <td class="actions">
                                            <a href="admin_edit_user.php?id=<?php echo $row['id']; ?>" class="btn-edit" title="Düzenle">✏️</a>
                                            <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                                <a href="admin_manage_users.php?delete=<?php echo $row['id']; ?>" class="btn-delete" title="Sil" onclick="return confirm('Bu kullanıcıyı silmek istediğinizden emin misiniz?')">🗑️</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="no-data">
                        <p>Henüz hiç kullanıcı bulunmamaktadır.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script>
        // User search functionality
        document.getElementById('userSearch').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const rows = document.getElementById('userTableBody').getElementsByTagName('tr');
            
            for (let i = 0; i < rows.length; i++) {
                const nameCell = rows[i].getElementsByTagName('td')[1]; // Name column
                const emailCell = rows[i].getElementsByTagName('td')[2]; // Email column
                
                if (nameCell && emailCell) {
                    const nameText = nameCell.textContent || nameCell.innerText;
                    const emailText = emailCell.textContent || emailCell.innerText;
                    
                    if (nameText.toLowerCase().indexOf(searchValue) > -1 || 
                        emailText.toLowerCase().indexOf(searchValue) > -1) {
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