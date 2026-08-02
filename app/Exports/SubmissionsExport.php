<?php

namespace App\Exports;

use App\Models\Submission;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SubmissionsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function __construct(private array $filters = []) {}

    public function query(): Builder
    {
        $query = Submission::query();

        if (filled($this->filters['q'] ?? null)) {
            $term = $this->filters['q'];
            $query->where(function (Builder $q) use ($term) {
                $q->where('nama', 'like', "%{$term}%")
                    ->orWhere('judul_alat', 'like', "%{$term}%");
            });
        }

        if (filled($this->filters['status'] ?? null)) {
            $query->where('status', $this->filters['status']);
        }

        if (filled($this->filters['tipe'] ?? null)) {
            $query->where('tipe', $this->filters['tipe']);
        }

        if (filled($this->filters['tgl_dari'] ?? null)) {
            $query->whereDate('tanggal_pengajuan', '>=', $this->filters['tgl_dari']);
        }

        if (filled($this->filters['tgl_sampai'] ?? null)) {
            $query->whereDate('tanggal_pengajuan', '<=', $this->filters['tgl_sampai']);
        }

        if (filled($this->filters['dl_dari'] ?? null)) {
            $query->whereDate('deadline', '>=', $this->filters['dl_dari']);
        }

        if (filled($this->filters['dl_sampai'] ?? null)) {
            $query->whereDate('deadline', '<=', $this->filters['dl_sampai']);
        }

        if (filled($this->filters['bulan'] ?? null) && $this->filters['bulan'] !== 'all') {
            $query->whereMonth('tanggal_pengajuan', $this->filters['bulan']);
        }

        if (filled($this->filters['tahun'] ?? null) && $this->filters['tahun'] !== 'all') {
            $query->whereYear('tanggal_pengajuan', $this->filters['tahun']);
        }

        return $query->orderBy('tanggal_pengajuan', 'desc');
    }

    public function headings(): array
    {
        return [
            'No', 'Nama', 'Asal Kampus/Sekolah', 'Judul Alat', 'Tipe', 'Fitur',
            'Tanggal Pengajuan', 'Deadline', 'Biaya Jasa', 'Status', 'Dibuat Pada',
        ];
    }

    public function map($submission): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $this->escapeFormula($submission->nama),
            $this->escapeFormula($submission->asal_kampus),
            $this->escapeFormula($submission->judul_alat),
            $submission->tipeLabel(),
            $this->escapeFormula($submission->fitur),
            $submission->tanggal_pengajuan->format('d-m-Y'),
            $submission->deadline->format('d-m-Y'),
            $submission->biaya_jasa,
            $submission->statusLabel(),
            $submission->created_at->format('d-m-Y H:i'),
        ];
    }

    /**
     * Neutralize leading characters (=, +, -, @, tab, CR) that spreadsheet
     * apps interpret as the start of a formula, to prevent formula/CSV
     * injection from user-submitted values (the public form is unauthenticated).
     */
    private function escapeFormula(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        return preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
