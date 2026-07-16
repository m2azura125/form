<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Submission extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_BARU = 'baru';
    public const STATUS_DIPROSES = 'diproses';
    public const STATUS_SELESAI = 'selesai';
    public const STATUS_DITOLAK = 'ditolak';

    public const STATUSES = [
        self::STATUS_BARU,
        self::STATUS_DIPROSES,
        self::STATUS_SELESAI,
        self::STATUS_DITOLAK,
    ];

    public const STATUS_LABELS = [
        self::STATUS_BARU => 'Baru',
        self::STATUS_DIPROSES => 'Diproses',
        self::STATUS_SELESAI => 'Selesai',
        self::STATUS_DITOLAK => 'Ditolak',
    ];

    public const TIPE_PROJECT = 'project';
    public const TIPE_CETAK_PCB = 'cetak_pcb';
    public const TIPE_CETAK_3D_PRINTING = 'cetak_3d_printing';
    public const TIPE_LAIN_LAIN = 'lain_lain';

    public const TIPES = [
        self::TIPE_PROJECT,
        self::TIPE_CETAK_PCB,
        self::TIPE_CETAK_3D_PRINTING,
        self::TIPE_LAIN_LAIN,
    ];

    public const TIPE_LABELS = [
        self::TIPE_PROJECT => 'Project',
        self::TIPE_CETAK_PCB => 'Cetak PCB',
        self::TIPE_CETAK_3D_PRINTING => 'Cetak 3D Printing',
        self::TIPE_LAIN_LAIN => 'Lain-lain',
    ];

    public const METODE_TUNAI = 'tunai';
    public const METODE_TRANSFER = 'transfer';
    public const METODE_QRIS = 'qris';
    public const METODE_E_WALLET = 'e_wallet';

    public const METODE_PEMBAYARANS = [
        self::METODE_TUNAI,
        self::METODE_TRANSFER,
        self::METODE_QRIS,
        self::METODE_E_WALLET,
    ];

    public const METODE_PEMBAYARAN_LABELS = [
        self::METODE_TUNAI => 'Tunai',
        self::METODE_TRANSFER => 'Transfer Bank',
        self::METODE_QRIS => 'QRIS',
        self::METODE_E_WALLET => 'E-Wallet',
    ];

    protected $fillable = [
        'nama',
        'asal_kampus',
        'judul_alat',
        'tipe',
        'fitur',
        'tanggal_pengajuan',
        'deadline',
        'biaya_jasa',
        'status',
        'urutan',
        'metode_pembayaran',
        'jumlah_bayar',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pengajuan' => 'date',
            'deadline' => 'date',
            'biaya_jasa' => 'integer',
            'urutan' => 'integer',
            'jumlah_bayar' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $submission): void {
            if ($submission->urutan === null) {
                $submission->urutan = (int) self::query()->max('urutan') + 1;
            }
        });
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function tipeLabel(): string
    {
        return self::TIPE_LABELS[$this->tipe] ?? $this->tipe;
    }

    public function biayaJasaFormatted(): ?string
    {
        return $this->biaya_jasa !== null ? 'Rp '.number_format($this->biaya_jasa, 0, ',', '.') : null;
    }

    public function metodePembayaranLabel(): ?string
    {
        return $this->metode_pembayaran !== null
            ? (self::METODE_PEMBAYARAN_LABELS[$this->metode_pembayaran] ?? $this->metode_pembayaran)
            : null;
    }

    public function jumlahBayarFormatted(): ?string
    {
        return $this->jumlah_bayar !== null ? 'Rp '.number_format($this->jumlah_bayar, 0, ',', '.') : null;
    }

    public function kembalian(): int
    {
        return max(($this->jumlah_bayar ?? 0) - ($this->biaya_jasa ?? 0), 0);
    }

    public function isOverdue(): bool
    {
        return $this->deadline->isPast() && ! $this->deadline->isToday() && $this->status !== self::STATUS_SELESAI;
    }

    public function isDeadlineUrgent(): bool
    {
        if ($this->status === self::STATUS_SELESAI || $this->isOverdue()) {
            return false;
        }

        return Carbon::today()->diffInDays($this->deadline, false) <= 3;
    }
}
