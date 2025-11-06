<?php
session_start();
include "config.php";

// Check if user is a teacher
require_teacher();

$teacher_id = $_SESSION["user_id"];
$success_message = "";
$error_message = "";

// Get courses taught by this teacher
$sql_courses = "SELECT id, name FROM courses WHERE teacher_id = :teacher_id ORDER BY name";
$stmt_courses = $pdo->prepare($sql_courses);
$stmt_courses->execute(['teacher_id' => $teacher_id]);
$courses = $stmt_courses->fetchAll();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_id = secure_input($_POST["course_id"]);
    $question = secure_input($_POST["question"]);
    $option_a = secure_input($_POST["option_a"]);
    $option_b = secure_input($_POST["option_b"]);
    $option_c = secure_input($_POST["option_c"]);
    $option_d = secure_input($_POST["option_d"]);
    $correct_option = secure_input($_POST["correct_option"]);
    
    // Validate input
    if (empty($course_id) || empty($question) || empty($option_a) || empty($option_b) || 
        empty($option_c) || empty($option_d) || empty($correct_option)) {
        $error_message = "Tüm alanları doldurunuz!";
    } else {
        // Verify that the course belongs to this teacher
        $sql_verify = "SELECT id FROM courses WHERE id = :course_id AND teacher_id = :teacher_id";
        $stmt_verify = $pdo->prepare($sql_verify);
        $stmt_verify->execute([
            'course_id' => $course_id,
            'teacher_id' => $teacher_id
        ]);
        $course_exists = $stmt_verify->fetch();
        
        if (!$course_exists) {
            $error_message = "Bu derse quiz eklemek için yetkili değilsiniz!";
        } else {
            // Insert quiz
            $sql_insert = "INSERT INTO quizzes (course_id, question, option_a, option_b, option_c, option_d, correct_option) 
                           VALUES (:course_id, :question, :option_a, :option_b, :option_c, :option_d, :correct_option)";
            $stmt_insert = $pdo->prepare($sql_insert);
            $insert_params = [
                'course_id' => $course_id,
                'question' => $question,
                'option_a' => $option_a,
                'option_b' => $option_b,
                'option_c' => $option_c,
                'option_d' => $option_d,
                'correct_option' => $correct_option
            ];
            
            if ($stmt_insert->execute($insert_params)) {
                $success_message = "Quiz başarıyla eklendi!";
                // Clear form fields after successful submission
                unset($_POST);
            } else {
                $error_message = "Quiz eklenirken bir hata oluştu!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Mehmet Akif Ersoy Üniversitesi Quiz Ekleme Sayfası">
    <meta name="keywords" content="MAKÜ, quiz, sınav, öğretmen, test">
    <meta name="author" content="Mehmet Akif Ersoy Üniversitesi">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#1A3C34">
    <title>MAKÜ - Quiz Ekle</title>
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
                <h1>Yeni Quiz Ekle</h1>
                <p>Dersleriniz için yeni quiz soruları oluşturun.</p>
            </div>
            
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <?php if (count($courses) > 0): ?>
                <div class="content-card">
                    <div class="card-header">
                        <h2>Quiz Bilgileri</h2>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="" class="admin-form">
                            <div class="form-row">
                                <div class="form-group full-width">
                                    <label for="course_id">Ders Seçin:</label>
                                    <select id="course_id" name="course_id" required>
                                        <option value="">Bir ders seçin</option>
                                        <?php foreach ($courses as $course): ?>
                                            <option value="<?php echo $course['id']; ?>" <?php echo (isset($_POST['course_id']) && $_POST['course_id'] == $course['id']) ? 'selected' : ''; ?>>
                                                <?php echo e($course['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group full-width">
                                    <label for="question">Soru:</label>
                                    <textarea id="question" name="question" rows="3" required><?php echo isset($_POST['question']) ? e($_POST['question']) : ''; ?></textarea>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group half-width">
                                    <label for="option_a">Seçenek A:</label>
                                    <input type="text" id="option_a" name="option_a" value="<?php echo isset($_POST['option_a']) ? e($_POST['option_a']) : ''; ?>" required>
                                </div>
                                
                                <div class="form-group half-width">
                                    <label for="option_b">Seçenek B:</label>
                                    <input type="text" id="option_b" name="option_b" value="<?php echo isset($_POST['option_b']) ? e($_POST['option_b']) : ''; ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group half-width">
                                    <label for="option_c">Seçenek C:</label>
                                    <input type="text" id="option_c" name="option_c" value="<?php echo isset($_POST['option_c']) ? e($_POST['option_c']) : ''; ?>" required>
                                </div>
                                
                                <div class="form-group half-width">
                                    <label for="option_d">Seçenek D:</label>
                                    <input type="text" id="option_d" name="option_d" value="<?php echo isset($_POST['option_d']) ? e($_POST['option_d']) : ''; ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group full-width">
                                    <label>Doğru Cevap:</label>
                                    <div class="radio-buttons">
                                        <label class="radio-label">
                                            <input type="radio" name="correct_option" value="A" <?php echo (isset($_POST['correct_option']) && $_POST['correct_option'] == 'A') ? 'checked' : ''; ?> required>
                                            A
                                        </label>
                                        <label class="radio-label">
                                            <input type="radio" name="correct_option" value="B" <?php echo (isset($_POST['correct_option']) && $_POST['correct_option'] == 'B') ? 'checked' : ''; ?>>
                                            B
                                        </label>
                                        <label class="radio-label">
                                            <input type="radio" name="correct_option" value="C" <?php echo (isset($_POST['correct_option']) && $_POST['correct_option'] == 'C') ? 'checked' : ''; ?>>
                                            C
                                        </label>
                                        <label class="radio-label">
                                            <input type="radio" name="correct_option" value="D" <?php echo (isset($_POST['correct_option']) && $_POST['correct_option'] == 'D') ? 'checked' : ''; ?>>
                                            D
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="primary-button">
                                    <i class="button-icon">➕</i> Quiz Ekle
                                </button>
                                <a href="teacher_dashboard.php" class="secondary-button">
                                    <i class="button-icon">🔙</i> İptal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <p>Quiz eklemek için önce bir ders oluşturmalısınız.</p>
                    <div class="alert-actions">
                        <a href="teacher_add_course.php" class="btn">Ders Ekle</a>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="content-card">
                <div class="card-header">
                    <h2>Mevcut Quizler</h2>
                    <div class="card-actions">
                        <input type="text" id="quizSearch" class="search-input" placeholder="Quiz ara...">
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <?php
                        // Get existing quizzes for this teacher's courses
                        $sql_quizzes = "SELECT q.id, q.question, q.correct_option, c.name as course_name, q.created_at
                                       FROM quizzes q
                                       JOIN courses c ON q.course_id = c.id
                                       WHERE c.teacher_id = :teacher_id
                                       ORDER BY q.created_at DESC";
                        $stmt_quizzes = $pdo->prepare($sql_quizzes);
                        $stmt_quizzes->execute(['teacher_id' => $teacher_id]);
                        $quizzes = $stmt_quizzes->fetchAll();
                        ?>
                        
                        <?php if (count($quizzes) > 0): ?>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Ders</th>
                                        <th>Soru</th>
                                        <th>Doğru Cevap</th>
                                        <th>Eklenme Tarihi</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($quizzes as $index => $quiz): ?>
                                        <tr>
                                            <td><?php echo $index + 1; ?></td>
                                            <td><?php echo e($quiz['course_name']); ?></td>
                                            <td><?php echo e($quiz['question']); ?></td>
                                            <td class="correct-answer"><?php echo e($quiz['correct_option']); ?></td>
                                            <td><?php echo date('d.m.Y H:i', strtotime($quiz['created_at'])); ?></td>
                                            <td class="actions">
                                                <a href="edit_quiz.php?id=<?php echo $quiz['id']; ?>" class="btn-icon" title="Düzenle">
                                                    ✏️
                                                </a>
                                                <a href="delete_quiz.php?id=<?php echo $quiz['id']; ?>" class="btn-icon btn-delete" title="Sil" data-confirm="Bu quizi silmek istediğinizden emin misiniz?">
                                                    🗑️
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="no-data">
                                <p>Henüz hiç quiz eklenmemiş.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="js/main.js"></script>
    <script>
        // Search functionality for quizzes
        document.getElementById('quizSearch').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.data-table tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>