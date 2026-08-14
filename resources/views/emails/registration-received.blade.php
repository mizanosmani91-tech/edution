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
            <h1 style="margin:0 0 10px;font-size:20px;color:#2A2320;">আবেদন গ্রহণ করা হয়েছে</h1>
            <p style="margin:0 0 20px;font-size:14.5px;line-height:1.8;color:#7A7061;">
              প্রিয় {{ $institution->admin_name }},<br><br>
              <strong style="color:#2A2320;">{{ $institution->name }}</strong>-এর জন্য আপনার EDUTION রেজিস্ট্রেশন সফলভাবে জমা হয়েছে। আমাদের টিম শীঘ্রই আপনার আবেদন পর্যালোচনা করবে।
            </p>

            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F7F2E5;border-radius:12px;margin-bottom:20px;">
              <tr><td style="padding:16px 18px;font-size:13.5px;color:#2A2320;">
                <div style="margin-bottom:8px;"><span style="color:#7A7061;">প্রতিষ্ঠানের নাম:</span> <strong>{{ $institution->name }}</strong></div>
                <div style="margin-bottom:8px;"><span style="color:#7A7061;">প্যাকেজ:</span> <strong>{{ ['basic'=>'বেসিক','standard'=>'স্ট্যান্ডার্ড','premium'=>'প্রিমিয়াম'][$institution->plan] ?? $institution->plan }}</strong></div>
                <div><span style="color:#7A7061;">প্রত্যাশিত ওয়েব ঠিকানা:</span> <strong>{{ $institution->slug }}.edution.xyz</strong></div>
              </tr></td>
            </table>

            <p style="margin:0 0 4px;font-size:14px;line-height:1.8;color:#7A7061;">
              অনুমোদন হয়ে গেলে আপনার নিবন্ধিত ইমেইল ও মোবাইল নম্বরে লগইন তথ্য (সাময়িক পাসওয়ার্ড) পাঠানো হবে — সাধারণত এতে ২৪ ঘণ্টার কম সময় লাগে।
            </p>
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
