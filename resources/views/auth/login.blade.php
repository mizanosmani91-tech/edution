<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>লগইন</title>
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-screen items-center justify-center bg-gray-50 p-4">
    <form method="POST" action="{{ route('login') }}" class="w-full max-w-sm rounded-lg bg-white p-6 shadow">
        @csrf
        <h1 class="mb-4 text-lg font-semibold text-gray-900">লগইন করুন</h1>

        @if ($errors->any())
            <p class="mb-3 rounded-lg bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</p>
        @endif

        <label class="mb-1 block text-sm font-medium text-gray-700">ইমেইল</label>
        <input type="email" name="email" class="mb-3 w-full rounded-lg border border-gray-300 px-3 py-2.5 text-base" required>

        <label class="mb-1 block text-sm font-medium text-gray-700">পাসওয়ার্ড</label>
        <input type="password" name="password" class="mb-4 w-full rounded-lg border border-gray-300 px-3 py-2.5 text-base" required>

        <button type="submit" class="w-full rounded-lg bg-blue-600 py-2.5 font-medium text-white">প্রবেশ করুন</button>
    </form>
</body>
</html>
