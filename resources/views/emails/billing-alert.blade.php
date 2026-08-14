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
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;background:#FFFFFF;border-radius:18px;overflow:hidden;box-shadow:0 20px 40px -20px rgba(60,30,20,.3);">
        <tr>
          <td style="background:linear-gradient(135deg,#6E2136,#5C1A2B 45%,#3E1120);padding:32px 32px 28px;text-align:center;">
            <div style="width:52px;height:52px;margin:0 auto 14px;border-radius:50%;background:rgba(231,199,103,.12);border:1.5px solid rgba(231,199,103,.5);line-height:52px;color:#E7C767;font-size:22px;font-weight:700;">E</div>
            <div style="color:#F3E9D2;font-size:20px;font-weight:700;letter-spacing:.5px;">EDUTION</div>
          </td>
        </tr>
        <tr>
          <td style="padding:32px;">
            <h1 style="margin:0 0 10px;font-size:20px;color:#2A2320;">{{ $alertTitle }}</h1>
            <p style="margin:0 0 20px;font-size:14.5px;line-height:1.8;color:#7A7061;">
              <strong style="color:#2A2320;">{{ $institution->name }}</strong> — {{ $alertBody }}
            </p>

            <a href="https://{{ $institution->slug }}.edution.xyz/login" style="display:block;text-align:center;background:linear-gradient(90deg,#E7C767,#C9A227);color:#2A2320;text-decoration:none;font-weight:700;font-size:14.5px;padding:13px;border-radius:10px;margin-bottom:6px;">
              লগইন করে দেখুন →
            </a>
          </td>
        </tr>
        <tr>
          <td style="padding:18px 32px;background:#F7F2E5;text-align:center;font-size:11.5px;color:#AFA593;">
            এটা একটা স্বয়ংক্রিয় ইমেইল, দয়া করে সরাসরি রিপ্লাই করবেন না।
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</body>
</html>
