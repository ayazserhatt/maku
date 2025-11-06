<?php
session_start();
include "config.php";

// Check if user is a teacher
require_teacher();

$teacher_id = $_SESSION["user_id"];
$success_message = "";
$error_message = "";
$quiz_data = null;

// Check if quiz ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: add_quiz.php");
    exit;
}

$quiz_id = intval($_GET['id']);

// Verify that the quiz belongs to a course taught by this teacher
$sql_verify = "SELECT q.*, c.name as course_name, c.id as course_id 
               FROM quizzes q 
               JOIN courses c ON q.course_id = c.id 
               WHERE q.id = :quiz_id AND c.teacher_id = :teacher_id";
$stmt_verify = $pdo->prepare($sql_verify);
$stmt_verify->execute([
    'quiz_id' => $quiz_id,
    'teacher_id' => $teacher_id
]);
$quiz_data = $stmt_verify->fetch();

if (!$quiz_data) {
    $_SESSION['error_message'] = "Bu quizi düzenlemek için yetkili değilsiniz!";
    header("Location: add_quiz.php");
    exit;
}

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
        $sql_course_verify = "SELECT id FROM courses WHERE id = :course_id AND teacher_id = :teacher_id";
        $stmt_course_verify = $pdo->prepare($sql_course_verify);
        $stmt_course_verify->execute([
            'course_id' => $course_id,
            'teacher_id' => $teacher_id
        ]);
        $course_exists = $stmt_course_verify->fetch();
        
        if (!$course_exists) {
            $error_message = "Bu derse quiz eklemek için yetkili değilsiniz!";
        } else {
            // Update quiz
            $sql_update = "UPDATE quizzes SET 
                          course_id = :course_id, 
                          question = :question, 
                          option_a = :option_a, 
                          option_b = :option_b, 
                          option_c = :option_c, 
                          option_d = :option_d, 
                          correct_option = :correct_option 
                          WHERE id = :quiz_id";
            $stmt_update = $pdo->prepare($sql_update);
            $update_params = [
                'course_id' => $course_id,
                'question' => $question,
                'option_a' => $option_a,
                'option_b' => $option_b,
                'option_c' => $option_c,
                'option_d' => $option_d,
                'correct_option' => $correct_option,
                'quiz_id' => $quiz_id
            ];
            
            if ($stmt_update->execute($update_params)) {
                $success_message = "Quiz başarıyla güncellendi!";
                
                // Refresh quiz data
                $stmt_verify->execute([
                    'quiz_id' => $quiz_id,
                    'teacher_id' => $teacher_id
                ]);
                $quiz_data = $stmt_verify->fetch();
            } else {
                $error_message = "Quiz güncellenirken bir hata oluştu!";
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
    <meta name="description" content="Mehmet Akif Ersoy Üniversitesi Quiz Düzenleme Sayfası">
    <meta name="keywords" content="MAKÜ, quiz, sınav, öğretmen, test, düzenleme">
    <meta name="author" content="Mehmet Akif Ersoy Üniversitesi">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#1A3C34">
    <title>MAKÜ - Quiz Düzenle</title>
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
                <h1>Quiz Düzenle</h1>
                <p>Quiz sorusunu ve seçeneklerini düzenleyin.</p>
            </div>
            
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <?php if ($quiz_data): ?>
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
                                            <option value="<?php echo $course['id']; ?>" <?php echo ($quiz_data['course_id'] == $course['id']) ? 'selected' : ''; ?>>
                                                <?php echo e($course['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group full-width">
                                    <label for="question">Soru:</label>
                                    <textarea id="question" name="question" rows="3" required><?php echo e($quiz_data['question']); ?></textarea>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group half-width">
                                    <label for="option_a">Seçenek A:</label>
                                    <input type="text" id="option_a" name="option_a" value="<?php echo e($quiz_data['option_a']); ?>" required>
                                </div>
                                
                                <div class="form-group half-width">
                                    <label for="option_b">Seçenek B:</label>
                                    <input type="text" id="option_b" name="option_b" value="<?php echo e($quiz_data['option_b']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group half-width">
                                    <label for="option_c">Seçenek C:</label>
                                    <input type="text" id="option_c" name="option_c" value="<?php echo e($quiz_data['option_c']); ?>" required>
                                </div>
                                
                                <div class="form-group half-width">
                                    <label for="option_d">Seçenek D:</label>
                                    <input type="text" id="option_d" name="option_d" value="<?php echo e($quiz_data['option_d']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group full-width">
                                    <label>Doğru Cevap:</label>
                                    <div class="radio-buttons">
                                        <label class="radio-label">
                                            <input type="radio" name="correct_option" value="A" <?php echo ($quiz_data['correct_option'] == 'A') ? 'checked' : ''; ?> required>
                                            A
                                        </label>
                                        <label class="radio-label">
                                            <input type="radio" name="correct_option" value="B" <?php echo ($quiz_data['correct_option'] == 'B') ? 'checked' : ''; ?>>
                                            B
                                        </label>
                                        <label class="radio-label">
                                            <input type="radio" name="correct_option" value="C" <?php echo ($quiz_data['correct_option'] == 'C') ? 'checked' : ''; ?>>
                                            C
                                        </label>
                                        <label class="radio-label">
                                            <input type="radio" name="correct_option" value="D" <?php echo ($quiz_data['correct_option'] == 'D') ? 'checked' : ''; ?>>
                                            D
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="primary-button">
                                    <i class="button-icon">✅</i> Değişiklikleri Kaydet
                                </button>
                                <a href="add_quiz.php" class="secondary-button">
                                    <i class="button-icon">🔙</i> İptal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="content-card">
                    <div class="card-header">
                        <h2>Öğrenci Cevapları</h2>
                    </div>
                    <div class="card-body">
                        <?php
                        // Get student responses for this quiz
                        $sql_responses = "SELECT 
                                        u.name as student_name, 
                                        qr.user_answer, 
                                        qr.is_correct, 
                                        qr.created_at
                                     FROM quiz_results qr
                                     JOIN users u ON qr.user_id = u.id
                                     WHERE qr.quiz_id = :quiz_id
                                     ORDER BY qr.created_at DESC";
                        $stmt_responses = $pdo->prepare($sql_responses);
                        $stmt_responses->execute(['quiz_id' => $quiz_id]);
                        $responses = $stmt_responses->fetchAll();
                        ?>
                        
                        <?php if (count($responses) > 0): ?>
                            <div class="table-responsive">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Öğrenci</th>
                                            <th>Verilen Cevap</th>
                                            <th>Sonuç</th>
                                            <th>Tarih</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($responses as $response): ?>
                                            <tr>
                                                <td><?php echo e($response['student_name']); ?></td>
                                                <td class="user-answer"><?php echo e($response['user_answer']); ?></td>
                                                <td class="<?php echo $response['is_correct'] ? 'correct-answer' : 'wrong-answer'; ?>">
                                                    <?php echo $response['is_correct'] ? '✅ Doğru' : '❌ Yanlış'; ?>
                                                </td>
                                                <td><?php echo date('d.m.Y H:i', strtotime($response['created_at'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="no-data">
                                <p>Bu quiz henüz hiçbir öğrenci tarafından cevaplanmamış.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-danger">
                    <p>Quiz bulunamadı.</p>
                    <div class="alert-actions">
                        <a href="add_quiz.php" class="btn">Quiz Listesine Dön</a>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
    
    <script src="js/main.js"></script>
</body>
</html>