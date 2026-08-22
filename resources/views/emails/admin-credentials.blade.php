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
            @if ($isReset)
              <h1 style="margin:0 0 10px;font-size:20px;color:#1F2432;">পাসওয়ার্ড রিসেট হয়েছে</h1>
              <p style="margin:0 0 20px;font-size:14.5px;line-height:1.8;color:#6B7280;">
                <strong style="color:#1F2432;">{{ $institution->name }}</strong>-এর এডমিন অ্যাকাউন্টের জন্য একটা নতুন সাময়িক পাসওয়ার্ড তৈরি করা হয়েছে।
              </p>
            @else
              <h1 style="margin:0 0 10px;font-size:20px;color:#1F2432;">🎉 আপনার প্রতিষ্ঠান অনুমোদিত হয়েছে!</h1>
              <p style="margin:0 0 20px;font-size:14.5px;line-height:1.8;color:#6B7280;">
                অভিনন্দন! <strong style="color:#1F2432;">{{ $institution->name }}</strong>-এর জন্য EDUTION অ্যাকাউন্ট চালু হয়ে গেছে। নিচের তথ্য দিয়ে লগইন করুন।
              </p>
            @endif

            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F7F8FC;border-radius:12px;margin-bottom:22px;">
              <tr><td style="padding:18px 20px;font-size:14px;color:#1F2432;">
                <div style="margin-bottom:10px;"><span style="color:#6B7280;">ওয়েব ঠিকানা:</span><br><strong>{{ $institution->slug }}.edution.xyz</strong></div>
                <div style="margin-bottom:10px;"><span style="color:#6B7280;">ইমেইল:</span><br><strong>{{ $loginEmail }}</strong></div>
                <div>
                  <span style="color:#6B7280;">সাময়িক পাসওয়ার্ড:</span><br>
                  <span style="display:inline-block;margin-top:4px;padding:8px 14px;background:#FFFFFF;border:1.5px dashed #F59E0B;border-radius:8px;font-size:17px;font-weight:700;letter-spacing:2px;color:#6C5CE7;">{{ $password }}</span>
                </div>
              </tr></td>
            </table>

            <a href="https://{{ $institution->slug }}.edution.xyz/login" style="display:block;text-align:center;background:linear-gradient(90deg,#FBBF24,#F59E0B);color:#1F2432;text-decoration:none;font-weight:700;font-size:14.5px;padding:13px;border-radius:10px;margin-bottom:18px;">
              এখনই লগইন করুন →
            </a>

            <p style="margin:0;font-size:13px;line-height:1.8;color:#9CA3AF;">
              নিরাপত্তার জন্য প্রথমবার লগইন করার সাথে সাথেই নতুন পাসওয়ার্ড সেট করতে বলা হবে। এই পাসওয়ার্ড কারো সাথে শেয়ার করবেন না।
            </p>
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
