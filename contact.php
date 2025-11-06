<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Mehmet Akif Ersoy Üniversitesi - İletişim Sayfası">
    <meta name="keywords" content="MAKÜ, Mehmet Akif Ersoy Üniversitesi, iletişim, destek, online eğitim">
    <meta name="author" content="Mehmet Akif Ersoy Üniversitesi">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#1A3C34">
    <title>MAKÜ - İletişim</title>
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
                    <li><a href="index.php">Ana Sayfa</a></li>
                    <li><a href="announcements.php">Duyurular</a></li>
                    <li><a href="contact.php" class="active">İletişim</a></li>
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
        <div class="section">
            <div class="container">
                <h1>İletişim</h1>
                <p>Bize ulaşmak için aşağıdaki formu doldurabilir veya iletişim bilgilerimizi kullanabilirsiniz.</p>
                <form action="contact.php" method="POST" class="login-form">
                    <div class="input-group">
                        <input type="text" name="name" id="name" placeholder=" " required>
                        <label for="name">Ad Soyad</label>
                        <span class="input-icon">👤</span>
                    </div>
                    <div class="input-group">
                        <input type="email" name="email" id="email" placeholder=" " required>
                        <label for="email">E-posta</label>
                        <span class="input-icon">✉️</span>
                    </div>
                    <div class="input-group">
                        <textarea name="message" id="message" placeholder=" " required></textarea>
                        <label for="message">Mesajınız</label>
                        <span class="input-icon">📝</span>
                    </div>
                    <button type="submit" class="contact-button">Mesaj Gönder</button>
                </form>
                <?php
                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    $name = secure_input($_POST['name']);
                    $email = secure_input($_POST['email']);
                    $message = secure_input($_POST['message']);
                    
                    // Basic form validation
                    if (!empty($name) && !empty($email) && !empty($message) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        // In a production environment, you would typically save to a database or send an email
                        // For demonstration, we'll show a success message
                        echo '<div class="alert alert-success">Mesajınız başarıyla gönderildi! En kısa sürede size dönüş yapacağız.</div>';
                    } else {
                        echo '<div class="alert alert-danger">Lütfen tüm alanları doğru bir şekilde doldurun.</div>';
                    }
                }
                ?>
            </div>
        </div>
        <div class="features-section">
            <div class="container">
                <h2>İletişim Bilgilerimiz</h2>
                <div class="features-grid">
                    <div class="feature-item">
                        <div class="feature-icon">📍</div>
                        <h3>Adres</h3>
                        <p>MAKÜ, İstiklal Yerleşkesi, Burdur, Türkiye</p>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">📧</div>
                        <h3>E-posta</h3>
                        <p><a href="mailto:info@maku.edu.tr">info@maku.edu.tr</a></p>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">📞</div>
                        <h3>Telefon</h3>
                        <p>+90 248 213 10 00</p>
                    </div>
                </div>
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