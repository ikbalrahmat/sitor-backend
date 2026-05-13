<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reset Password</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        .header { text-align: center; margin-bottom: 20px; }
        .button { display: inline-block; padding: 10px 20px; background-color: #0b3c5d; color: #ffffff; text-decoration: none; border-radius: 5px; font-weight: bold; }
        .footer { margin-top: 30px; font-size: 12px; color: #777; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Pemulihan Akses Akun Si-Tor</h2>
        </div>
        <p>Halo,</p>
        <p>Anda menerima email ini karena kami menerima permintaan reset password untuk akun Anda di Sistem Kompetensi Auditor (Si-Tor).</p>
        <p style="text-align: center; margin: 30px 0;">
            <a href="{{ $resetUrl }}" class="button" style="color: white;">Reset Password Sekarang</a>
        </p>
        <p>Link reset password ini akan kadaluarsa dalam waktu 60 menit.</p>
        <p>Jika Anda tidak pernah meminta reset password, Anda dapat mengabaikan email ini.</p>
        <div class="footer">
            <p>Email ini dihasilkan secara otomatis, mohon tidak membalas ke alamat email ini.</p>
            <p>&copy; {{ date('Y') }} Si-Tor. Sistem Kompetensi Auditor.</p>
        </div>
    </div>
</body>
</html>
