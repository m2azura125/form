<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class SubmissionHistory extends Model
{
    use HasFactory;

    public const AKSI_BUAT = 'buat';
    public const AKSI_TERIMA = 'terima';
    public const AKSI_TOLAK = 'tolak';
    public const AKSI_TULIS_HARGA = 'tulis_harga';
    public const AKSI_GANTI_HARGA = 'ganti_harga';
    public const AKSI_SELESAI = 'selesai';
    public const AKSI_EDIT = 'edit';
    public const AKSI_HAPUS = 'hapus';
    public const AKSI_PULIHKAN = 'pulihkan';

    public const AKSI_LABELS = [
        self::AKSI_BUAT => 'Pengajuan Dibuat',
        self::AKSI_TERIMA => 'Diterima',
        self::AKSI_TOLAK => 'Ditolak',
        self::AKSI_TULIS_HARGA => 'Menulis Harga',
        self::AKSI_GANTI_HARGA => 'Mengganti Harga',
        self::AKSI_SELESAI => 'Selesai',
        self::AKSI_EDIT => 'Diubah',
        self::AKSI_HAPUS => 'Dihapus',
        self::AKSI_PULIHKAN => 'Dipulihkan',
    ];

    protected $fillable = [
        'submission_id',
        'user_id',
        'user_name',
        'aksi',
        'keterangan',
        'perubahan',
    ];

    protected function casts(): array
    {
        return [
            'perubahan' => 'array',
        ];
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class)->withTrashed();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function record(
        Submission $submission,
        string $aksi,
        string $keterangan,
        ?array $perubahan = null,
        ?User $user = null
    ): self {
        $currentUser = $user ?? Auth::user();

        return self::create([
            'submission_id' => $submission->id,
            'user_id' => $currentUser?->id,
            'user_name' => $currentUser?->name ?? 'Sistem / Pemohon',
            'aksi' => $aksi,
            'keterangan' => $keterangan,
            'perubahan' => $perubahan,
        ]);
    }

    public function aksiLabel(): string
    {
        return self::AKSI_LABELS[$this->aksi] ?? ucfirst($this->aksi);
    }
}
