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
                <div class="max-w-6xl mx-auto px-4 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2.5">
                        <span class="flex h-8 w-8 items-center justify-center rounded-md bg-indigo-600 text-sm font-semibold text-white">
                            {{ Str::of(config('app.name'))->explode(' ')->map(fn ($w) => Str::substr($w, 0, 1))->take(2)->implode('') }}
                        </span>
                        <span class="text-base font-semibold tracking-tight text-zinc-900">{{ config('app.name') }}</span>
                    </a>

                    <nav class="flex flex-wrap items-center gap-2">
                        <a
                            href="{{ route('home') }}"
                            wire:navigate
                            class="inline-flex items-center rounded-md px-3 py-1.5 text-sm font-medium {{ request()->routeIs('home') ? 'bg-indigo-600 text-white' : 'text-zinc-600 hover:bg-zinc-100' }}"
                        >
                            Pengajuan Form
                        </a>
                        <a
                            href="{{ route('antrian') }}"
                            wire:navigate
                            class="inline-flex items-center rounded-md px-3 py-1.5 text-sm font-medium {{ request()->routeIs('antrian') ? 'bg-indigo-600 text-white' : 'text-zinc-600 hover:bg-zinc-100' }}"
                        >
                            List Antrian
                        </a>
                        <a
                            href="{{ route('login') }}"
                            wire:navigate
                            class="inline-flex items-center rounded-md border border-zinc-300 px-3 py-1.5 text-sm font-medium text-zinc-700 hover:bg-zinc-50"
                        >
                            Login Admin
                        </a>
                    </nav>
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
