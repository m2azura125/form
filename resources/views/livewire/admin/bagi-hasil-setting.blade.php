<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Persentase Bagi Hasil</h1>
        <p class="mt-1 text-sm text-zinc-500">Atur persentase bagi hasil project per bulan. Bulan yang tidak diatur memakai pengaturan bulan terakhir sebelumnya.</p>
    </div>

    @if ($pesan)
        <div class="mb-4 rounded-md bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">{{ $pesan }}</div>
    @endif

    <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3">
        <p class="text-xs text-amber-700">Berlaku bulan ini ({{ $bulanIniLabel }})</p>
        <p class="mt-1 text-sm font-semibold text-amber-900">
            Toko {{ \App\Models\PersentaseBagiHasil::formatPersen($persenBulanIni['toko']) }}
            · Krisna {{ \App\Models\PersentaseBagiHasil::formatPersen($persenBulanIni['krisna']) }}
            · Aldo {{ \App\Models\PersentaseBagiHasil::formatPersen($persenBulanIni['aldo']) }}
        </p>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Form atur / edit --}}
        <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
            <h2 class="text-base font-semibold text-zinc-900">{{ $sudahAda ? 'Edit Persentase' : 'Atur Persentase' }}</h2>
            <p class="mt-1 text-xs text-zinc-500">
                {{ $sudahAda ? 'Bulan ini sudah punya pengaturan. Simpan untuk mengubahnya.' : 'Bulan ini belum diatur. Angka di bawah diambil dari pengaturan yang berlaku.' }}
            </p>

            <form wire:submit="save" class="mt-4 space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700">Bulan</label>
                        <select wire:model.live="bulan" class="mt-1.5 block w-full rounded-md border-zinc-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @foreach ($bulanLabels as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('bulan') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700">Tahun</label>
                        <select wire:model.live="tahun" class="mt-1.5 block w-full rounded-md border-zinc-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @foreach ($tahunOptions as $value)
                                <option value="{{ $value }}">{{ $value }}</option>
                            @endforeach
                        </select>
                        @error('tahun') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                @foreach (['toko' => 'Toko', 'krisna' => 'Krisna', 'aldo' => 'Aldo'] as $field => $label)
                    <div>
                        <label class="block text-sm font-medium text-zinc-700">{{ $label }} (%)</label>
                        <input type="number" step="0.01" min="0" max="100" wire:model.live.debounce.300ms="{{ $field }}" class="mt-1.5 block w-full rounded-md border-zinc-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm tabular-nums">
                        @error($field) <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                @endforeach

                <div class="flex items-center justify-between rounded-md px-3 py-2 text-sm {{ abs($total - 100) < 0.001 ? 'bg-emerald-50 text-emerald-800' : 'bg-red-50 text-red-700' }}">
                    <span>Total</span>
                    <span class="font-semibold tabular-nums">{{ \App\Models\PersentaseBagiHasil::formatPersen($total) }}</span>
                </div>
                @error('total') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

                <div class="flex justify-end">
                    <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">Simpan</button>
                </div>
            </form>
        </div>

        {{-- Daftar pengaturan --}}
        <div class="lg:col-span-2 rounded-lg border border-zinc-200 bg-white shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-zinc-200">
                <h2 class="text-base font-semibold text-zinc-900">Riwayat Pengaturan</h2>
                <p class="mt-1 text-xs text-zinc-500">Jika belum ada pengaturan, dipakai bawaan Toko 23% · Krisna 38,5% · Aldo 38,5%.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200">
                    <thead class="bg-zinc-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wide">Berlaku Mulai</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 uppercase tracking-wide">Toko</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 uppercase tracking-wide">Krisna</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 uppercase tracking-wide">Aldo</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wide">Diubah</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200">
                        @forelse ($pengaturans as $pengaturan)
                            <tr class="{{ (int) $bulan === $pengaturan->bulan && (int) $tahun === $pengaturan->tahun ? 'bg-indigo-50/60' : '' }}">
                                <td class="px-4 py-3 text-sm font-medium text-zinc-900 whitespace-nowrap">{{ $bulanLabels[$pengaturan->bulan] }} {{ $pengaturan->tahun }}</td>
                                <td class="px-4 py-3 text-sm text-right tabular-nums text-zinc-700">{{ \App\Models\PersentaseBagiHasil::formatPersen($pengaturan->toko) }}</td>
                                <td class="px-4 py-3 text-sm text-right tabular-nums text-zinc-700">{{ \App\Models\PersentaseBagiHasil::formatPersen($pengaturan->krisna) }}</td>
                                <td class="px-4 py-3 text-sm text-right tabular-nums text-zinc-700">{{ \App\Models\PersentaseBagiHasil::formatPersen($pengaturan->aldo) }}</td>
                                <td class="px-4 py-3 text-xs text-zinc-500 whitespace-nowrap">
                                    {{ $pengaturan->diubah_oleh ?? '—' }}<br>{{ $pengaturan->updated_at->format('d-m-Y H:i') }}
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <button wire:click="edit({{ $pengaturan->id }})" class="inline-flex items-center rounded-md border border-indigo-300 bg-white px-2.5 py-1 text-xs font-semibold text-indigo-600 hover:bg-indigo-50">Edit</button>
                                    <button wire:click="confirmDelete({{ $pengaturan->id }})" class="inline-flex items-center rounded-md border border-red-300 bg-white px-2.5 py-1 text-xs font-semibold text-red-600 hover:bg-red-50">Hapus</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-sm text-zinc-400">Belum ada pengaturan. Semua bulan memakai persentase bawaan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if ($confirmingDeleteId)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="fixed inset-0 bg-zinc-900/40" wire:click="$set('confirmingDeleteId', null)"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white rounded-lg shadow-xl w-full max-w-sm p-6">
                    <h2 class="text-base font-semibold text-zinc-900">Hapus pengaturan?</h2>
                    <p class="mt-2 text-sm text-zinc-500">Bulan tersebut akan kembali memakai pengaturan bulan sebelumnya (atau persentase bawaan).</p>

                    <div class="mt-6 flex justify-end gap-2">
                        <button wire:click="$set('confirmingDeleteId', null)" class="rounded-md border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50">Batal</button>
                        <button wire:click="delete" class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-500">Hapus</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
