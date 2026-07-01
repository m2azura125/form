<div>
    <div class="max-w-xl mx-auto">
        @if ($submitted)
            <div class="bg-white border border-zinc-200 rounded-xl shadow-sm ring-1 ring-zinc-900/5 p-8 text-center">
                <div class="mx-auto w-12 h-12 rounded-full bg-emerald-50 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h1 class="text-lg font-semibold text-zinc-900">Pengajuan berhasil dikirim</h1>
                <p class="mt-2 text-sm text-zinc-500">Terima kasih, pengajuan Anda telah kami terima dan akan segera kami tinjau.</p>
                <button
                    type="button"
                    wire:click="ajukanLagi"
                    class="mt-6 inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 transition"
                >
                    Ajukan Lagi
                </button>
            </div>
        @else
            <div class="mb-6 text-center sm:text-left">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-600">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Tanpa akun, isi langsung
                </span>
                <h1 class="mt-3 text-2xl font-semibold tracking-tight text-zinc-900">Form Pengajuan Project</h1>
                <p class="mt-1.5 text-sm text-zinc-500">Ceritakan alat yang ingin Anda buat, tim kami akan meninjau dan menghubungi Anda kembali.</p>
            </div>

            <form wire:submit="submit" class="bg-white border border-zinc-200 rounded-xl shadow-sm ring-1 ring-zinc-900/5 overflow-hidden">
                <div class="h-1.5 bg-indigo-600"></div>

                <div class="p-6 sm:p-8 space-y-5">
                    {{-- Honeypot: hidden from real users, left empty by them --}}
                    <div class="absolute w-px h-px overflow-hidden -m-px" style="clip: rect(0,0,0,0); clip-path: inset(50%);" aria-hidden="true">
                        <label for="website">Jangan diisi</label>
                        <input type="text" id="website" wire:model="honeypot" tabindex="-1" autocomplete="off">
                    </div>

                    <div>
                        <label for="nama" class="block text-sm font-medium text-zinc-700">Nama</label>
                        <div class="relative mt-1.5">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-zinc-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </span>
                            <input
                                type="text"
                                id="nama"
                                wire:model.blur="form.nama"
                                placeholder="Nama lengkap Anda"
                                class="block w-full rounded-md border-zinc-300 pl-10 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                        </div>
                        @error('form.nama') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="asal_kampus" class="block text-sm font-medium text-zinc-700">Asal Kampus/Sekolah</label>
                        <div class="relative mt-1.5">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-zinc-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.42A12.083 12.083 0 0121 13c0 3.517-2.686 6.5-6.16 7.42L12 21l-2.84-.58A12.083 12.083 0 013 13c0-.577.078-1.136.22-1.668L12 14zm0 0v7" />
                                </svg>
                            </span>
                            <input
                                type="text"
                                id="asal_kampus"
                                wire:model.blur="form.asal_kampus"
                                placeholder="Nama kampus atau sekolah"
                                class="block w-full rounded-md border-zinc-300 pl-10 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                        </div>
                        @error('form.asal_kampus') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="judul_alat" class="block text-sm font-medium text-zinc-700">Judul Alat</label>
                        <div class="relative mt-1.5">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-zinc-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </span>
                            <input
                                type="text"
                                id="judul_alat"
                                wire:model.blur="form.judul_alat"
                                placeholder="mis. Alat Pengering Otomatis Berbasis Arduino"
                                class="block w-full rounded-md border-zinc-300 pl-10 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                        </div>
                        @error('form.judul_alat') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="fitur" class="block text-sm font-medium text-zinc-700">Fitur</label>
                        <textarea
                            id="fitur"
                            wire:model.blur="form.fitur"
                            rows="4"
                            placeholder="Jelaskan fitur/spesifikasi alat yang Anda butuhkan"
                            class="mt-1.5 block w-full rounded-md border-zinc-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        ></textarea>
                        @error('form.fitur') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="tanggal_pengajuan" class="block text-sm font-medium text-zinc-700">Tanggal Pengajuan</label>
                            <div class="relative mt-1.5">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-zinc-400">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </span>
                                <input
                                    type="date"
                                    id="tanggal_pengajuan"
                                    wire:model.blur="form.tanggal_pengajuan"
                                    class="block w-full rounded-md border-zinc-300 pl-10 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                >
                            </div>
                            @error('form.tanggal_pengajuan') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="deadline" class="block text-sm font-medium text-zinc-700">Deadline</label>
                            <div class="relative mt-1.5">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-zinc-400">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </span>
                                <input
                                    type="date"
                                    id="deadline"
                                    wire:model.blur="form.deadline"
                                    class="block w-full rounded-md border-zinc-300 pl-10 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                >
                            </div>
                            @error('form.deadline') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="submit"
                        class="w-full inline-flex items-center justify-center gap-2 rounded-md bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-500 disabled:opacity-60 disabled:cursor-not-allowed transition"
                    >
                        <svg wire:loading.remove wire:target="submit" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                        <span wire:loading.remove wire:target="submit">Kirim Pengajuan</span>
                        <span wire:loading wire:target="submit">Mengirim...</span>
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>
