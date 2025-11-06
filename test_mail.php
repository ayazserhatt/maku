<?php
include "mailer.php";

if (send_reset_code("seninmailadresin@gmail.com", "Test Kullanıcı", "123456")) {
    echo "✅ E-posta başarıyla gönderildi!";
} else {
    echo "❌ E-posta gönderimi başarısız. Ayrıntı için error_log'a bak.";
}