# MAKÜ Online Eğitim Platformu

## Proje Hakkında
Mehmet Akif Ersoy Üniversitesi için geliştirilmiş online eğitim platformu. Öğrenciler, öğretmenler ve yöneticiler için quiz ve kurs yönetim sistemi.

## Son Değişiklikler (28 Ekim 2025)
- ✅ Gmail SMTP ile şifre sıfırlama sistemi eklendi
- ✅ 3 adımlı şifre sıfırlama süreci: Email → Kod Doğrulama → Yeni Şifre
- ✅ PHPMailer entegrasyonu ile profesyonel email gönderimi
- ✅ 6 haneli doğrulama kodu sistemi
- ✅ 15 dakika zamanaşımı kontrolü
- ✅ SQLite veritabanı desteği (Replit uyumlu)
- ✅ password_reset_tokens tablosu eklendi

## Teknoloji Stack
- **Backend**: PHP 8.2
- **Veritabanı**: SQLite (PDO)
- **Email**: PHPMailer + Gmail SMTP
- **Frontend**: HTML5, CSS3, JavaScript

## Kurulum
1. `setup_database.php` dosyasını çalıştırarak veritabanını oluşturun
2. Gmail SMTP bilgilerini Replit Secrets'a ekleyin:
   - `GMAIL_USER`: Gmail adresi
   - `GMAIL_APP_PASSWORD`: Gmail uygulama şifresi

## Kullanıcı Rolleri
- **Admin**: admin@maku.edu.tr (şifre: password)
- **Öğretmen**: ogretmen@maku.edu.tr (şifre: password)
- **Öğrenci**: ogrenci@maku.edu.tr (şifre: password)

## Şifre Sıfırlama Süreci
1. Kullanıcı "Şifremi Unuttum" sayfasına gider
2. Email adresini girer ve "Kod Gönder" butonuna tıklar
3. Sisteme kayıtlı email adresine 6 haneli doğrulama kodu gönderilir
4. Kullanıcı email'inden aldığı kodu girer
5. Kod doğrulandıktan sonra yeni şifresini belirler
6. Şifre güncellenir ve kullanıcı giriş sayfasına yönlendirilir

## Güvenlik Özellikleri
- PDO prepared statements (SQL injection koruması)
- Salt ile SHA-512 şifreleme
- Session tabanlı doğrulama
- 15 dakikalık zamanaşımı kontrolü
- CSRF koruması
- Kullanılmış kodların işaretlenmesi

## Dosya Yapısı
- `forgot_password.php`: Şifre sıfırlama ana sayfası (3 adım)
- `mailer.php`: Email gönderimi ve kod oluşturma fonksiyonları
- `config.php`: Veritabanı bağlantısı ve güvenlik fonksiyonları
- `setup_database.php`: Veritabanı kurulum scripti
- `login.php`: Kullanıcı giriş sayfası

## Email Şablonu
Email'de şunlar bulunur:
- MAKÜ logosu ve branding
- 6 haneli doğrulama kodu (büyük ve belirgin)
- 15 dakika geçerlilik süresi uyarısı
- Profesyonel HTML tasarımı
- Plain text alternatifi

## Geliştirme Notları
- Veritabanı: SQLite kullanılıyor (MySQL yerine Replit uyumluluğu için)
- Email gönderimi: Gmail SMTP üzerinden PHPMailer
- Session yönetimi: PHP native sessions
- Zamanaşımı: 900 saniye (15 dakika)

## Kullanıcı Tercihleri
- Türkçe arayüz
- Gmail SMTP kullanımı
- 6 haneli doğrulama kodu
- 15 dakika kod geçerlilik süresi
