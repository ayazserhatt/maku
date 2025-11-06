<?php
putenv("GMAIL_USER=ayazserhattt@gmail.com");
putenv("GMAIL_APP_PASSWORD=dnpv vfjq ihlf vvno");
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function send_reset_code($to_email, $to_name, $reset_code) {
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = getenv('GMAIL_USER');
        $mail->Password = getenv('GMAIL_APP_PASSWORD');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';
        
        $mail->setFrom(getenv('GMAIL_USER'), 'MAKÜ Online Eğitim Platformu');
        $mail->addAddress($to_email, $to_name);
        
        $mail->isHTML(true);
        $mail->Subject = 'Şifre Sıfırlama Kodu - MAKÜ';
        
        $mail->Body = '
        <!DOCTYPE html>
        <html lang="tr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    background-color: #f4f4f4;
                    margin: 0;
                    padding: 0;
                }
                .container {
                    max-width: 600px;
                    margin: 20px auto;
                    background: #fff;
                    border-radius: 8px;
                    overflow: hidden;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                }
                .header {
                    background: #1A3C34;
                    color: #fff;
                    padding: 30px;
                    text-align: center;
                }
                .header h1 {
                    margin: 0;
                    font-size: 24px;
                }
                .content {
                    padding: 30px;
                }
                .code-box {
                    background: #f8f9fa;
                    border: 2px solid #1A3C34;
                    border-radius: 8px;
                    padding: 20px;
                    text-align: center;
                    margin: 20px 0;
                }
                .code {
                    font-size: 32px;
                    font-weight: bold;
                    color: #1A3C34;
                    letter-spacing: 8px;
                    font-family: "Courier New", monospace;
                }
                .info {
                    background: #fff3cd;
                    border-left: 4px solid #ffc107;
                    padding: 15px;
                    margin: 20px 0;
                }
                .footer {
                    background: #f8f9fa;
                    padding: 20px;
                    text-align: center;
                    font-size: 12px;
                    color: #6c757d;
                }
                .button {
                    display: inline-block;
                    padding: 12px 30px;
                    background: #1A3C34;
                    color: #fff;
                    text-decoration: none;
                    border-radius: 5px;
                    margin: 10px 0;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Mehmet Akif Ersoy Üniversitesi</h1>
                    <p>Şifre Sıfırlama Talebi</p>
                </div>
                <div class="content">
                    <p>Merhaba <strong>' . htmlspecialchars($to_name) . '</strong>,</p>
                    <p>Hesabınız için bir şifre sıfırlama talebi aldık. Aşağıdaki 6 haneli doğrulama kodunu kullanarak şifrenizi sıfırlayabilirsiniz:</p>
                    
                    <div class="code-box">
                        <div class="code">' . $reset_code . '</div>
                    </div>
                    
                    <div class="info">
                        <strong>⏰ Önemli:</strong> Bu kod <strong>15 dakika</strong> boyunca geçerlidir. Süresi dolduktan sonra yeni bir kod talep etmeniz gerekecektir.
                    </div>
                    
                    <p>Bu kodu şifre sıfırlama sayfasında girmeniz yeterlidir.</p>
                    
                    <p><strong>Not:</strong> Eğer bu talebi siz yapmadıysanız, bu e-postayı görmezden gelebilirsiniz. Hesabınız güvende.</p>
                    
                    <p>Saygılarımızla,<br>
                    <strong>MAKÜ Online Eğitim Platformu</strong></p>
                </div>
                <div class="footer">
                    <p>"Bilginin Işığında Geleceğe"</p>
                    <p>Mehmet Akif Ersoy Üniversitesi</p>
                    <p style="margin-top: 10px; font-size: 11px;">
                        Bu otomatik bir mesajdır. Lütfen yanıtlamayın.
                    </p>
                </div>
            </div>
        </body>
        </html>
        ';
        
        $mail->AltBody = "Merhaba $to_name,\n\n" .
                        "Hesabınız için bir şifre sıfırlama talebi aldık.\n\n" .
                        "Doğrulama Kodunuz: $reset_code\n\n" .
                        "Bu kod 15 dakika boyunca geçerlidir.\n\n" .
                        "Eğer bu talebi siz yapmadıysanız, bu e-postayı görmezden gelebilirsiniz.\n\n" .
                        "Saygılarımızla,\n" .
                        "MAKÜ Online Eğitim Platformu";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email gönderimi başarısız: {$mail->ErrorInfo}");
        return false;
    }
}

function generate_reset_code() {
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}
?>
