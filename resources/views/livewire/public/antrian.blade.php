<div class="max-w-6xl mx-auto">
    <div class="mb-6">
        <h1 class="text-xl font-semibold text-zinc-900">List Antrian Pengajuan</h1>
        <p class="mt-1 text-sm text-zinc-500">Daftar seluruh pengajuan yang masuk beserta statusnya.</p>
    </div>

    {{-- Search & filters --}}
    <div class="bg-white border border-zinc-200 rounded-lg p-4 mb-4 space-y-3">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Cari nama atau judul alat..."
                    class="block w-full rounded-md border-zinc-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                >
            </div>

            <select wire:model.live="perPage" class="rounded-md border-zinc-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="15">15 / halaman</option>
                <option value="25">25 / halaman</option>
                <option value="50">50 / halaman</option>
            </select>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            <select wire:model.live="status" class="rounded-md border-zinc-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="">Semua Status</option>
                @foreach (\App\Models\Submission::STATUS_LABELS as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>

            <select wire:model.live="tipe" class="rounded-md border-zinc-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="">Semua Tipe</option>
                @foreach (\App\Models\Submission::TIPE_LABELS as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>

            <div class="flex items-center">
                <button wire:click="resetFilters" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Reset filter</button>
            </div>
        </div>
    </div>

    {{-- Desktop table --}}
    <div class="hidden lg:block bg-white border border-zinc-200 rounded-lg overflow-x-auto">
        <table class="min-w-full divide-y divide-zinc-200">
            <thead class="bg-zinc-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wide">No</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wide cursor-pointer select-none" wire:click="sortBy('nama')">
                        Nama
                        @if ($sortField === 'nama') <span class="text-indigo-600">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wide">Judul Alat</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wide">Tipe</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wide cursor-pointer select-none" wire:click="sortBy('tanggal_pengajuan')">
                        Tgl Pengajuan
                        @if ($sortField === 'tanggal_pengajuan') <span class="text-indigo-600">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wide cursor-pointer select-none" wire:click="sortBy('deadline')">
                        Deadline
                        @if ($sortField === 'deadline') <span class="text-indigo-600">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wide">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100">
                @forelse ($submissions as $i => $submission)
                    <tr class="hover:bg-zinc-50">
                        <td class="px-4 py-3 text-sm text-zinc-500 tabular-nums">{{ $submissions->firstItem() + $i }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-zinc-900">{{ $submission->nama }}</td>
                        <td class="px-4 py-3 text-sm text-zinc-600"><div class="max-w-[220px] truncate" title="{{ $submission->judul_alat }}">{{ $submission->judul_alat }}</div></td>
                        <td class="px-4 py-3 text-sm text-zinc-600 whitespace-nowrap">{{ $submission->tipeLabel() }}</td>
                        <td class="px-4 py-3 text-sm text-zinc-600 tabular-nums">{{ $submission->tanggal_pengajuan->format('d-m-Y') }}</td>
                        <td class="px-4 py-3 text-sm tabular-nums {{ $submission->isOverdue() ? 'text-red-600 font-medium' : ($submission->isDeadlineUrgent() ? 'text-amber-600 font-medium' : 'text-zinc-600') }}">
                            {{ $submission->deadline->format('d-m-Y') }}
                        </td>
                        <td class="px-4 py-3 text-sm"><x-status-badge :status="$submission->status" /></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-sm text-zinc-400">Belum ada pengajuan masuk.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile card list --}}
    <div class="lg:hidden space-y-3">
        @forelse ($submissions as $submission)
            <div class="bg-white border border-zinc-200 rounded-lg p-4">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-zinc-900">{{ $submission->nama }}</p>
                        <p class="text-sm text-zinc-500">{{ $submission->judul_alat }}</p>
                    </div>
                    <x-status-badge :status="$submission->status" />
                </div>

                <dl class="mt-3 grid grid-cols-2 gap-2 text-sm">
                    <div>
                        <dt class="text-xs text-zinc-400">Tipe</dt>
                        <dd class="text-zinc-700">{{ $submission->tipeLabel() }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-zinc-400">Tgl Pengajuan</dt>
                        <dd class="text-zinc-700 tabular-nums">{{ $submission->tanggal_pengajuan->format('d-m-Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-zinc-400">Deadline</dt>
                        <dd class="tabular-nums {{ $submission->isOverdue() ? 'text-red-600 font-medium' : ($submission->isDeadlineUrgent() ? 'text-amber-600 font-medium' : 'text-zinc-700') }}">
                            {{ $submission->deadline->format('d-m-Y') }}
                        </dd>
                    </div>
                </dl>
            </div>
        @empty
            <div class="bg-white border border-zinc-200 rounded-lg p-8 text-center text-sm text-zinc-400">Belum ada pengajuan masuk.</div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $submissions->links() }}
    </div>
</div>
