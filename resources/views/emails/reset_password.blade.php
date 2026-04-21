<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f9fafb; padding: 20px;">
    <div style="max-w: 600px; margin: 0 auto; background-color: #ffffff; padding: 40px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <h2 style="color: #901C31; margin-bottom: 20px;">Halo, {{ $user->name ?? 'Pengguna' }}</h2>
        
        <p style="color: #4b5563; line-height: 1.6; margin-bottom: 20px;">
            Kami menerima permintaan untuk melakukan reset password pada akun Anda. 
            Jika Anda memang merasa meminta perubahan password, silakan klik tombol di bawah ini untuk mengatur ulang password Anda.
        </p>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $url }}" style="background-color: #901C31; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;">
                Reset Password Sekarang
            </a>
        </div>

        <p style="color: #4b5563; line-height: 1.6; margin-bottom: 20px;">
            Tautan reset password ini akan kedaluwarsa dalam 60 menit.
        </p>

        <p style="color: #6b7280; font-size: 14px; line-height: 1.6;">
            Jika Anda tidak meminta perubahan password, Anda tidak perlu melakukan tindakan apapun. Akun Anda tetap aman bersama kami.
        </p>

        <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 30px 0;" />

        <p style="color: #9ca3af; font-size: 12px; text-align: center;">
            Jika Anda mengalami kendala saat menekan tombol, Anda bisa menyalin dan membuka tautan berikut di browser Anda:<br/>
            <a href="{{ $url }}" style="color: #901C31; word-break: break-all;">{{ $url }}</a>
        </p>
    </div>
</body>
</html>
