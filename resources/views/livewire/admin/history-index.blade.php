<div>
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900">History Perubahan</h1>
            <p class="mt-1 text-sm text-zinc-500">Riwayat aktivitas pencatatan, penentuan harga, penerimaan, dan perubahan data pengajuan.</p>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="mb-6 rounded-xl border border-zinc-200 bg-white p-4 sm:p-5 shadow-sm">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <div>
                <label class="block text-xs font-medium text-zinc-700 mb-1">Cari</label>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Pemohon, judul, pengubah..."
                    class="w-full rounded-lg border-zinc-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                />
            </div>

            <div>
                <label class="block text-xs font-medium text-zinc-700 mb-1">Diubah Oleh</label>
                <select
                    wire:model.live="userId"
                    class="w-full rounded-lg border-zinc-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                    <option value="">-- Semua User --</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->username }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-zinc-700 mb-1">Jenis Aksi</label>
                <select
                    wire:model.live="aksi"
                    class="w-full rounded-lg border-zinc-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                    <option value="">-- Semua Aksi --</option>
                    @foreach($aksiOptions as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-zinc-700 mb-1">Dari Tanggal</label>
                <input
                    type="date"
                    wire:model.live="tanggalDari"
                    class="w-full rounded-lg border-zinc-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                />
            </div>

            <div>
                <label class="block text-xs font-medium text-zinc-700 mb-1">Sampai Tanggal</label>
                <input
                    type="date"
                    wire:model.live="tanggalSampai"
                    class="w-full rounded-lg border-zinc-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                />
            </div>
        </div>

        @if($search !== '' || $aksi !== '' || $userId !== '' || $tanggalDari !== '' || $tanggalSampai !== '')
            <div class="mt-4 flex justify-end">
                <button
                    wire:click="resetFilters"
                    class="inline-flex items-center gap-1 text-xs font-semibold text-rose-600 hover:text-rose-700 hover:underline"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Reset Filter
                </button>
            </div>
        @endif
    </div>

    <!-- History Table Container -->
    <div class="rounded-xl border border-zinc-200 bg-white shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-zinc-600">
                <thead class="bg-zinc-50 border-b border-zinc-200 text-xs uppercase font-semibold text-zinc-500">
                    <tr>
                        <th class="px-4 py-3.5">Waktu</th>
                        <th class="px-4 py-3.5">Pengubah</th>
                        <th class="px-4 py-3.5">Aksi</th>
                        <th class="px-4 py-3.5">Pengajuan / Pemohon</th>
                        <th class="px-4 py-3.5">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200">
                    @forelse($histories as $history)
                        @php
                            $badgeColor = match($history->aksi) {
                                'terima' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'tolak' => 'bg-rose-50 text-rose-700 border-rose-200',
                                'tulis_harga' => 'bg-amber-50 text-amber-800 border-amber-200',
                                'ganti_harga' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                'selesai' => 'bg-blue-50 text-blue-700 border-blue-200',
                                'edit' => 'bg-sky-50 text-sky-700 border-sky-200',
                                'hapus' => 'bg-red-50 text-red-700 border-red-200',
                                'pulihkan' => 'bg-teal-50 text-teal-700 border-teal-200',
                                default => 'bg-purple-50 text-purple-700 border-purple-200',
                            };
                        @endphp
                        <tr class="hover:bg-zinc-50/80 transition-colors">
                            <td class="px-4 py-3.5 whitespace-nowrap text-xs text-zinc-500">
                                <div>{{ $history->created_at->translatedFormat('d M Y') }}</div>
                                <div class="text-[11px] text-zinc-400 font-mono">{{ $history->created_at->format('H:i:s') }} WIB</div>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs">
                                        {{ strtoupper(substr($history->user_name ?? 'S', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-medium text-zinc-900">{{ $history->user_name ?? 'Sistem / Pemohon' }}</div>
                                        @if($history->user)
                                            <div class="text-xs text-zinc-400">@<span>{{ $history->user->username }}</span></div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold border {{ $badgeColor }}">
                                    {{ $history->aksiLabel() }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                @if($history->submission)
                                    <div class="font-semibold text-zinc-900">{{ $history->submission->nama }}</div>
                                    <div class="text-xs text-zinc-500 line-clamp-1">{{ $history->submission->judul_alat }}</div>
                                @else
                                    <span class="text-xs text-zinc-400 italic">Pengajuan dihapus permanen</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-zinc-800">
                                {{ $history->keterangan }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-zinc-500">
                                Tidak ada riwayat perubahan yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($histories->hasPages())
            <div class="px-4 py-3.5 border-t border-zinc-200 bg-zinc-50">
                {{ $histories->links() }}
            </div>
        @endif
    </div>
</div>
