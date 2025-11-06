<?php
session_start();
include "config.php";

// Check if user is a student
require_student();

$student_id = $_SESSION["user_id"];

// Pagination settings
$records_per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $records_per_page;

// Filter by course if specified
$course_filter = "";
$filter_params = ['student_id' => $student_id];

if (isset($_GET['course_id']) && is_numeric($_GET['course_id'])) {
    $course_id = intval($_GET['course_id']);
    $course_filter = "AND c.id = :course_id";
    $filter_params['course_id'] = $course_id;
}

// Get total records for pagination
$sql_count = "SELECT COUNT(*) as total FROM quiz_results qr
              JOIN quizzes q ON qr.quiz_id = q.id
              JOIN courses c ON q.course_id = c.id
              WHERE qr.user_id = :student_id $course_filter";
$stmt_count = $pdo->prepare($sql_count);
$stmt_count->execute($filter_params);
$total_records = $stmt_count->fetch()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Get quiz history with pagination
$sql_history = "SELECT 
                qr.id, 
                qr.created_at,
                qr.user_answer,
                qr.is_correct,
                q.question,
                q.correct_option,
                q.option_a,
                q.option_b,
                q.option_c,
                q.option_d,
                c.id as course_id,
                c.name as course_name
               FROM quiz_results qr
               JOIN quizzes q ON qr.quiz_id = q.id
               JOIN courses c ON q.course_id = c.id
               WHERE qr.user_id = :student_id $course_filter
               ORDER BY qr.created_at DESC
               LIMIT :limit OFFSET :offset";
$stmt_history = $pdo->prepare($sql_history);
$stmt_history->bindValue(':limit', $records_per_page, PDO::PARAM_INT);
$stmt_history->bindValue(':offset', $offset, PDO::PARAM_INT);

foreach ($filter_params as $param => $value) {
    $stmt_history->bindValue(":$param", $value);
}

$stmt_history->execute();
$quiz_history = $stmt_history->fetchAll();

// Get all courses for filter dropdown
$sql_courses = "SELECT DISTINCT c.id, c.name
               FROM courses c
               JOIN quizzes q ON q.course_id = c.id
               JOIN quiz_results qr ON qr.quiz_id = q.id
               WHERE qr.user_id = :student_id
               ORDER BY c.name";
$stmt_courses = $pdo->prepare($sql_courses);
$stmt_courses->execute(['student_id' => $student_id]);
$courses = $stmt_courses->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Mehmet Akif Ersoy Üniversitesi Öğrenci Quiz Geçmişi">
    <meta name="keywords" content="MAKÜ, öğrenci, quiz, geçmiş, sonuçlar">
    <meta name="author" content="Mehmet Akif Ersoy Üniversitesi">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#1A3C34">
    <title>MAKÜ - Quiz Geçmişim</title>
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
                    <li><a href="student_dashboard.php">Anasayfa</a></li>
                    <li><a href="student_courses.php">Derslerim</a></li>
                    <li><a href="student_quiz_history.php" class="active">Quiz Geçmişim</a></li>
                    <li><a href="student_quiz_stats.php">İstatistiklerim</a></li>
                    <li><a href="islem/logout.php" class="btn-action">Çıkış</a></li>
                </ul>
            </nav>
        </div>
    </header>
    
    <div class="dashboard-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="img/school-logo.jpg" alt="MAKÜ Logo" class="sidebar-logo">
                <h3>Öğrenci Paneli</h3>
            </div>
            <ul class="sidebar-menu">
                <li><a href="student_dashboard.php"><i class="icon">🏠</i> Ana Sayfa</a></li>
                <li><a href="student_courses.php"><i class="icon">📚</i> Derslerim</a></li>
                <li><a href="student_quiz_history.php" class="active"><i class="icon">📝</i> Quiz Geçmişim</a></li>
                <li><a href="student_quiz_stats.php"><i class="icon">📊</i> İstatistiklerim</a></li>
                <li><a href="islem/logout.php"><i class="icon">🚪</i> Çıkış</a></li>
            </ul>
        </div>
        
        <main class="dashboard-content">
            <div class="dashboard-header">
                <h1>Quiz Geçmişim</h1>
                <p>Çözdüğünüz quizlerin geçmişini ve cevaplarınızı buradan görüntüleyebilirsiniz.</p>
            </div>
            
            <?php if (count($quiz_history) > 0 || count($courses) > 0): ?>
                <div class="filter-section">
                    <form method="GET" action="" class="filter-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="course_id">Derse Göre Filtrele:</label>
                                <select id="course_id" name="course_id" onchange="this.form.submit()">
                                    <option value="">Tüm Dersler</option>
                                    <?php foreach ($courses as $course): ?>
                                        <option value="<?php echo $course['id']; ?>" <?php echo (isset($_GET['course_id']) && $_GET['course_id'] == $course['id']) ? 'selected' : ''; ?>>
                                            <?php echo e($course['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <?php if (isset($_GET['course_id']) && !empty($_GET['course_id'])): ?>
                                <div class="form-actions">
                                    <a href="student_quiz_history.php" class="btn-action clear-filter">Filtreyi Temizle</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
            
            <?php if (count($quiz_history) > 0): ?>
                <div class="content-card">
                    <div class="card-header">
                        <h2>Quiz Sonuçlarım</h2>
                        <div class="card-actions">
                            <input type="text" id="quizSearch" class="search-input" placeholder="Quiz ara...">
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="data-table" id="quizHistoryTable">
                                <thead>
                                    <tr>
                                        <th>Tarih</th>
                                        <th>Ders</th>
                                        <th>Soru</th>
                                        <th>Cevabınız</th>
                                        <th>Doğru Cevap</th>
                                        <th>Sonuç</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($quiz_history as $record): ?>
                                        <?php 
                                        // Map answer letters to full text
                                        $option_map = [
                                            'A' => $record['option_a'],
                                            'B' => $record['option_b'], 
                                            'C' => $record['option_c'],
                                            'D' => $record['option_d']
                                        ];
                                        $user_answer_text = $option_map[$record['user_answer']] ?? '';
                                        $correct_answer_text = $option_map[$record['correct_option']] ?? '';
                                        ?>
                                        <tr>
                                            <td><?php echo date('d.m.Y H:i', strtotime($record['created_at'])); ?></td>
                                            <td>
                                                <a href="student_courses.php?course_id=<?php echo $record['course_id']; ?>">
                                                    <?php echo e($record['course_name']); ?>
                                                </a>
                                            </td>
                                            <td class="quiz-question-cell">
                                                <div class="quiz-question-tooltip">
                                                    <?php echo e(substr($record['question'], 0, 50) . (strlen($record['question']) > 50 ? '...' : '')); ?>
                                                    <span class="tooltip-text"><?php echo e($record['question']); ?></span>
                                                </div>
                                            </td>
                                            <td><?php echo e($record['user_answer']); ?></td>
                                            <td><?php echo e($record['correct_option']); ?></td>
                                            <td class="<?php echo $record['is_correct'] ? 'correct-answer' : 'wrong-answer'; ?>">
                                                <?php echo $record['is_correct'] ? '✅ Doğru' : '❌ Yanlış'; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if ($total_pages > 1): ?>
                            <div class="pagination">
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?php echo ($page - 1); ?><?php echo isset($_GET['course_id']) ? '&course_id=' . intval($_GET['course_id']) : ''; ?>" class="pagination-arrow">&laquo;</a>
                                <?php endif; ?>
                                
                                <?php
                                // Calculate range of page numbers to display
                                $start_page = max(1, $page - 2);
                                $end_page = min($total_pages, $page + 2);
                                
                                // Always show first page
                                if ($start_page > 1) {
                                    echo '<a href="?page=1' . (isset($_GET['course_id']) ? '&course_id=' . intval($_GET['course_id']) : '') . '">1</a>';
                                    if ($start_page > 2) {
                                        echo '<span class="pagination-ellipsis">...</span>';
                                    }
                                }
                                
                                // Display page numbers
                                for ($i = $start_page; $i <= $end_page; $i++) {
                                    $active_class = ($i == $page) ? 'active' : '';
                                    echo '<a href="?page=' . $i . (isset($_GET['course_id']) ? '&course_id=' . intval($_GET['course_id']) : '') . '" class="' . $active_class . '">' . $i . '</a>';
                                }
                                
                                // Always show last page
                                if ($end_page < $total_pages) {
                                    if ($end_page < $total_pages - 1) {
                                        echo '<span class="pagination-ellipsis">...</span>';
                                    }
                                    echo '<a href="?page=' . $total_pages . (isset($_GET['course_id']) ? '&course_id=' . intval($_GET['course_id']) : '') . '">' . $total_pages . '</a>';
                                }
                                ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <a href="?page=<?php echo ($page + 1); ?><?php echo isset($_GET['course_id']) ? '&course_id=' . intval($_GET['course_id']) : ''; ?>" class="pagination-arrow">&raquo;</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="content-card">
                    <div class="card-body">
                        <div class="no-data">
                            <p>Henüz hiç quiz çözmediniz. Geçmiş görüntülemek için önce derslere katılıp quizleri çözmelisiniz.</p>
                            <div class="no-data-actions">
                                <a href="student_courses.php" class="btn-action">Derslere Git</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
    
    <script src="js/main.js"></script>
    <script>
        // Search functionality for quiz history
        document.getElementById('quizSearch').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#quizHistoryTable tbody tr');
            
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