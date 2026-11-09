<?php

namespace App\Livewire\Admin;

use App\Models\PersentaseBagiHasil;
use App\Models\Submission;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class BagiHasilSetting extends Component
{
    public const BULAN_LABELS = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public string $bulan = '';

    public string $tahun = '';

    public string $toko = '';

    public string $krisna = '';

    public string $aldo = '';

    public ?int $confirmingDeleteId = null;

    public ?string $pesan = null;

    public function mount(): void
    {
        $this->bulan = (string) date('n');
        $this->tahun = (string) date('Y');
        $this->isiDariBulanTerpilih();
    }

    public function updatedBulan(): void
    {
        $this->isiDariBulanTerpilih();
    }

    public function updatedTahun(): void
    {
        $this->isiDariBulanTerpilih();
    }

    public function edit(int $id): void
    {
        $pengaturan = PersentaseBagiHasil::query()->findOrFail($id);

        $this->bulan = (string) $pengaturan->bulan;
        $this->tahun = (string) $pengaturan->tahun;
        $this->isiPersen($pengaturan->persen());
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->validate([
            'bulan' => ['required', 'integer', 'between:1,12'],
            'tahun' => ['required', 'integer', 'between:2000,2099'],
            'toko' => ['required', 'numeric', 'between:0,100'],
            'krisna' => ['required', 'numeric', 'between:0,100'],
            'aldo' => ['required', 'numeric', 'between:0,100'],
        ], [
            'bulan.required' => 'Bulan wajib dipilih.',
            'tahun.required' => 'Tahun wajib diisi.',
            'tahun.between' => 'Tahun tidak valid.',
            '*.required' => 'Persentase wajib diisi.',
            '*.numeric' => 'Persentase harus berupa angka.',
            '*.between' => 'Persentase harus antara 0 sampai 100.',
        ]);

        if (abs($this->total() - 100) > 0.001) {
            $this->addError('total', 'Total persentase harus 100%. Sekarang '.PersentaseBagiHasil::formatPersen($this->total()).'.');

            return;
        }

        PersentaseBagiHasil::query()->updateOrCreate(
            ['tahun' => (int) $this->tahun, 'bulan' => (int) $this->bulan],
            [
                'toko' => (float) $this->toko,
                'krisna' => (float) $this->krisna,
                'aldo' => (float) $this->aldo,
                'diubah_oleh' => Auth::user()?->name,
            ],
        );

        $this->pesan = 'Persentase bagi hasil '.self::BULAN_LABELS[(int) $this->bulan].' '.$this->tahun.' berhasil disimpan.';
    }

    public function confirmDelete(int $id): void
    {
        $this->confirmingDeleteId = $id;
    }

    public function delete(): void
    {
        PersentaseBagiHasil::query()->whereKey($this->confirmingDeleteId)->delete();

        $this->confirmingDeleteId = null;
        $this->isiDariBulanTerpilih();
        $this->pesan = 'Pengaturan persentase dihapus.';
    }

    public function total(): float
    {
        return (float) $this->toko + (float) $this->krisna + (float) $this->aldo;
    }

    private function isiDariBulanTerpilih(): void
    {
        if ($this->bulan === '' || $this->tahun === '' || ! is_numeric($this->tahun)) {
            return;
        }

        $this->isiPersen(PersentaseBagiHasil::persenUntuk((int) $this->tahun, (int) $this->bulan));
        $this->resetValidation();
    }

    /**
     * @param  array<string, float>  $persen
     */
    private function isiPersen(array $persen): void
    {
        $this->toko = (string) $persen[Submission::PENERIMA_TOKO];
        $this->krisna = (string) $persen[Submission::PENERIMA_KRISNA];
        $this->aldo = (string) $persen[Submission::PENERIMA_ALDO];
    }

    public function render()
    {
        $tahunSekarang = (int) date('Y');
        $bulanSekarang = (int) date('n');

        $sudahAda = is_numeric($this->tahun) && $this->bulan !== ''
            ? PersentaseBagiHasil::query()->where('tahun', (int) $this->tahun)->where('bulan', (int) $this->bulan)->exists()
            : false;

        return view('livewire.admin.bagi-hasil-setting', [
            'pengaturans' => PersentaseBagiHasil::query()->orderByDesc('tahun')->orderByDesc('bulan')->get(),
            'persenBulanIni' => PersentaseBagiHasil::persenUntuk($tahunSekarang, $bulanSekarang),
            'bulanIniLabel' => self::BULAN_LABELS[$bulanSekarang].' '.$tahunSekarang,
            'bulanLabels' => self::BULAN_LABELS,
            'tahunOptions' => range($tahunSekarang + 1, min($tahunSekarang - 3, 2024)),
            'sudahAda' => $sudahAda,
            'total' => $this->total(),
        ]);
    }
}
