<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Mehmet Akif Ersoy Üniversitesi - Bilginin Işığında Geleceğe">
    <meta name="keywords" content="MAKÜ, Mehmet Akif Ersoy Üniversitesi, üniversite, eğitim, online ders, e-öğrenme">
    <meta name="author" content="Mehmet Akif Ersoy Üniversitesi">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#1A3C34">
    <title>MAKÜ - Ana Sayfa</title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="icon" type="image/jpeg" href="img/header-logo.jpg">
</head>
<body>
    <header id="header" class="header">
        <div class="container">
            <img src="img/school-logo.jpg" alt="MAKÜ Logo" class="header-logo">
            <div class="nav-toggle">☰</div>
            <nav id="navmenu" class="navmenu">
                <ul>
                    <li><a href="index.php" class="active">Ana Sayfa</a></li>
                    <li><a href="announcements.php">Duyurular</a></li>
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
        <div class="hero-section">
            <div class="container">
                <h1>Mehmet Akif Ersoy Üniversitesi</h1>
                <h2>Online Eğitim Platformu</h2>
                <p>Bilginin ışığında geleceğe! MAKÜ, akademik mükemmeliyet ve yenilikçi eğitim anlayışıyla sizleri bekliyor.</p>
                <?php if(isset($_SESSION["user_id"])): ?>
                    <?php if($_SESSION["user_role"] == "admin"): ?>
                        <a href="admin_dashboard.php" class="hero-button">Yönetim Paneline Git</a>
                    <?php elseif($_SESSION["user_role"] == "teacher"): ?>
                        <a href="teacher_dashboard.php" class="hero-button">Derslerinizi Yönetin</a>
                    <?php elseif($_SESSION["user_role"] == "student"): ?>
                        <a href="student_dashboard.php" class="hero-button">Derslerinize Erişin</a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="login.php" class="hero-button">Hemen Giriş Yapın</a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="features-section">
            <div class="container">
                <h2>Platformumuzun Özellikleri</h2>
                <div class="features-grid">
                    <div class="feature-item">
                        <div class="feature-icon">📚</div>
                        <h3>Online Dersler</h3>
                        <p>İstediğiniz zaman, istediğiniz yerden derslere erişin. Eğitim artık daha esnek ve erişilebilir.</p>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">📝</div>
                        <h3>İnteraktif Quizler</h3>
                        <p>Bilginizi ölçün ve anlık geri bildirim alın. Öğrenme sürecinizi aktif olarak değerlendirin.</p>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">📊</div>
                        <h3>İlerleme Takibi</h3>
                        <p>Öğrenim sürecinizi adım adım izleyin ve başarınızı görselleştirin.</p>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">👨‍🏫</div>
                        <h3>Uzman Eğitmenler</h3>
                        <p>Alanında uzman öğretim üyelerinden kaliteli eğitim alın.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="cta-section">
            <div class="container">
                <h2>Eğitim Yolculuğuna Hemen Başlayın</h2>
                <p>MAKÜ Online Eğitim Platformu ile kariyerinizde bir adım öne geçin.</p>
                <a href="login.php" class="cta-button">Şimdi Giriş Yapın</a>
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
