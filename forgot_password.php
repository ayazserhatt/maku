<?php
session_start();
date_default_timezone_set('Europe/Istanbul');
include "config.php";
include "mailer.php";


$message = "";
$error = "";
$step = 1;

if (isset($_GET['cancel'])) {
    unset($_SESSION['reset_user_id'], $_SESSION['reset_user_email'], $_SESSION['reset_token_time']);
    header("Location: login.php");
    exit;
}

if (isset($_SESSION['reset_user_id']) && isset($_SESSION['reset_token_time'])) {
    if (time() - $_SESSION['reset_token_time'] > 900) {
        $error = "Oturum süresi doldu (15 dakika). Lütfen tekrar başlayın.";
        unset($_SESSION['reset_user_id'], $_SESSION['reset_user_email'], $_SESSION['reset_token_time']);
        $step = 1;
    } else {
        $step = isset($_SESSION['code_verified']) && $_SESSION['code_verified'] === true ? 3 : 2;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['send_code'])) {
        $email = secure_input($_POST["email"]);
        
        if (empty($email)) {
            $error = "E-posta adresi boş bırakılamaz!";
        } else {
            $sql = "SELECT id, name, email FROM users WHERE email = :email";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();
            
            if ($user) {
                $reset_code = generate_reset_code();
                
                date_default_timezone_set('Europe/Istanbul');
                $expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                
                $sql = "INSERT INTO password_reset_tokens (user_id, email, reset_code, expires_at) 
                        VALUES (:user_id, :email, :reset_code, :expires_at)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'user_id' => $user['id'],
                    'email' => $user['email'],
                    'reset_code' => $reset_code,
                    'expires_at' => $expires_at
                ]);
                
                if (send_reset_code($user['email'], $user['name'], $reset_code)) {
                    $_SESSION['reset_user_id'] = $user['id'];
                    $_SESSION['reset_user_email'] = $user['email'];
                    $_SESSION['reset_token_time'] = time();
                    $_SESSION['code_verified'] = false;
                    
                    $step = 2;
                    $message = "Doğrulama kodu e-posta adresinize gönderildi!";
                } else {
                    $error = "E-posta gönderilirken bir hata oluştu. Lütfen tekrar deneyin.";
                }
            } else {
                $error = "Bu e-posta adresi sistemde kayıtlı değil!";
            }
        }
    } elseif (isset($_POST['verify_code'])) {
        if (!isset($_SESSION['reset_user_id'])) {
            $error = "Oturum süresi doldu. Lütfen tekrar başlayın.";
            $step = 1;
        } elseif (time() - $_SESSION['reset_token_time'] > 900) {
            $error = "Kod süresi doldu (15 dakika). Lütfen yeni bir kod talep edin.";
            unset($_SESSION['reset_user_id'], $_SESSION['reset_user_email'], $_SESSION['reset_token_time']);
            $step = 1;
        } else {
            $entered_code = secure_input($_POST['reset_code']);
            
            if (empty($entered_code)) {
                $error = "Doğrulama kodunu girin!";
                $step = 2;
            } else {
                $sql = "SELECT * FROM password_reset_tokens 
                WHERE user_id = :user_id 
                AND reset_code = :reset_code 
                AND used = 0 
                ORDER BY created_at DESC LIMIT 1";
              if ($token) {
    if (strtotime($token['expires_at']) > time()) {
        $_SESSION['code_verified'] = true;
        $_SESSION['verified_token_id'] = $token['id'];
        $step = 3;
        $message = "Kod doğrulandı! Yeni şifrenizi belirleyin.";
    } else {
        $error = "Kodun süresi dolmuş!";
        $step = 2;
    }
} else {
    $error = "Hatalı kod!";
    $step = 2;
}


                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'user_id' => $_SESSION['reset_user_id'],
                    'reset_code' => $entered_code
                ]);
                $token = $stmt->fetch();
            
                if ($token) {
                    $_SESSION['code_verified'] = true;
                    $_SESSION['verified_token_id'] = $token['id'];
                    $step = 3;
                    $message = "Kod doğrulandı! Yeni şifrenizi belirleyin.";
                } else {
                    $error = "Hatalı veya süresi dolmuş kod!";
                    $step = 2;
                }
            }
        }
    } elseif (isset($_POST['reset_password'])) {
        if (!isset($_SESSION['reset_user_id']) || !isset($_SESSION['code_verified']) || $_SESSION['code_verified'] !== true) {
            $error = "Geçersiz işlem. Lütfen tekrar başlayın.";
            unset($_SESSION['reset_user_id'], $_SESSION['reset_user_email'], $_SESSION['reset_token_time'], $_SESSION['code_verified']);
            $step = 1;
        } elseif (time() - $_SESSION['reset_token_time'] > 900) {
            $error = "Oturum süresi doldu (15 dakika). Lütfen tekrar başlayın.";
            unset($_SESSION['reset_user_id'], $_SESSION['reset_user_email'], $_SESSION['reset_token_time'], $_SESSION['code_verified']);
            $step = 1;
        } else {
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];
            
            if (empty($new_password) || empty($confirm_password)) {
                $error = "Tüm alanları doldurun!";
                $step = 3;
            } elseif (strlen($new_password) < 6) {
                $error = "Şifre en az 6 karakter olmalıdır!";
                $step = 3;
            } elseif ($new_password !== $confirm_password) {
                $error = "Şifreler eşleşmiyor!";
                $step = 3;
            } else {
                $new_salt = generate_salt(16);
                $new_hash = hash_password($new_password, $new_salt);
                
                $sql = "UPDATE users SET password = :password, salt = :salt, updated_at = CURRENT_TIMESTAMP WHERE id = :user_id";
                $stmt = $pdo->prepare($sql);
                
                if ($stmt->execute([
                    'password' => $new_hash,
                    'salt' => $new_salt,
                    'user_id' => $_SESSION['reset_user_id']
                ])) {
                    if (isset($_SESSION['verified_token_id'])) {
                        $sql = "UPDATE password_reset_tokens SET used = 1 WHERE id = :token_id";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute(['token_id' => $_SESSION['verified_token_id']]);
                    }
                    
                    unset($_SESSION['reset_user_id'], $_SESSION['reset_user_email'], $_SESSION['reset_token_time'], $_SESSION['code_verified'], $_SESSION['verified_token_id']);
                    
                    $_SESSION['password_reset_success'] = true;
                    header("Location: login.php");
                    exit;
                } else {
                    $error = "Şifre güncellenirken bir hata oluştu!";
                    $step = 3;
                }
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
    <meta name="description" content="Mehmet Akif Ersoy Üniversitesi Şifre Sıfırlama">
    <meta name="keywords" content="MAKÜ, şifre sıfırlama">
    <meta name="author" content="Mehmet Akif Ersoy Üniversitesi">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#1A3C34">
    <title>MAKÜ - Şifremi Unuttum</title>
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
        <div class="login-box">
            <div class="login-header">
                <img src="img/school-logo.jpg" alt="MAKÜ Logosu" class="school-logo">
                <h1>Şifre Sıfırlama</h1>
                <?php if ($step == 1): ?>
                    <p>E-posta adresinizi girin</p>
                <?php elseif ($step == 2): ?>
                    <p>Doğrulama kodunu girin</p>
                <?php else: ?>
                    <p>Yeni şifrenizi belirleyin</p>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($message)): ?>
                <div class="success-message"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($step == 1): ?>
                <form method="post" action="" class="login-form">
                    <div class="input-group">
                        <input type="email" id="email" name="email" required placeholder=" ">
                        <label for="email">E-posta Adresi</label>
                        <span class="input-icon">📧</span>
                    </div>
                    
                    <button type="submit" name="send_code" class="login-button">Kod Gönder</button>
                    
                    <div class="login-options">
                        <a href="login.php" class="register-link">← Giriş Sayfasına Dön</a>
                    </div>
                </form>
            <?php elseif ($step == 2): ?>
                <form method="post" action="" class="login-form">
                    <div class="info-box">
                        <p><strong>E-posta:</strong> <?php echo e($_SESSION['reset_user_email']); ?></p>
                        <p class="text-muted">E-postanıza gönderilen 6 haneli kodu girin</p>
                        <p class="text-warning"><small>⏰ Kod 15 dakika boyunca geçerlidir</small></p>
                    </div>
                    
                    <div class="input-group">
                        <input type="text" id="reset_code" name="reset_code" required placeholder=" " maxlength="6" pattern="[0-9]{6}" inputmode="numeric">
                        <label for="reset_code">Doğrulama Kodu (6 haneli)</label>
                        <span class="input-icon">🔑</span>
                    </div>
                    
                    <button type="submit" name="verify_code" class="login-button">Kodu Doğrula</button>
                    
                    <div class="login-options">
                        <a href="forgot_password.php?cancel=1" class="register-link">İptal Et</a>
                    </div>
                </form>
            <?php else: ?>
                <form method="post" action="" class="login-form">
                    <div class="info-box">
                        <p><strong>E-posta:</strong> <?php echo e($_SESSION['reset_user_email']); ?></p>
                        <p class="text-muted">Yeni şifrenizi aşağıya girin</p>
                    </div>
                    
                    <div class="input-group">
                        <input type="password" id="new_password" name="new_password" required placeholder=" " minlength="6">
                        <label for="new_password">Yeni Şifre</label>
                        <span class="input-icon">🔒</span>
                        <span class="toggle-password">👁️</span>
                    </div>
                    
                    <div class="input-group">
                        <input type="password" id="confirm_password" name="confirm_password" required placeholder=" " minlength="6">
                        <label for="confirm_password">Şifre Tekrar</label>
                        <span class="input-icon">🔒</span>
                        <span class="toggle-password">👁️</span>
                    </div>
                    
                    <div class="password-requirements">
                        <p><small>• Şifre en az 6 karakter olmalıdır</small></p>
                        <p><small>• Güvenli bir şifre kullanmanız önerilir</small></p>
                    </div>
                    
                    <button type="submit" name="reset_password" class="login-button">Şifreyi Güncelle</button>
                    
                    <div class="login-options">
                        <a href="forgot_password.php?cancel=1" class="register-link">İptal Et</a>
                    </div>
                </form>
            <?php endif; ?>
            
            <p class="motto">"Bilginin Işığında Geleceğe"</p>
        </div>
    </div>
    
    <script src="js/main.js"></script>
</body>
</html>