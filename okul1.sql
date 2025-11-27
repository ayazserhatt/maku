-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 27 Kas 2025, 13:00:28
-- Sunucu sürümü: 10.4.32-MariaDB
-- PHP Sürümü: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `okul1`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `courses`
--

INSERT INTO `courses` (`id`, `name`, `description`, `teacher_id`, `created_at`, `updated_at`) VALUES
(1, 'Web Tasarım', 'HTML, CSS ve JavaScript kullanarak modern web siteleri öğrenin.', 2, '2024-05-23 08:15:00', '2025-05-16 08:47:41'),
(2, 'Veri Tabanı Yönetimi', 'SQL, veritabanı tasarımı ve veritabanı yönetim sistemleri hakkında kapsamlı bir kurs.', 2, '2024-05-23 08:16:00', '2025-05-16 02:52:07'),
(3, 'Mobil Uygulama Geliştirme', 'Android ve iOS için mobil uygulama geliştirme temelleri.', 2, '2024-05-23 08:17:00', '2025-05-16 02:52:07'),
(8, 'test ders', 'test dersidir', 8, '2025-05-16 08:50:15', '2025-05-16 08:50:15'),
(9, 'Test Ders', 'Test Açıklama', 10, '2025-10-16 10:42:56', '2025-10-16 10:42:56');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `course_content`
--

CREATE TABLE `course_content` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `course_content`
--

INSERT INTO `course_content` (`id`, `course_id`, `title`, `content`, `created_at`, `updated_at`) VALUES
(1, 1, 'HTML Temelleri', '<h3>HTML Nedir?</h3><p>HTML (Hypertext Markup Language), web sayfalarının yapısını oluşturmak için kullanılan standart işaretleme dilidir.</p><h3>Temel HTML Etiketleri</h3><ul><li>&lt;html&gt; - HTML belgesinin kök öğesidir</li><li>&lt;head&gt; - Meta bilgiler, başlık, stil ve bağlantılar içerir</li><li>&lt;title&gt; - Belgenin başlığını belirtir</li><li>&lt;body&gt; - Belgenin görünür içeriğini içerir</li><li>&lt;h1&gt; - &lt;h6&gt; - Başlık etiketleri</li><li>&lt;p&gt; - Paragraf etiketi</li><li>&lt;a&gt; - Bağlantı etiketi</li><li>&lt;img&gt; - Resim etiketi</li></ul>', '2024-05-23 08:30:00', '2025-05-16 02:52:07'),
(2, 1, 'CSS Temelleri', '<h3>CSS Nedir?</h3><p>CSS (Cascading Style Sheets), web sayfalarının görünümünü ve düzenini kontrol etmek için kullanılan bir stil dilidir.</p><h3>CSS Seçiciler</h3><ul><li>Etiket seçiciler: p { color: red; }</li><li>Sınıf seçiciler: .class-name { font-size: 16px; }</li><li>ID seçiciler: #id-name { margin: 20px; }</li></ul><h3>CSS Kutusu Modeli</h3><p>Her HTML öğesi bir kutu olarak düşünülebilir. CSS kutu modeli, içerik alanı, dolgu, kenar ve kenar boşluğu alanlarını içerir.</p>', '2024-05-23 08:31:00', '2025-05-16 02:52:07'),
(3, 2, 'SQL Temelleri', '<h3>SQL Nedir?</h3><p>SQL (Structured Query Language), veritabanlarıyla etkileşim kurmak için kullanılan bir programlama dilidir.</p><h3>Temel SQL Komutları</h3><ul><li>SELECT - veritabanından veri seçer</li><li>INSERT INTO - veritabanına yeni veri ekler</li><li>UPDATE - veritabanındaki verileri günceller</li><li>DELETE - veritabanından veri siler</li><li>CREATE DATABASE - yeni bir veritabanı oluşturur</li><li>CREATE TABLE - yeni bir tablo oluşturur</li><li>ALTER TABLE - bir tabloyu değiştirir</li><li>DROP TABLE - bir tabloyu siler</li></ul>', '2024-05-23 08:32:00', '2025-05-16 02:52:07'),
(4, 9, 'Test Başlık', 'Test İçerik', '2025-10-16 10:44:57', '2025-10-16 10:44:57');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email` varchar(150) NOT NULL,
  `reset_code` varchar(6) DEFAULT NULL,
  `used` tinyint(1) DEFAULT 0,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`id`, `user_id`, `email`, `reset_code`, `used`, `expires_at`, `created_at`) VALUES
(1, 11, 'acabbarrr123@gmail.com', '900492', 0, '2025-10-28 09:55:35', '2025-10-28 08:40:35'),
(2, 11, 'acabbarrr123@gmail.com', '587650', 0, '2025-10-28 19:25:50', '2025-10-28 18:10:50'),
(5, 11, 'acabbarrr123@gmail.com', '463913', 0, '2025-10-28 19:48:45', '2025-10-28 18:33:45'),
(6, 11, 'acabbarrr123@gmail.com', '171882', 0, '2025-10-28 20:04:27', '2025-10-28 18:49:27'),
(7, 11, 'acabbarrr123@gmail.com', '291286', 0, '2025-10-28 22:23:55', '2025-10-28 19:08:55'),
(8, 11, 'acabbarrr123@gmail.com', '169671', 0, '2025-10-28 22:24:50', '2025-10-28 19:09:50'),
(9, 11, 'acabbarrr123@gmail.com', '304776', 1, '2025-11-04 11:56:40', '2025-11-04 08:41:40'),
(10, 11, 'acabbarrr123@gmail.com', '579071', 0, '2025-11-04 11:57:33', '2025-11-04 08:42:33'),
(11, 11, 'acabbarrr123@gmail.com', '323209', 1, '2025-11-05 17:39:19', '2025-11-05 14:24:19'),
(12, 11, 'acabbarrr123@gmail.com', '394069', 1, '2025-11-05 17:41:44', '2025-11-05 14:26:44'),
(13, 11, 'acabbarrr123@gmail.com', '192018', 1, '2025-11-05 17:42:38', '2025-11-05 14:27:38');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `quizzes`
--

CREATE TABLE `quizzes` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `question` text NOT NULL,
  `option_a` varchar(255) NOT NULL,
  `option_b` varchar(255) NOT NULL,
  `option_c` varchar(255) NOT NULL,
  `option_d` varchar(255) NOT NULL,
  `correct_option` enum('A','B','C','D') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `quizzes`
--

INSERT INTO `quizzes` (`id`, `course_id`, `question`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_option`, `created_at`, `updated_at`) VALUES
(2, 1, 'Aşağıdakilerden hangisi CSS\'de bir seçici türü değildir?', 'ID seçici', 'Sınıf seçici', 'Element seçici', 'Function seçici', 'D', '2024-05-23 08:46:00', '2025-05-16 02:52:07'),
(3, 2, 'SQL\'de bir tablodaki tüm kayıtları seçmek için hangi komut kullanılır?', 'SELECT ALL FROM table', 'SELECT * FROM table', 'GET * FROM table', 'RETRIEVE ALL FROM table', 'B', '2024-05-23 08:47:00', '2025-05-16 02:52:07'),
(4, 2, 'Bir tabloya yeni bir kayıt eklemek için hangi SQL komutu kullanılır?', 'ADD', 'INSERT', 'UPDATE', 'CREATE', 'B', '2024-05-23 08:48:00', '2025-05-16 02:52:07'),
(5, 3, 'Android uygulamaları hangi programlama dilinde yazılır?', 'Swift', 'Objective-C', 'Java veya Kotlin', 'C#', 'C', '2024-05-23 08:49:00', '2025-05-16 02:52:07'),
(9, 9, 'Test Soru', 'A', 'B', 'C', 'D', 'B', '2025-10-16 10:43:24', '2025-10-16 10:43:24');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `quiz_results`
--

CREATE TABLE `quiz_results` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `user_answer` enum('A','B','C','D') NOT NULL,
  `is_correct` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `quiz_results`
--

INSERT INTO `quiz_results` (`id`, `user_id`, `quiz_id`, `user_answer`, `is_correct`, `created_at`) VALUES
(2, 3, 2, 'C', 0, '2024-05-23 09:01:00'),
(3, 3, 3, 'B', 1, '2024-05-23 09:02:00'),
(8, 9, 5, 'C', 1, '2025-06-15 13:42:50'),
(9, 9, 2, 'C', 0, '2025-10-16 07:19:45'),
(10, 9, 2, 'D', 1, '2025-10-16 07:19:53'),
(11, 9, 9, 'B', 1, '2025-10-16 10:45:38'),
(12, 9, 5, 'A', 0, '2025-10-22 17:13:01'),
(13, 11, 5, 'C', 1, '2025-11-05 14:30:00');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `salt` varchar(32) DEFAULT NULL,
  `role` enum('student','teacher','admin') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `salt`, `role`, `created_at`, `updated_at`) VALUES
(2, 'Örnek Öğretmen', 'ogretmen@maku.edu.tr', '3c9909afec25354d551dae21590bb26e38d53f2173b8d3dc3eee4c047e7ab1c1eb8b85103e3be7ba613b31bb5c9c36214dc9f14a42fd7a2fdb84856bca5c44c2', '123456789abcdef', 'teacher', '2024-05-23 08:00:00', '2025-05-16 02:52:07'),
(3, 'Örnek Öğrenci', 'ogrenci@maku.edu.tr', '3c9909afec25354d551dae21590bb26e38d53f2173b8d3dc3eee4c047e7ab1c1eb8b85103e3be7ba613b31bb5c9c36214dc9f14a42fd7a2fdb84856bca5c44c2', '123456789abcdef', 'student', '2024-05-23 08:00:00', '2025-05-16 02:52:07'),
(6, 'serhat ayaz', 'serhat@gmail.com', '74d90b52656d71701f7a08f26bd02ab4fb06509b6109e73d904680fec2340086f19d921aacec86f7050116f9a8c38d5acdfc78b17be529580ddea32f0b16f51a', 'd004d547d97b6dd15b1011138a603449', 'admin', '2025-05-16 03:53:23', '2025-05-16 03:53:23'),
(8, 'test', 'test@mail.com', '50c7729d8bc6684e6c9c768ced9044a9176a2f6476e7c24f003a7b4f3e78542e044ef74b8987089be918cf290487624de3ed1ca06cbe8f359da003ce537c52c9', '55c9f847dc64ef292b0b016f87556bac', 'teacher', '2025-05-16 08:49:07', '2025-10-16 11:28:34'),
(9, 'asdasd', 'aaa@gmail.com', '74fcd707c1958510095f08f695d7906328eb7a9f828759a6ba11eb718343a9d17dd2d4b18e909d458d8c69af2e607bbae63a82c22607c49144f646990573b260', 'c8088e390811fd50a0907d791838cf0f', 'student', '2025-06-15 13:42:10', '2025-06-15 13:42:10'),
(10, 'ayaz', 'uye1@gmail.com', '7581f72e25d827f044720a0d226aa3e79a1ef068187464d2cb4495825c8c47d4745884d4956abb9eccfe63acb0540af1b019949243b8a74a5eb643ee4ef6a939', 'e5e88bffe9905a6b460979a5b23c5e51', 'teacher', '2025-06-15 13:43:18', '2025-06-15 13:43:18'),
(11, 'test öğrenci', 'acabbarrr123@gmail.com', '2abffe8156aa7c2a394a78fb3b15b004388860da3657690f5de604ccdaff7fa92af213c772d49c1a72ea833eea7aa83d582cf03e9e75c0fef8d4bc955ed4cd75', '8d6d56e6f9619a55f9a6df370a924a14', 'student', '2025-10-28 08:06:04', '2025-11-05 14:29:23');

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Tablo için indeksler `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Tablo için indeksler `course_content`
--
ALTER TABLE `course_content`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Tablo için indeksler `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Tablo için indeksler `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Tablo için indeksler `quiz_results`
--
ALTER TABLE `quiz_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `quiz_id` (`quiz_id`);

--
-- Tablo için indeksler `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Tablo için AUTO_INCREMENT değeri `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Tablo için AUTO_INCREMENT değeri `course_content`
--
ALTER TABLE `course_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Tablo için AUTO_INCREMENT değeri `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Tablo için AUTO_INCREMENT değeri `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Tablo için AUTO_INCREMENT değeri `quiz_results`
--
ALTER TABLE `quiz_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Tablo için AUTO_INCREMENT değeri `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Tablo kısıtlamaları `course_content`
--
ALTER TABLE `course_content`
  ADD CONSTRAINT `course_content_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD CONSTRAINT `password_reset_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `quizzes`
--
ALTER TABLE `quizzes`
  ADD CONSTRAINT `quizzes_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `quiz_results`
--
ALTER TABLE `quiz_results`
  ADD CONSTRAINT `quiz_results_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_results_ibfk_2` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
