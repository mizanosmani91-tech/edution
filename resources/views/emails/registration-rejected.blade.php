<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="margin:0;padding:0;background:#F3F4F6;font-family:'Hind Siliguri','Noto Sans Bengali',Arial,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:32px 16px;">
  <tr>
    <td align="center">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;background:#FFFFFF;border-radius:18px;overflow:hidden;">
        <tr>
          <td style="background:#0D9488;padding:26px 32px;text-align:center;">
            <div style="color:#FFFFFF;font-size:20px;font-weight:700;letter-spacing:.5px;">EDUTION</div>
          </td>
        </tr>
        <tr>
          <td style="padding:32px;">
            <h1 style="margin:0 0 10px;font-size:20px;color:#1F2432;">আপনার আবেদন গ্রহণ করা সম্ভব হয়নি</h1>
            <p style="margin:0 0 16px;font-size:14.5px;line-height:1.8;color:#6B7280;">
              প্রিয় {{ $institution->admin_name }},<br><br>
              <strong style="color:#1F2432;">{{ $institution->name }}</strong>-এর জন্য আপনার EDUTION রেজিস্ট্রেশনের আবেদনটি এই মুহূর্তে অনুমোদন করা যায়নি।
            </p>
            <p style="margin:0;font-size:14px;line-height:1.8;color:#6B7280;">
              বিস্তারিত জানতে বা আবেদন নিয়ে আলোচনা করতে EDUTION দলের সাথে যোগাযোগ করুন।
            </p>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</body>
</html>
