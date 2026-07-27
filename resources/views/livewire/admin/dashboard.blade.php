<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-zinc-900">{{ $trash ? 'Sampah Pengajuan' : 'Rekap Pengajuan' }}</h1>
            <p class="mt-1 text-sm text-zinc-500">
                {{ $trash ? 'Pengajuan yang telah dihapus, dapat dipulihkan.' : 'Semua pengajuan pembuatan alat yang masuk.' }}
            </p>
        </div>

        <div class="flex items-center gap-2">
            @if (auth()->user()->isSuperAdmin())
                <button
                    wire:click="toggleTrash"
                    class="inline-flex items-center rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50"
                >
                    {{ $trash ? 'Kembali ke Rekap' : 'Sampah' }}
                </button>
            @endif

            @unless ($trash)
                <button
                    wire:click="openReorder"
                    class="inline-flex items-center rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50"
                >
                    Edit Urutan
                </button>

                <a
                    href="{{ route('admin.export', $this->exportQuery()) }}"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-500"
                >
                    Export Excel
                </a>
            @endunless
        </div>
    </div>

    @unless ($trash)
        {{-- Bagi hasil (admin only) --}}
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6">
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm font-medium text-amber-900">Bagi Hasil Pendapatan (Selesai)</p>
                <p class="text-sm font-semibold tabular-nums text-amber-900">
                    Total: Rp {{ number_format($totalPendapatan, 0, ',', '.') }}
                </p>
            </div>
            <p class="text-xs text-amber-700 mb-3">Bagi hasil (23% / 38,5% / 38,5%) dihitung setelah dipotong pembagian prioritas sesuai penerima prioritas. Sisa biaya Cetak PCB &amp; Cetak 3D Printing masuk ke Lainnya.</p>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                @foreach ($bagiHasil as $bagian)
                    <div class="bg-white border border-amber-200 rounded-lg px-4 py-3">
                        <p class="text-xs text-zinc-500">{{ $bagian['label'] }} ({{ rtrim(rtrim(number_format($bagian['persen'] * 100, 1), '0'), '.') }}%)</p>
                        <p class="mt-1 text-lg font-semibold tabular-nums text-amber-700">
                            Rp {{ number_format($bagian['nominal'], 0, ',', '.') }}
                        </p>
                    </div>
                @endforeach
                <div class="bg-white border border-amber-200 rounded-lg px-4 py-3">
                    <p class="text-xs text-zinc-500">Lainnya (PCB/3D Printing)</p>
                    <p class="mt-1 text-lg font-semibold tabular-nums text-amber-700">
                        Rp {{ number_format($totalOther, 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Summary cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-6">
            <div class="bg-white border border-zinc-200 rounded-lg px-4 py-3">
                <p class="text-xs text-zinc-500">Total</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums text-zinc-900">{{ $summary['total'] }}</p>
            </div>
            <div class="bg-white border border-zinc-200 rounded-lg px-4 py-3">
                <p class="text-xs text-zinc-500">Baru</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums text-zinc-600">{{ $summary['baru'] }}</p>
            </div>
            <div class="bg-white border border-zinc-200 rounded-lg px-4 py-3">
                <p class="text-xs text-zinc-500">Diproses</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums text-blue-600">{{ $summary['diproses'] }}</p>
            </div>
            <div class="bg-white border border-zinc-200 rounded-lg px-4 py-3">
                <p class="text-xs text-zinc-500">Selesai</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums text-emerald-600">{{ $summary['selesai'] }}</p>
            </div>
            <div class="bg-white border border-zinc-200 rounded-lg px-4 py-3">
                <p class="text-xs text-zinc-500">Ditolak</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums text-red-600">{{ $summary['ditolak'] }}</p>
            </div>
        </div>
    @endunless

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

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
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

            <input type="date" wire:model.live="tanggalDari" class="rounded-md border-zinc-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Tgl Pengajuan dari">
            <input type="date" wire:model.live="tanggalSampai" class="rounded-md border-zinc-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Tgl Pengajuan sampai">
            <input type="date" wire:model.live="deadlineDari" class="rounded-md border-zinc-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Deadline dari">
            <input type="date" wire:model.live="deadlineSampai" class="rounded-md border-zinc-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Deadline sampai">
        </div>

        <div>
            <button wire:click="resetFilters" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Reset filter</button>
        </div>
    </div>

    {{-- Desktop table --}}
    <div class="hidden lg:block bg-white border border-zinc-200 rounded-lg overflow-x-auto">
        <table class="min-w-full divide-y divide-zinc-200">
            <thead class="bg-zinc-50 sticky top-0">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wide">No</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wide cursor-pointer select-none" wire:click="sortBy('urutan')">
                        Urutan
                        @if ($sortField === 'urutan') <span class="text-indigo-600">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                    </th>
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
                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wide">Biaya Jasa</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wide">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100">
                @forelse ($submissions as $i => $submission)
                    <tr class="hover:bg-zinc-50">
                        <td class="px-4 py-3 text-sm text-zinc-500 tabular-nums">{{ $submissions->firstItem() + $i }}</td>
                        <td class="px-4 py-3 text-sm text-zinc-500 tabular-nums">{{ $submission->urutan ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-zinc-900">{{ $submission->nama }}</td>
                        <td class="px-4 py-3 text-sm text-zinc-600"><div class="max-w-[200px] truncate" title="{{ $submission->judul_alat }}">{{ $submission->judul_alat }}</div></td>
                        <td class="px-4 py-3 text-sm text-zinc-600 whitespace-nowrap">{{ $submission->tipeLabel() }}</td>
                        <td class="px-4 py-3 text-sm text-zinc-600 tabular-nums">{{ $submission->tanggal_pengajuan->format('d-m-Y') }}</td>
                        <td class="px-4 py-3 text-sm tabular-nums {{ $submission->isOverdue() ? 'text-red-600 font-medium' : ($submission->isDeadlineUrgent() ? 'text-amber-600 font-medium' : 'text-zinc-600') }}">
                            {{ $submission->deadline->format('d-m-Y') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-zinc-600 tabular-nums">{{ $submission->biayaJasaFormatted() ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm"><x-status-badge :status="$submission->status" /></td>
                        <td class="px-4 py-3 text-left text-sm whitespace-nowrap">
                            <div class="inline-flex items-center gap-1.5">
                                @if ($trash)
                                    <button wire:click="restoreSubmission({{ $submission->id }})" class="inline-flex items-center rounded-md bg-indigo-600 px-2.5 py-1 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500">Pulihkan</button>
                                @else
                                    @if ($submission->status === 'baru')
                                        <button wire:click="openAccept({{ $submission->id }})" class="inline-flex items-center rounded-md bg-indigo-600 px-2.5 py-1 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500">Terima</button>
                                        <button
                                            wire:click="rejectSubmission({{ $submission->id }})"
                                            wire:confirm="Yakin ingin menolak pengajuan ini?"
                                            class="inline-flex items-center rounded-md bg-red-600 px-2.5 py-1 text-xs font-semibold text-white shadow-sm hover:bg-red-500"
                                        >
                                            Tolak
                                        </button>
                                    @endif

                                    <button wire:click="openDetail({{ $submission->id }})" class="inline-flex items-center rounded-md bg-zinc-600 px-2.5 py-1 text-xs font-semibold text-white shadow-sm hover:bg-zinc-500">Detail</button>

                                    <button wire:click="openEdit({{ $submission->id }})" class="inline-flex items-center rounded-md border border-indigo-300 bg-white px-2.5 py-1 text-xs font-semibold text-indigo-600 hover:bg-indigo-50">Edit</button>

                                    @if ($submission->biaya_jasa !== null)
                                        <a href="{{ route('admin.struk', $submission) }}" target="_blank" class="inline-flex items-center rounded-md border border-zinc-300 bg-white px-2.5 py-1 text-xs font-semibold text-zinc-700 hover:bg-zinc-50">Cetak Struk</a>
                                    @endif

                                    @if ($submission->status === 'diproses')
                                        <button
                                            wire:click="openComplete({{ $submission->id }})"
                                            class="inline-flex items-center rounded-md bg-emerald-600 px-2.5 py-1 text-xs font-semibold text-white shadow-sm hover:bg-emerald-500"
                                        >
                                            Selesai
                                        </button>
                                    @endif

                                    <button
                                        wire:click="confirmDelete({{ $submission->id }})"
                                        class="inline-flex items-center rounded-md border border-red-300 bg-white px-2.5 py-1 text-xs font-semibold text-red-600 hover:bg-red-50"
                                    >
                                        Hapus
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-4 py-12 text-center text-sm text-zinc-400">
                            {{ $trash ? 'Sampah kosong.' : 'Belum ada pengajuan masuk.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile card list --}}
    <div class="lg:hidden space-y-3">
        @forelse ($submissions as $submission)
            <div class="bg-white border border-zinc-200 rounded-lg p-4">
                {{-- Header: nama + judul alat --}}
                <div class="min-w-0 flex items-start gap-2">
                    <span class="flex-none text-xs font-semibold text-zinc-400 tabular-nums">#{{ $submission->urutan ?? '—' }}</span>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-zinc-900 truncate">{{ $submission->nama }}</p>
                        <p class="text-xs text-zinc-500 truncate mt-0.5">{{ $submission->judul_alat }}</p>
                    </div>
                </div>

                {{-- Badge status --}}
                <div class="mt-2">
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
                    <div>
                        <dt class="text-xs text-zinc-400">Biaya Jasa</dt>
                        <dd class="text-zinc-700 tabular-nums">{{ $submission->biayaJasaFormatted() ?? '—' }}</dd>
                    </div>
                </dl>

                {{-- Tombol aksi: wrap ke baris baru kalau tidak muat --}}
                <div class="mt-3 flex flex-wrap items-center gap-2">
                    @if ($trash)
                        <button wire:click="restoreSubmission({{ $submission->id }})" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm">Pulihkan</button>
                    @else
                        @if ($submission->status === 'baru')
                            <button wire:click="openAccept({{ $submission->id }})" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm">Terima</button>
                            <button
                                wire:click="rejectSubmission({{ $submission->id }})"
                                wire:confirm="Yakin ingin menolak pengajuan ini?"
                                class="inline-flex items-center rounded-md bg-red-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm"
                            >Tolak</button>
                        @endif

                        <button wire:click="openDetail({{ $submission->id }})" class="inline-flex items-center rounded-md bg-zinc-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm">Detail</button>

                        <button wire:click="openEdit({{ $submission->id }})" class="inline-flex items-center rounded-md border border-indigo-300 bg-white px-3 py-1.5 text-xs font-semibold text-indigo-600">Edit</button>

                        @if ($submission->biaya_jasa !== null)
                            <a href="{{ route('admin.struk', $submission) }}" target="_blank" class="inline-flex items-center rounded-md border border-zinc-300 bg-white px-3 py-1.5 text-xs font-semibold text-zinc-700">Cetak Struk</a>
                        @endif

                        @if ($submission->status === 'diproses')
                            <button
                                wire:click="openComplete({{ $submission->id }})"
                                class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm"
                            >Selesai</button>
                        @endif

                        <button
                            wire:click="confirmDelete({{ $submission->id }})"
                            class="inline-flex items-center rounded-md border border-red-300 bg-white px-3 py-1.5 text-xs font-semibold text-red-600"
                        >Hapus</button>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white border border-zinc-200 rounded-lg p-8 text-center text-sm text-zinc-400">
                {{ $trash ? 'Sampah kosong.' : 'Belum ada pengajuan masuk.' }}
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $submissions->links() }}
    </div>

    {{-- Detail / Edit modal --}}
    @if ($activeSubmission)
        <div class="fixed inset-0 z-40 overflow-y-auto">
            <div class="fixed inset-0 bg-zinc-900/40" wire:click="closeModal"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white rounded-lg shadow-xl w-full max-w-lg p-6">
                    @if ($modalMode === 'detail')
                        <div class="flex items-start justify-between mb-4">
                            <h2 class="text-lg font-semibold text-zinc-900">Detail Pengajuan</h2>
                            <x-status-badge :status="$activeSubmission->status" />
                        </div>

                        <dl class="space-y-3 text-sm">
                            <div class="grid grid-cols-3 gap-2"><dt class="text-zinc-500">Nama</dt><dd class="col-span-2 text-zinc-900">{{ $activeSubmission->nama }}</dd></div>
                            <div class="grid grid-cols-3 gap-2"><dt class="text-zinc-500">Asal Kampus/Sekolah</dt><dd class="col-span-2 text-zinc-900">{{ $activeSubmission->asal_kampus }}</dd></div>
                            <div class="grid grid-cols-3 gap-2"><dt class="text-zinc-500">Judul Alat</dt><dd class="col-span-2 text-zinc-900">{{ $activeSubmission->judul_alat }}</dd></div>
                            <div class="grid grid-cols-3 gap-2"><dt class="text-zinc-500">Tipe</dt><dd class="col-span-2 text-zinc-900">{{ $activeSubmission->tipeLabel() }}</dd></div>
                            <div class="grid grid-cols-3 gap-2"><dt class="text-zinc-500">Fitur</dt><dd class="col-span-2 text-zinc-900 whitespace-pre-line">{{ $activeSubmission->fitur }}</dd></div>
                            <div class="grid grid-cols-3 gap-2"><dt class="text-zinc-500">Tgl Pengajuan</dt><dd class="col-span-2 text-zinc-900 tabular-nums">{{ $activeSubmission->tanggal_pengajuan->format('d-m-Y') }}</dd></div>
                            <div class="grid grid-cols-3 gap-2"><dt class="text-zinc-500">Deadline</dt><dd class="col-span-2 text-zinc-900 tabular-nums">{{ $activeSubmission->deadline->format('d-m-Y') }}</dd></div>
                            <div class="grid grid-cols-3 gap-2"><dt class="text-zinc-500">Biaya Jasa</dt><dd class="col-span-2 text-zinc-900 tabular-nums">{{ $activeSubmission->biayaJasaFormatted() ?? 'Belum ditentukan' }}</dd></div>
                            @if ($activeSubmission->pembagian_prioritas)
                                <div class="grid grid-cols-3 gap-2"><dt class="text-zinc-500">Pembagian Prioritas</dt><dd class="col-span-2 text-zinc-900 tabular-nums">{{ $activeSubmission->pembagianPrioritasFormatted() }} @if ($activeSubmission->penerima_prioritas) ({{ $activeSubmission->penerimaPrioritasLabel() }}) @endif</dd></div>
                            @endif
                            @if ($activeSubmission->tipe === \App\Models\Submission::TIPE_LAIN_LAIN)
                                <div class="grid grid-cols-3 gap-2"><dt class="text-zinc-500">Penerima</dt><dd class="col-span-2 text-zinc-900">{{ $activeSubmission->penerimaLabel() ?? 'Belum ditentukan' }}</dd></div>
                            @endif
                            @if ($activeSubmission->status === \App\Models\Submission::STATUS_SELESAI)
                                <div class="grid grid-cols-3 gap-2"><dt class="text-zinc-500">Metode Pembayaran</dt><dd class="col-span-2 text-zinc-900">{{ $activeSubmission->metodePembayaranLabel() ?? '—' }}</dd></div>
                                <div class="grid grid-cols-3 gap-2"><dt class="text-zinc-500">Jumlah Dibayar</dt><dd class="col-span-2 text-zinc-900 tabular-nums">{{ $activeSubmission->jumlahBayarFormatted() ?? '—' }}</dd></div>
                            @endif
                            <div class="grid grid-cols-3 gap-2"><dt class="text-zinc-500">Dibuat</dt><dd class="col-span-2 text-zinc-900 tabular-nums">{{ $activeSubmission->created_at->format('d-m-Y H:i') }}</dd></div>
                        </dl>

                        <div class="mt-6 flex justify-end gap-2">
                            <button wire:click="closeModal" class="rounded-md border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50">Tutup</button>
                            <button wire:click="switchToEdit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">Edit</button>
                        </div>
                    @else
                        <h2 class="text-lg font-semibold text-zinc-900 mb-4">Edit Pengajuan</h2>

                        <form wire:submit="save" class="space-y-4 max-h-[70vh] overflow-y-auto pr-1">
                            <div>
                                <label class="block text-sm font-medium text-zinc-700">Nama</label>
                                <input type="text" wire:model.blur="form.nama" class="mt-1.5 block w-full rounded-md border-zinc-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                @error('form.nama') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-zinc-700">Asal Kampus/Sekolah</label>
                                <input type="text" wire:model.blur="form.asal_kampus" class="mt-1.5 block w-full rounded-md border-zinc-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                @error('form.asal_kampus') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-zinc-700">Judul Alat</label>
                                <input type="text" wire:model.blur="form.judul_alat" class="mt-1.5 block w-full rounded-md border-zinc-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                @error('form.judul_alat') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-zinc-700">Tipe</label>
                                <select wire:model.live="form.tipe" class="mt-1.5 block w-full rounded-md border-zinc-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    @foreach (\App\Models\Submission::TIPE_LABELS as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('form.tipe') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-zinc-700">Fitur</label>
                                <textarea wire:model.blur="form.fitur" rows="3" class="mt-1.5 block w-full rounded-md border-zinc-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                                @error('form.fitur') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-zinc-700">Tgl Pengajuan</label>
                                    <input type="date" wire:model.blur="form.tanggal_pengajuan" class="mt-1.5 block w-full rounded-md border-zinc-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    @error('form.tanggal_pengajuan') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-zinc-700">Deadline</label>
                                    <input type="date" wire:model.blur="form.deadline" class="mt-1.5 block w-full rounded-md border-zinc-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    @error('form.deadline') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-zinc-700">Status</label>
                                <select wire:model.live="form.status" class="mt-1.5 block w-full rounded-md border-zinc-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    @foreach (\App\Models\Submission::STATUS_LABELS as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('form.status') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-zinc-700">Biaya Jasa (Rp)</label>
                                <input type="number" min="0" step="1000" wire:model.blur="form.biaya_jasa" class="mt-1.5 block w-full rounded-md border-zinc-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                @error('form.biaya_jasa') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-zinc-700">Pembagian Prioritas (Rp)</label>
                                <input type="number" min="0" step="1000" wire:model.live="form.pembagian_prioritas" placeholder="0" class="mt-1.5 block w-full rounded-md border-zinc-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <p class="mt-1 text-xs text-zinc-500">Nominal ini dipotong terlebih dahulu sebelum pembagian persentase (23% / 38,5% / 38,5%).</p>
                                @error('form.pembagian_prioritas') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            @if ((int) $form->pembagian_prioritas > 0)
                                <div>
                                    <label class="block text-sm font-medium text-zinc-700">Penerima Prioritas</label>
                                    <select wire:model.blur="form.penerima_prioritas" class="mt-1.5 block w-full rounded-md border-zinc-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        @foreach (\App\Models\Submission::PENERIMA_PRIORITAS_LABELS as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <p class="mt-1 text-xs text-zinc-500">Pilih penerima nominal prioritas (masuk 100% tanpa dipotong persentase).</p>
                                    @error('form.penerima_prioritas') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            @endif

                            @if ($form->tipe === \App\Models\Submission::TIPE_LAIN_LAIN)
                                <div>
                                    <label class="block text-sm font-medium text-zinc-700">Penerima</label>
                                    <select wire:model.blur="form.penerima" class="mt-1.5 block w-full rounded-md border-zinc-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="">Pilih penerima</option>
                                        @foreach (\App\Models\Submission::PENERIMA_LABELS as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('form.penerima') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            @endif

                            @if ($form->status === \App\Models\Submission::STATUS_SELESAI)
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-zinc-700">Dibayar Lewat</label>
                                        <select wire:model.blur="form.metode_pembayaran" class="mt-1.5 block w-full rounded-md border-zinc-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <option value="">Pilih metode</option>
                                            @foreach (\App\Models\Submission::METODE_PEMBAYARAN_LABELS as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @error('form.metode_pembayaran') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-zinc-700">Jumlah Dibayarkan (Rp)</label>
                                        <input type="number" min="0" step="1000" wire:model.blur="form.jumlah_bayar" class="mt-1.5 block w-full rounded-md border-zinc-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        @error('form.jumlah_bayar') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            @endif

                            <div class="flex justify-end gap-2 pt-2">
                                <button type="button" wire:click="closeModal" class="rounded-md border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50">Batal</button>
                                <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">Simpan</button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Accept (Terima) modal --}}
    @if ($acceptingId)
        <div class="fixed inset-0 z-40 overflow-y-auto">
            <div class="fixed inset-0 bg-zinc-900/40" wire:click="closeAccept"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white rounded-lg shadow-xl w-full max-w-sm p-6">
                    <h2 class="text-lg font-semibold text-zinc-900">Terima Pengajuan</h2>
                    <p class="mt-1 text-sm text-zinc-500">Tentukan biaya jasa dan deadline sebelum pengajuan mulai diproses.</p>

                    <form wire:submit="acceptSubmission" class="mt-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-zinc-700">Biaya Jasa (Rp)</label>
                            <input type="number" min="0" step="1000" wire:model.blur="acceptBiayaJasa" placeholder="mis. 500000" class="mt-1.5 block w-full rounded-md border-zinc-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('acceptBiayaJasa') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-zinc-700">Deadline</label>
                            <input type="date" wire:model.blur="acceptDeadline" class="mt-1.5 block w-full rounded-md border-zinc-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('acceptDeadline') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        @if ($acceptingSubmission?->tipe === \App\Models\Submission::TIPE_LAIN_LAIN)
                            <div>
                                <label class="block text-sm font-medium text-zinc-700">Penerima</label>
                                <select wire:model.blur="acceptPenerima" class="mt-1.5 block w-full rounded-md border-zinc-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Pilih penerima</option>
                                    @foreach (\App\Models\Submission::PENERIMA_LABELS as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('acceptPenerima') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        @endif

                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" wire:click="closeAccept" class="rounded-md border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50">Batal</button>
                            <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">Terima & Proses</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Complete (Selesai) payment modal --}}
    @if ($completingId && $completingSubmission)
        <div class="fixed inset-0 z-40 overflow-y-auto">
            <div class="fixed inset-0 bg-zinc-900/40" wire:click="closeComplete"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white rounded-lg shadow-xl w-full max-w-sm p-6">
                    <h2 class="text-lg font-semibold text-zinc-900">Selesaikan Pengajuan</h2>
                    <p class="mt-1 text-sm text-zinc-500">Catat pembayaran sebelum pengajuan ditandai Selesai.</p>

                    <div class="mt-4 rounded-md bg-zinc-50 border border-zinc-200 px-3 py-2 text-sm">
                        <div class="flex justify-between"><span class="text-zinc-500">Biaya Jasa</span><span class="font-medium text-zinc-900 tabular-nums">{{ $completingSubmission->biayaJasaFormatted() ?? '—' }}</span></div>
                    </div>

                    <form wire:submit="completeSubmission" class="mt-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-zinc-700">Dibayar Lewat</label>
                            <select wire:model.live="completeMetodePembayaran" class="mt-1.5 block w-full rounded-md border-zinc-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                @foreach (\App\Models\Submission::METODE_PEMBAYARAN_LABELS as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('completeMetodePembayaran') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-zinc-700">Jumlah Dibayarkan (Rp)</label>
                            <input type="number" min="0" step="1000" wire:model.live="completeJumlahBayar" class="mt-1.5 block w-full rounded-md border-zinc-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('completeJumlahBayar') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-zinc-700">Pembagian Prioritas (Rp)</label>
                            <input type="number" min="0" step="1000" wire:model.live="completePembagianPrioritas" placeholder="0" class="mt-1.5 block w-full rounded-md border-zinc-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <p class="mt-1 text-xs text-zinc-500">Nominal prioritas dipotong sebelum bagi hasil persentase.</p>
                            @error('completePembagianPrioritas') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        @if ((int) $completePembagianPrioritas > 0)
                            <div>
                                <label class="block text-sm font-medium text-zinc-700">Penerima Prioritas</label>
                                <select wire:model.blur="completePenerimaPrioritas" class="mt-1.5 block w-full rounded-md border-zinc-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    @foreach (\App\Models\Submission::PENERIMA_PRIORITAS_LABELS as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('completePenerimaPrioritas') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        @endif

                        <div class="flex justify-between text-sm">
                            <span class="text-zinc-500">Kembalian</span>
                            <span class="font-medium text-zinc-900 tabular-nums">
                                Rp {{ number_format(max((int) $completeJumlahBayar - ($completingSubmission->biaya_jasa ?? 0), 0), 0, ',', '.') }}
                            </span>
                        </div>

                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" wire:click="closeComplete" class="rounded-md border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50">Batal</button>
                            <button type="submit" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-500">Tandai Selesai</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Reorder (Edit Urutan) modal --}}
    @if ($reordering)
        <div
            class="fixed inset-0 z-40 overflow-y-auto"
            x-data="{
                items: @js($reorderItems),
                dragIndex: null,
                dragStart(index) { this.dragIndex = index },
                drop(index) {
                    if (this.dragIndex === null || this.dragIndex === index) return;
                    const moved = this.items.splice(this.dragIndex, 1)[0];
                    this.items.splice(index, 0, moved);
                    this.dragIndex = null;
                },
                save() { $wire.saveReorder(this.items.map(item => item.id)) },
            }"
        >
            <div class="fixed inset-0 bg-zinc-900/40" wire:click="closeReorder"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white rounded-lg shadow-xl w-full max-w-lg p-6">
                    <h2 class="text-lg font-semibold text-zinc-900">Edit Urutan Antrian</h2>
                    <p class="mt-1 text-sm text-zinc-500">Geser (drag) untuk menyusun ulang. Nomor 1 dikerjakan paling pertama.</p>

                    <ul class="mt-4 space-y-2 max-h-[60vh] overflow-y-auto pr-1">
                        <template x-for="(item, index) in items" :key="item.id">
                            <li
                                draggable="true"
                                @dragstart="dragStart(index)"
                                @dragover.prevent
                                @drop.prevent="drop(index)"
                                class="flex items-center gap-3 rounded-md border border-zinc-200 bg-white px-3 py-2 cursor-move select-none hover:bg-zinc-50"
                            >
                                <span class="flex h-6 w-6 flex-none items-center justify-center rounded-full bg-indigo-100 text-xs font-semibold text-indigo-700" x-text="index + 1"></span>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-zinc-900 truncate" x-text="item.nama"></p>
                                    <p class="text-xs text-zinc-500 truncate" x-text="item.judul_alat"></p>
                                </div>
                            </li>
                        </template>
                    </ul>

                    <div class="mt-6 flex justify-end gap-2">
                        <button type="button" wire:click="closeReorder" class="rounded-md border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50">Batal</button>
                        <button type="button" @click="save" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">Simpan Urutan</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete confirmation --}}
    @if ($confirmingDeleteId)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="fixed inset-0 bg-zinc-900/40" wire:click="$set('confirmingDeleteId', null)"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white rounded-lg shadow-xl w-full max-w-sm p-6">
                    <h2 class="text-base font-semibold text-zinc-900">Hapus pengajuan?</h2>
                    <p class="mt-2 text-sm text-zinc-500">Pengajuan akan dipindahkan ke sampah dan dapat dipulihkan kembali oleh super admin.</p>

                    <div class="mt-6 flex justify-end gap-2">
                        <button wire:click="$set('confirmingDeleteId', null)" class="rounded-md border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50">Batal</button>
                        <button wire:click="deleteSubmission" class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-500">Hapus</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
