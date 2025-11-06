<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include "config.php";

$hataMesaji = "";
$successMessage = "";

// Check for password reset success
if (isset($_SESSION['password_reset_success'])) {
    $successMessage = "Şifreniz başarıyla güncellendi! Yeni şifreniz ile giriş yapabilirsiniz.";
    unset($_SESSION['password_reset_success']);
}

// Check if already logged in
if (isset($_SESSION["user_id"])) {
    // Redirect based on roleg
    switch ($_SESSION["user_role"]) {
        case "student":
            header("Location: student_dashboard.php");
            exit;
        case "teacher":
            header("Location: teacher_dashboard.php");
            exit;
        case "admin":
            header("Location: admin_dashboard.php");
            exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = secure_input($_POST["email"]);
    $password = $_POST["password"];

    if (empty($email) || empty($password)) {
        $hataMesaji = "E-posta ve şifre alanları boş bırakılamaz!";
    } else {
        // Prepare SQL statement to prevent SQL injection
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user) {
            // Check if user has salt field
            if (isset($user["salt"])) {
                $stored_hash = $user["password"];
                $stored_salt = $user["salt"];
                $hashed_password = hash('sha512', $password . $stored_salt);
                
                if ($hashed_password === $stored_hash) {
                    login_success($user);
                } else {
                    $hataMesaji = "Hatalı şifre!";
                }
            } else {
                // For users without salt field (old data)
                if (password_verify($password, $user["password"])) {
                    login_success($user);
                } else {
                    $hataMesaji = "Hatalı şifre!";
                }
            }
        } else {
            $hataMesaji = "Kullanıcı bulunamadı!";
        }
    }
}

function login_success($user) {
    $_SESSION["user_id"] = $user["id"];
    $_SESSION["user_name"] = $user["name"];
    $_SESSION["user_email"] = $user["email"];
    $_SESSION["user_role"] = $user["role"];
    
    // Redirect based on role
    switch ($user["role"]) {
        case "student":
            header("Location: student_dashboard.php");
            break;
        case "teacher":
            header("Location: teacher_dashboard.php");
            break;
        case "admin":
            header("Location: admin_dashboard.php");
            break;
        default:
            $hataMesaji = "Geçersiz rol!";
            return;
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Mehmet Akif Ersoy Üniversitesi Yönetim Paneli Girişi">
    <meta name="keywords" content="MAKÜ, giriş, yönetim paneli">
    <meta name="author" content="Mehmet Akif Ersoy Üniversitesi">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#1A3C34">
    <title>MAKÜ - Giriş Yap</title>
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
                    <li><a href="contact.php">İletişim</a></li>
                    <li><a href="login.php" class="active btn-action">Giriş Yap</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <img src="img/school-logo.jpg" alt="MAKÜ Logosu" class="school-logo">
                <h1>Mehmet Akif Ersoy Üniversitesi</h1>
                <p>Online Eğitim Platformu Girişi</p>
            </div>
            <form method="post" action="" class="login-form">
                <?php if (!empty($hataMesaji)) { ?>
                    <div class="error-message"><?php echo $hataMesaji; ?></div>
                <?php } ?>
                <?php if (!empty($successMessage)) { ?>
                    <div class="success-message"><?php echo $successMessage; ?></div>
                <?php } ?>
                <div class="input-group">
                    <input type="email" id="email" name="email" required placeholder=" ">
                    <label for="email">E-posta</label>
                    <span class="input-icon">📧</span>
                </div>
                <div class="input-group">
                    <input type="password" id="password" name="password" required placeholder=" ">
                    <label for="password">Şifre</label>
                    <span class="input-icon">🔒</span>
                    <span class="toggle-password">👁️</span>
                </div>
                <button type="submit" class="login-button">Giriş Yap</button>
                <div class="login-options">
                    <a href="forgot_password.php" class="forgot-password">Şifremi Unuttum</a>
                    <span class="divider">•</span>
                    <a href="register.php" class="register-link">Yeni Kayıt</a>
                </div>
            </form>
            <p class="motto">"Bilginin Işığında Geleceğe"</p>
        </div>
    </div>
    <script src="js/main.js"></script>
</body>
</html>
