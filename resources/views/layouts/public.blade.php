<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-zinc-50 text-zinc-900">
        <div class="min-h-screen flex flex-col">
            <header class="border-b border-zinc-200 bg-white">
                <div class="max-w-xl mx-auto px-4 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-8 w-8 items-center justify-center rounded-md bg-indigo-600 text-sm font-semibold text-white">
                            {{ Str::of(config('app.name'))->explode(' ')->map(fn ($w) => Str::substr($w, 0, 1))->take(2)->implode('') }}
                        </span>
                        <span class="text-base font-semibold tracking-tight text-zinc-900">{{ config('app.name') }}</span>
                    </div>

                    <a href="{{ route('login') }}" wire:navigate class="text-sm font-medium text-zinc-500 hover:text-indigo-600 transition">
                        Masuk Admin
                    </a>
                </div>
            </header>

            <main class="flex-1 px-4 py-10 sm:py-14">
                {{ $slot }}
            </main>

            <footer class="px-4 py-6 text-center text-xs text-zinc-400">
                &copy; {{ now()->year }} {{ config('app.name') }}
            </footer>
        </div>
    </body>
</html>
