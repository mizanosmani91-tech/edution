<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>সেবা সাময়িকভাবে বন্ধ</title>
    <style>
        body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;background:#f6f7f9;color:#1f2933;font-family:system-ui,-apple-system,"Segoe UI",sans-serif;}
        .box{max-width:460px;margin:24px;padding:32px 28px;background:#fff;border-radius:16px;box-shadow:0 10px 30px rgba(0,0,0,.08);text-align:center;}
        h1{font-size:20px;margin:0 0 10px;}
        p{margin:0 0 14px;line-height:1.6;color:#52606d;}
        button{padding:10px 18px;border:0;border-radius:10px;background:#0D9488;color:#fff;font-size:15px;cursor:pointer;}
    </style>
</head>
<body>
    <div class="box">
        <h1>সেবা সাময়িকভাবে বন্ধ আছে</h1>
        <p>{{ $institution->name ?? 'এই প্রতিষ্ঠানের' }} অ্যাকাউন্ট এই মুহূর্তে স্থগিত। প্রতিষ্ঠানের কর্তৃপক্ষের সাথে যোগাযোগ করুন।</p>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">লগআউট</button>
        </form>
    </div>
</body>
</html>
