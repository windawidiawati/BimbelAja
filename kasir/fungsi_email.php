<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php'; // Sesuaikan path jika perlu

function kirimEmailSiswa($toEmail, $namaSiswa, $username, $password_plain) {
    $mail = new PHPMailer(true);
    try {
        // Konfigurasi SMTP Gmail
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'bimbelaja29@gmail.com'; // Ganti emailmu
        $mail->Password   = 'jcdg ktoc ejgw qgir';  // Ganti app password
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Pengirim & Penerima
        $mail->setFrom('bimbelaja29@gmail.com', 'Admin Bimbel');
        $mail->addAddress($toEmail, $namaSiswa);

        // Konten email
        $mail->isHTML(true);
        $mail->Subject = "Akun dan Password Siswa";
        $mail->Body    = "Halo <b>$namaSiswa</b>,<br>
                          Akun kamu telah dibuat.<br>
                          Username: <b>$username</b><br>
                          Password: <b>$password_plain</b><br>
                          Silakan login di sistem BimbelAja.";
        $mail->AltBody = "Halo $namaSiswa, Akun kamu telah dibuat. Username: $username, Password: $password_plain";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return "Email gagal dikirim. Error: " . $mail->ErrorInfo;
    }
}
?>
