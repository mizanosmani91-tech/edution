<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="margin:0;padding:0;background:#E5DCC5;font-family:'Hind Siliguri','Noto Sans Bengali',Arial,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#E5DCC5;padding:32px 16px;">
  <tr>
    <td align="center">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;background:#FFFFFF;border-radius:18px;overflow:hidden;box-shadow:0 20px 40px -20px rgba(31,36,50,.3);">
        <tr>
          <td style="background:linear-gradient(135deg,#8B7CF6,#6C5CE7 45%,#4B3FC4);padding:32px 32px 28px;text-align:center;">
            <div style="width:52px;height:52px;margin:0 auto 14px;border-radius:50%;background:rgba(251,191,36,.12);border:1.5px solid rgba(251,191,36,.5);line-height:52px;color:#FBBF24;font-size:22px;font-weight:700;">E</div>
            <div style="color:#F3E9D2;font-size:20px;font-weight:700;letter-spacing:.5px;">EDUTION</div>
          </td>
        </tr>
        <tr>
          <td style="padding:32px;">
            <h1 style="margin:0 0 10px;font-size:20px;color:#1F2432;">{{ $alertTitle }}</h1>
            <p style="margin:0 0 20px;font-size:14.5px;line-height:1.8;color:#6B7280;">
              <strong style="color:#1F2432;">{{ $institution->name }}</strong> — {{ $alertBody }}
            </p>

            <a href="https://{{ $institution->slug }}.edution.xyz/login" style="display:block;text-align:center;background:linear-gradient(90deg,#FBBF24,#F59E0B);color:#1F2432;text-decoration:none;font-weight:700;font-size:14.5px;padding:13px;border-radius:10px;margin-bottom:6px;">
              লগইন করে দেখুন →
            </a>
          </td>
        </tr>
        <tr>
          <td style="padding:18px 32px;background:#F7F8FC;text-align:center;font-size:11.5px;color:#9CA3AF;">
            এটা একটা স্বয়ংক্রিয় ইমেইল, দয়া করে সরাসরি রিপ্লাই করবেন না।
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</body>
</html>
