<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PersentaseBagiHasil extends Model
{
    /**
     * Persentase bawaan (dalam persen) dipakai jika belum ada pengaturan sama sekali.
     */
    public const DEFAULT = [
        Submission::PENERIMA_TOKO => 23.0,
        Submission::PENERIMA_KRISNA => 38.5,
        Submission::PENERIMA_ALDO => 38.5,
    ];

    protected $fillable = [
        'tahun',
        'bulan',
        'toko',
        'krisna',
        'aldo',
        'diubah_oleh',
    ];

    protected function casts(): array
    {
        return [
            'tahun' => 'integer',
            'bulan' => 'integer',
            'toko' => 'float',
            'krisna' => 'float',
            'aldo' => 'float',
        ];
    }

    /**
     * Pengaturan yang berlaku untuk suatu bulan: pengaturan bulan itu sendiri,
     * atau pengaturan terakhir sebelum bulan itu.
     */
    public static function berlakuUntuk(int $tahun, int $bulan): ?self
    {
        return self::query()
            ->where(function (Builder $q) use ($tahun, $bulan) {
                $q->where('tahun', '<', $tahun)
                    ->orWhere(fn (Builder $sub) => $sub->where('tahun', $tahun)->where('bulan', '<=', $bulan));
            })
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->first();
    }

    /**
     * Persentase (dalam persen, mis. 23.0) per penerima untuk suatu bulan.
     *
     * @return array<string, float>
     */
    public static function persenUntuk(int $tahun, int $bulan): array
    {
        return self::berlakuUntuk($tahun, $bulan)?->persen() ?? self::DEFAULT;
    }

    /**
     * @return array<string, float>
     */
    public function persen(): array
    {
        return [
            Submission::PENERIMA_TOKO => $this->toko,
            Submission::PENERIMA_KRISNA => $this->krisna,
            Submission::PENERIMA_ALDO => $this->aldo,
        ];
    }

    public static function formatPersen(float $persen): string
    {
        return rtrim(rtrim(number_format($persen, 2, ',', '.'), '0'), ',').'%';
    }
}
