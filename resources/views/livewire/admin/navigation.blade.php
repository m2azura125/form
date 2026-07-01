<nav x-data="{ open: false }" class="bg-white border-b border-zinc-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-14 items-center">
            <div class="flex items-center gap-8">
                <a href="{{ route('admin.dashboard') }}" wire:navigate class="text-sm font-semibold tracking-tight text-zinc-900">
                    {{ config('app.name') }}
                </a>

                <div class="hidden sm:flex items-center gap-1">
                    <a
                        href="{{ route('admin.dashboard') }}"
                        wire:navigate
                        class="px-3 py-1.5 rounded-md text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'text-indigo-600 bg-indigo-50' : 'text-zinc-600 hover:text-zinc-900 hover:bg-zinc-50' }}"
                    >
                        Dashboard
                    </a>
                </div>
            </div>

            <div class="hidden sm:flex items-center gap-4">
                <span class="text-sm text-zinc-500">{{ auth()->user()->name }}</span>
                <button wire:click="logout" class="text-sm font-medium text-zinc-600 hover:text-zinc-900">
                    Keluar
                </button>
            </div>

            <button @click="open = ! open" class="sm:hidden p-2 text-zinc-500 hover:text-zinc-700">
                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                    <path :class="{ hidden: open }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    <path :class="{ hidden: ! open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div x-show="open" x-cloak class="sm:hidden pb-3 space-y-1">
            <a href="{{ route('admin.dashboard') }}" wire:navigate class="block px-3 py-2 rounded-md text-sm font-medium text-zinc-700 hover:bg-zinc-50">Dashboard</a>
            <button wire:click="logout" class="block w-full text-left px-3 py-2 rounded-md text-sm font-medium text-zinc-700 hover:bg-zinc-50">Keluar</button>
        </div>
    </div>
</nav>
