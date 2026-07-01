<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Masuk — {{ config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-zinc-50 text-zinc-900">
        <div class="min-h-screen flex flex-col items-center justify-center px-4">
            <span class="text-base font-semibold tracking-tight text-zinc-900 mb-6">{{ config('app.name') }}</span>

            <div class="w-full sm:max-w-sm bg-white border border-zinc-200 rounded-lg p-6 sm:p-8">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
