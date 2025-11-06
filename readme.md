# 🎓 MAKÜ Online Eğitim Platformu


## 📋 İçindekiler

- [Proje Hakkında](#-proje-hakkında)
- [Özellikler](#-özellikler)
- [Teknoloji Stack](#-teknoloji-stack)
- [Kullanım](#-kullanım)
- [Kullanıcı Rolleri](#-kullanıcı-rolleri)
- [Proje Yapısı](#-proje-yapısı)

---

## 🎯 Proje Hakkında

MAKÜ Online Eğitim Platformu, Mehmet Akif Ersoy Üniversitesi için geliştirilmiş modern bir eğitim yönetim sistemidir. Platform, öğrenciler, öğretmenler ve yöneticiler için kapsamlı quiz ve kurs yönetimi sunmaktadır.


## ✨ Özellikler

### 📚 Online Dersler
- ✅ İstediğiniz zaman, istediğiniz yerden derslere erişim
- ✅ Esnek ve erişilebilir eğitim içeriği
- ✅ Kurs oluşturma ve yönetimi (öğretmenler için)
- ✅ Kurs katılımı ve içerik görüntüleme (öğrenciler için)

### 📝 İnteraktif Quizler
- ✅ Çoktan seçmeli quiz oluşturma sistemi
- ✅ Anlık geri bildirim ve sonuç hesaplama
- ✅ Quiz geçmişi ve detaylı istatistikler
- ✅ Zamanlı quiz desteği
- ✅ Otomatik puanlama ve değerlendirme

### 📊 İlerleme Takibi
- ✅ Detaylı öğrenim süreç takibi
- ✅ Quiz başarı oranları ve istatistikleri
- ✅ Görsel grafikler ve raporlar
- ✅ Performans analizi

### 👨‍🏫 Uzman Eğitmenler
- ✅ Öğretmen paneli
- ✅ Kurs ve quiz yönetimi
- ✅ Öğrenci performans takibi
- ✅ İçerik paylaşımı

### 🔐 Güvenlik ve Kullanıcı Yönetimi
- ✅ Rol tabanlı erişim kontrolü (Admin, Öğretmen, Öğrenci)
- ✅ Güvenli oturum yönetimi
- ✅ SHA-512 + Salt şifreleme
- ✅ Şifre sıfırlama sistemi (Email ile doğrulama)
- ✅ CSRF koruması
- ✅ SQL injection koruması

### 📧 E-posta Sistemi
- ✅ Gmail SMTP entegrasyonu
- ✅ Şifre sıfırlama için 6 haneli doğrulama kodu
- ✅ 15 dakikalık zamanaşımı kontrolü
- ✅ Profesyonel HTML email şablonları
- ✅ PHPMailer kullanımı

### 📱 Responsive Tasarım
- ✅ Mobil, tablet ve masaüstü uyumlu
- ✅ Modern ve kullanıcı dostu arayüz
- ✅ Bootstrap tabanlı tasarım



## 🛠 Teknoloji

### Backend
- **PHP 8.2+** - Server-side programlama dili
- **PDO (PHP Data Objects)** - Güvenli veritabanı bağlantısı
- **PHPMailer 7.0+** - E-posta gönderimi

### Veritabanı
- **MySQL** - İlişkisel veritabanı (production)
- **SQLite** - Hafif veritabanı (development/Replit)

### Frontend
- **HTML5** - Yapısal markup
- **CSS3** - Stil ve tasarım
- **JavaScript (Vanilla)** - İnteraktif özellikler
- **Bootstrap** - Responsive framework (opsiyonel)

### E-posta
- **Gmail SMTP** - E-posta gönderim servisi
- **PHPMailer** - E-posta kütüphanesi

### Güvenlik
- **Session Management** - Kullanıcı oturumları
- **SHA-512 + Salt** - Şifre hashleme
- **PDO Prepared Statements** - SQL injection koruması
- **CSRF Tokens** - Cross-site request forgery koruması




## 🚀 Kullanım

### İlk Giriş

Platform kurulumdan sonra aşağıdaki varsayılan kullanıcılarla giriş yapabilirsiniz:

| Rol | E-posta | Şifre |
|-----|---------|-------|
| Admin | admin@maku.edu.tr | 123456 |
| Öğretmen | ogretmen@maku.edu.tr | 123456 |
| Öğrenci | ogrenci@maku.edu.tr | 123456 |


### Yeni Kullanıcı Kaydı

1. Ana sayfada "Kayıt Ol" butonuna tıklayın
2. Gerekli bilgileri doldurun (Ad, Soyad, E-posta, Şifre)
3. Kullanıcı rolü seçin (Öğrenci/Öğretmen)
4. "Kayıt Ol" butonuna tıklayın

### Şifre Sıfırlama

1. Giriş sayfasında "Şifremi Unuttum" linkine tıklayın
2. Kayıtlı e-posta adresinizi girin
3. E-postanıza gelen 6 haneli doğrulama kodunu girin
4. Yeni şifrenizi belirleyin
5. Giriş yapın

> 📧 Doğrulama kodu 15 dakika geçerlidir.

### Kurs Oluşturma (Öğretmen)

1. Öğretmen paneline giriş yapın
2. "Kurs Ekle" butonuna tıklayın
3. Kurs bilgilerini doldurun (Başlık, Açıklama, İçerik)
4. Kaydet

### Quiz Oluşturma (Öğretmen/Admin)

1. "Quiz Ekle" bölümüne gidin
2. Quiz başlığını ve kursunu seçin
3. Soruları ekleyin (Soru metni, Seçenekler, Doğru cevap)
4. Kaydet

### Quiz Çözme (Öğrenci)

1. Öğrenci paneline giriş yapın
2. "Kurslarım" bölümünden kursu seçin
3. İlgili quiz'e tıklayın
4. Soruları cevaplayın
5. "Quiz'i Bitir" butonuna tıklayın
6. Sonuçlarınızı görüntüleyin

---

## 👥 Kullanıcı Rolleri

### 🔴 Admin (Yönetici)
**Yetkiler:**
- Tüm kullanıcıları görüntüleme, ekleme, düzenleme ve silme
- Tüm kursları görüntüleme ve yönetme
- Tüm quiz'leri görüntüleme ve yönetme
- Sistem genelinde istatistikleri görüntüleme
- Duyuru oluşturma ve yönetme
- Kullanıcı rollerini değiştirme

**Dashboard Özellikleri:**
- Toplam kullanıcı, kurs ve quiz sayıları
- Sistem genelinde istatistikler
- Kullanıcı yönetim paneli
- Kurs ve quiz yönetim paneli

### 🟡 Öğretmen (Teacher)
**Yetkiler:**
- Kendi kurslarını oluşturma, düzenleme ve silme
- Kurslar için quiz oluşturma ve yönetme
- Öğrenci performansını görüntüleme
- Kurs içeriklerini yönetme

**Dashboard Özellikleri:**
- Oluşturulan kurs sayısı
- Oluşturulan quiz sayısı
- Kurs başına öğrenci sayıları
- Quiz istatistikleri ve sonuçları

### 🟢 Öğrenci (Student)
**Yetkiler:**
- Kurslara katılma
- Quiz'lere katılma
- Kendi geçmişini görüntüleme
- İlerleme takibi

**Dashboard Özellikleri:**
- Katıldığı kurs sayısı
- Çözülen quiz sayısı
- Ortalama başarı oranı
- Quiz geçmişi ve detaylı sonuçlar

---

## 📁 Proje Yapısı

```
maku-egitim-platformu/
│
├── css/                          # Stil dosyaları
│   └── main.css                  # Ana CSS dosyası
│
├── js/                           # JavaScript dosyaları
│   ├── main.js                   # Ana JS dosyası
│   └── stats.js                  # İstatistik grafikleri
│
├── img/                          # Görseller
│   ├── header-logo.jpg           # Header logosu
│   └── school-logo.jpg           # Okul logosu
│
├── vendor/                       # Composer bağımlılıkları
│   └── phpmailer/                # PHPMailer kütüphanesi
│
├── islem/                        # İşlem dosyaları
│
├── config.php                    # Veritabanı ve güvenlik yapılandırması
├── mailer.php                    # E-posta gönderim fonksiyonları
│
├── index.php                     # Ana sayfa
├── login.php                     # Giriş sayfası
├── register.php                  # Kayıt sayfası
├── forgot_password.php           # Şifre sıfırlama sayfası
│
├── admin_dashboard.php           # Admin paneli
├── admin_manage_users.php        # Kullanıcı yönetimi
├── admin_manage_courses.php      # Kurs yönetimi (Admin)
├── admin_manage_quizzes.php      # Quiz yönetimi (Admin)
├── admin_view_quiz.php           # Quiz görüntüleme (Admin)
├── admin_quiz_stats.php          # Quiz istatistikleri (Admin)
│
├── teacher_dashboard.php         # Öğretmen paneli
├── teacher_add_course.php        # Kurs ekleme (Öğretmen)
├── teacher_manage_courses.php    # Kurs yönetimi (Öğretmen)
│
├── student_dashboard.php         # Öğrenci paneli
├── student_courses.php           # Öğrenci kursları
├── student_quiz_history.php      # Quiz geçmişi
├── student_quiz_stats.php        # Quiz istatistikleri
│
├── add_quiz.php                  # Quiz ekleme
├── edit_quiz.php                 # Quiz düzenleme
├── delete_quiz.php               # Quiz silme
├── take_quiz.php                 # Quiz çözme
│
├── announcements.php             # Duyurular
├── contact.php                   # İletişim sayfası
│
├── setup_database.php            # Veritabanı kurulum scripti
├── okul_mysql.sql                # MySQL veritabanı dump
│
├── composer.json                 # PHP bağımlılıkları
├── composer.lock                 # Bağımlılık kilit dosyası
│
└── README.md                     # Bu dosya
```

### Önemli Dosyalar

| Dosya | Açıklama |
|-------|----------|
| `config.php` | Veritabanı bağlantısı ve güvenlik fonksiyonları |
| `mailer.php` | E-posta gönderimi ve doğrulama kodu oluşturma |
| `setup_database.php` | Otomatik veritabanı kurulumu |
| `forgot_password.php` | 3 adımlı şifre sıfırlama süreci |


## 📞 İletişim

Proje ile ilgili sorularınız için:

- **Proje Sahibi**: [GitHub Profili](https://github.com/ayazserhatt)
- **Web Sitesi**: [maku.wuaze.com](https://maku.wuaze.com)



Bu projeyi mümkün kılan teknolojiler ve topluluklar:

- [PHP](https://php.net) - Server-side scripting dili
- [PHPMailer](https://github.com/PHPMailer/PHPMailer) - E-posta gönderimi
- [MySQL](https://mysql.com) - Veritabanı yönetim sistemi
- [Bootstrap](https://getbootstrap.com) - Frontend framework



