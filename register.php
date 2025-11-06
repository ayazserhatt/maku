<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include "config.php";

$success = false;
$error = "";

// If already logged in, redirect based on role
if (isset($_SESSION["user_id"])) {
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
    $name = secure_input($_POST["name"]);
    $email = secure_input($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    $role = $_POST["role"];
    
    // Validate input
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password) || empty($role)) {
        $error = "Tüm alanları doldurunuz!";
    } elseif ($password !== $confirm_password) {
        $error = "Şifreler eşleşmiyor!";
    } elseif (strlen($password) < 6) {
        $error = "Şifre en az 6 karakter olmalıdır!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Geçerli bir e-posta adresi giriniz!";
    } else {
        // Check if email already exists
        $check_sql = "SELECT * FROM users WHERE email = :email";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute(['email' => $email]);
        $user_exists = $check_stmt->fetch();
        
        if ($user_exists) {
            $error = "Bu e-posta adresi zaten kayıtlı!";
        } else {
            // Generate salt and hash password
            $salt = generate_salt();
            $hashed_password = hash_password($password, $salt);
            
            // Insert new user
            $insert_sql = "INSERT INTO users (name, email, password, salt, role) VALUES (:name, :email, :password, :salt, :role)";
            $insert_stmt = $pdo->prepare($insert_sql);
            $insert_params = [
                'name' => $name,
                'email' => $email,
                'password' => $hashed_password,
                'salt' => $salt,
                'role' => $role
            ];
            
            if ($insert_stmt->execute($insert_params)) {
                $success = true;
            } else {
                $error = "Kayıt oluşturulurken bir hata oluştu.";
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
    <meta name="description" content="Mehmet Akif Ersoy Üniversitesi Kayıt Sayfası">
    <meta name="keywords" content="MAKÜ, kayıt, üyelik, eğitim platformu">
    <meta name="author" content="Mehmet Akif Ersoy Üniversitesi">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#1A3C34">
    <title>MAKÜ - Yeni Kayıt</title>
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
                    <li><a href="login.php" class="btn-action">Giriş Yap</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <div class="login-container">
        <div class="login-box register-box">
            <div class="login-header">
                <img src="img/school-logo.jpg" alt="MAKÜ Logosu" class="school-logo">
                <h1>Mehmet Akif Ersoy Üniversitesi</h1>
                <p>Online Eğitim Platformuna Kayıt</p>
            </div>
            
            <?php if ($success): ?>
                <div class="success-message">
                    <h2>Kayıt Başarılı!</h2>
                    <p>Hesabınız başarıyla oluşturuldu. Şimdi giriş yapabilirsiniz.</p>
                    <a href="login.php" class="login-button">Giriş Yap</a>
                </div>
            <?php else: ?>
                <form method="post" action="" class="login-form register-form">
                    <?php if (!empty($error)): ?>
                        <div class="error-message"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <div class="input-group">
                        <input type="text" id="name" name="name" required placeholder=" " value="<?php echo isset($_POST['name']) ? e($_POST['name']) : ''; ?>">
                        <label for="name">Ad Soyad</label>
                        <span class="input-icon">👤</span>
                    </div>
                    
                    <div class="input-group">
                        <input type="email" id="email" name="email" required placeholder=" " value="<?php echo isset($_POST['email']) ? e($_POST['email']) : ''; ?>">
                        <label for="email">E-posta</label>
                        <span class="input-icon">📧</span>
                    </div>
                    
                    <div class="input-group">
                        <input type="password" id="password" name="password" required placeholder=" ">
                        <label for="password">Şifre</label>
                        <span class="input-icon">🔒</span>
                        <span class="toggle-password">👁️</span>
                    </div>
                    
                    <div class="input-group">
                        <input type="password" id="confirm_password" name="confirm_password" required placeholder=" ">
                        <label for="confirm_password">Şifre Tekrar</label>
                        <span class="input-icon">🔒</span>
                    </div>
                    
                    <div class="input-group role-select">
                        <select name="role" id="role" required>
                            <option value="" disabled selected>Rol seçiniz</option>
                            <option value="student" <?php echo (isset($_POST['role']) && $_POST['role'] == 'student') ? 'selected' : ''; ?>>Öğrenci</option>
                            <option value="teacher" <?php echo (isset($_POST['role']) && $_POST['role'] == 'teacher') ? 'selected' : ''; ?>>Öğretmen</option>
                        </select>
                        <label for="role">Rolünüz</label>
                        <span class="input-icon">🎓</span>
                    </div>
                    
                    <button type="submit" class="login-button">Kayıt Ol</button>
                    <p class="login-link">Zaten hesabınız var mı? <a href="login.php">Giriş Yap</a></p>
                </form>
            <?php endif; ?>
            
            <p class="motto">"Bilginin Işığında Geleceğe"</p>
        </div>
    </div>
    <script src="js/main.js"></script>
</body>
</html>
