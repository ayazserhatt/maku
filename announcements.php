<?php
require_once 'config.php';

$page_title = "Duyurular - MAKÜ";

try {
    $stmt = $pdo->query("SELECT a.*, u.name AS creator_name 
                         FROM announcements a 
                         JOIN users u ON a.created_by = u.id 
                         ORDER BY a.created_at DESC");
    $announcements = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Duyurular yüklenirken hata oluştu: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($page_title); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="icon" type="image/jpeg" href="img/header-logo.jpg">
    <style>
        /* Mevcut CSS'nizle uyumlu ek stil */
        .announcements-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .announcements-header {
            text-align: center;
            margin-bottom: 3rem;
            position: relative;
        }
        
        .announcements-header h1 {
            font-size: 2.5rem;
            color: var(--white);
            margin-bottom: 1rem;
            text-shadow: var(--shadow-sm);
        }
        .announcements-header p{
            color: #fff;
        }
        
        .announcements-header::after {
            content: '';
            display: block;
            width: 100px;
            height: 4px;
            background: var(--secondary-color);
            margin: 1rem auto;
            border-radius: 2px;
        }
        
        .announcements-list {
            display: grid;
            gap: 2rem;
        }
        
        .announcement-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            transition: transform var(--transition-normal), box-shadow var(--transition-normal);
        }
        
        .announcement-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        
        .announcement-header {
            background: var(--primary-color);
            color: white;
            padding: 1.5rem;
            position: relative;
        }
        
        .announcement-header h2 {
            margin: 0;
            font-size: 1.5rem;
            color: var(--white);
            padding-right: 2rem;
        }
        
        .announcement-icon {
            position: absolute;
            right: 1.5rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.8rem;
            color: rgba(255, 255, 255, 0.7);
        }
        
        .announcement-meta {
            display: flex;
            justify-content: space-between;
            margin-top: 0.8rem;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.9);
        }
        
        .announcement-meta i {
            margin-right: 0.5rem;
        }
        
        .announcement-content {
            padding: 2rem;
            line-height: 1.7;
            color: var(--text-dark);
        }
        
        .announcement-content p {
            margin-bottom: 1rem;
        }
        
        .no-announcements {
            text-align: center;
            padding: 4rem 2rem;
            background: rgba(255, 255, 255, 0.9);
            border-radius: var(--border-radius-lg);
            color: var(--dark-gray);
        }
        
        .no-announcements i {
            font-size: 3rem;
            color: var(--secondary-color);
            margin-bottom: 1.5rem;
            opacity: 0.7;
        }
        
        .no-announcements p {
            font-size: 1.2rem;
            margin-bottom: 0;
        }
        
        @media (max-width: 768px) {
            .announcements-header h1 {
                font-size: 2rem;
            }
            
            .announcement-header {
                padding: 1rem;
            }
            
            .announcement-header h2 {
                font-size: 1.3rem;
            }
            
            .announcement-content {
                padding: 1.5rem;
            }
            
            .announcement-meta {
                flex-direction: column;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <header id="header" class="header">
        <div class="container">
            <img src="img/school-logo.jpg" alt="MAKÜ Logo" class="header-logo">
            <div class="nav-toggle">☰</div>
            <nav id="navmenu" class="navmenu">
                <ul>
                    <li><a href="index.php">Ana Sayfa</a></li>
                    <li><a href="announcements.php" class="active">Duyurular</a></li>
                    <li><a href="contact.php">İletişim</a></li>
                    <?php if(isset($_SESSION["user_id"])): ?>
                        <?php if($_SESSION["user_role"] == "admin"): ?>
                            <li><a href="admin_dashboard.php" class="btn-action">Yönetim Paneli</a></li>
                        <?php elseif($_SESSION["user_role"] == "teacher"): ?>
                            <li><a href="teacher_dashboard.php" class="btn-action">Öğretmen Paneli</a></li>
                        <?php elseif($_SESSION["user_role"] == "student"): ?>
                            <li><a href="student_dashboard.php" class="btn-action">Öğrenci Paneli</a></li>
                        <?php endif; ?>
                    <?php else: ?>
                        <li><a href="login.php" class="btn-action">Giriş Yap</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main class="main">
        <div class="announcements-container">
            <div class="announcements-header">
                <h1><i class="fas fa-bullhorn"></i> Duyurular</h1>
                <p  >Mehmet Akif Ersoy Üniversitesi'nin en güncel duyuruları</p>
            </div>
            
            <div class="announcements-list">
                <?php if(empty($announcements)): ?>
                    <div class="no-announcements">
                        <i class="far fa-bell-slash"></i>
                        <p>Henüz duyuru bulunmamaktadır.</p>
                    </div>
                <?php else: ?>
                    <?php foreach($announcements as $announcement): ?>
                        <article class="announcement-card">
                            <div class="announcement-header">
                                <h2><?php echo e($announcement['title']); ?></h2>
                                <i class="fas fa-newspaper announcement-icon"></i>
                                <div class="announcement-meta">
                                    <span><i class="fas fa-user"></i> <?php echo e($announcement['creator_name']); ?></span>
                                    <span><i class="far fa-clock"></i> <?php echo date('d.m.Y H:i', strtotime($announcement['created_at'])); ?></span>
                                </div>
                            </div>
                            <div class="announcement-content">
                                <?php echo nl2br(e($announcement['content'])); ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <img src="img/school-logo.jpg" alt="MAKÜ Logo">
                    <p>Mehmet Akif Ersoy Üniversitesi</p>
                </div>
                <div class="footer-links">
                    <h3>Hızlı Bağlantılar</h3>
                    <ul>
                        <li><a href="index.php">Ana Sayfa</a></li>
                        <li><a href="announcements.php">Duyurular</a></li>
                        <li><a href="contact.php">İletişim</a></li>
                        <li><a href="login.php">Giriş Yap</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h3>İletişim</h3>
                    <p><strong>Adres:</strong> MAKÜ, İstiklal Yerleşkesi, Burdur</p>
                    <p><strong>E-posta:</strong> info@maku.edu.tr</p>
                    <p><strong>Telefon:</strong> +90 248 213 10 00</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date("Y"); ?> Mehmet Akif Ersoy Üniversitesi. Tüm hakları saklıdır.</p>
            </div>
        </div>
    </footer>
    
    <script src="js/main.js"></script>
</body>
</html>