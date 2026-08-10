<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Superadmin — Edution</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-50">
    <header class="border-b border-gray-200 bg-white p-4">
        <p class="text-lg font-bold text-gray-900">Edution Superadmin</p>
    </header>
    <main>{{ $slot }}</main>
    @livewireScripts
</body>
</html>
