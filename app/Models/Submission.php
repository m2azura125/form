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

    protected $fillable = [
        'nama',
        'asal_kampus',
        'judul_alat',
        'fitur',
        'tanggal_pengajuan',
        'deadline',
        'biaya_jasa',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pengajuan' => 'date',
            'deadline' => 'date',
            'biaya_jasa' => 'integer',
        ];
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function biayaJasaFormatted(): ?string
    {
        return $this->biaya_jasa !== null ? 'Rp '.number_format($this->biaya_jasa, 0, ',', '.') : null;
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
