<?php
// Gọi các file cần thiết từ thư mục PHPMailer bạn vừa copy vào
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function gui_email_xac_nhan($email_khach, $ten_khach, $ma_don_hang, $noi_dung_html) {
    $mail = new PHPMailer(true);
    $mail->SMTPDebug = 0;    
    try {
        // Cấu hình SMTP (Dùng Gmail)
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'hoangttt333@gmail.com'; // Email của bạn
        $mail->Password = 'rpso pvuk swnz xsrq';               // Mật khẩu ứng dụng (App Password)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        $mail->CharSet = 'UTF-8';

        // Người gửi & nhận
        $mail->setFrom('dia-chi-email-cua-ban@gmail.com', 'TechStore');
        $mail->addAddress($email_khach, $ten_khach);

        // Nội dung
        $mail->isHTML(true);
        $mail->Subject = 'Xác nhận đơn hàng #' . $ma_don_hang;
        $mail->Body    = $noi_dung_html;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}