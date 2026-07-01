<?php

namespace Database\Factories;

use App\Models\Submission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Submission>
 */
class SubmissionFactory extends Factory
{
    private const JUDUL_ALAT = [
        'Alat Pengering Otomatis Berbasis Arduino',
        'Sistem Monitoring Suhu dan Kelembaban IoT',
        'Robot Line Follower untuk Riset',
        'Alat Penyiram Tanaman Otomatis',
        'Sistem Parkir Pintar Berbasis RFID',
        'Alat Pendeteksi Banjir Berbasis Sensor Ultrasonik',
        'Smart Home Automation dengan ESP32',
        'Alat Sortir Barang Otomatis Berbasis Warna',
        'Sistem Absensi Wajah Berbasis Kamera',
        'Alat Pengukur Kualitas Udara Portable',
    ];

    private const ASAL_KAMPUS = [
        'Universitas Gadjah Mada',
        'Institut Teknologi Bandung',
        'Universitas Indonesia',
        'Politeknik Negeri Jakarta',
        'SMK Negeri 1 Surabaya',
        'Universitas Brawijaya',
        'Telkom University',
        'Universitas Diponegoro',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tanggalPengajuan = $this->faker->dateTimeBetween('-2 months', 'now');
        $deadline = $this->faker->dateTimeBetween($tanggalPengajuan, '+2 months');
        $status = $this->faker->randomElement(Submission::STATUSES);

        return [
            'nama' => $this->faker->name(),
            'asal_kampus' => $this->faker->randomElement(self::ASAL_KAMPUS),
            'judul_alat' => $this->faker->randomElement(self::JUDUL_ALAT),
            'fitur' => $this->faker->paragraph(3),
            'tanggal_pengajuan' => $tanggalPengajuan,
            'deadline' => $deadline,
            'biaya_jasa' => in_array($status, [Submission::STATUS_DIPROSES, Submission::STATUS_SELESAI], true)
                ? $this->faker->numberBetween(3, 30) * 100000
                : null,
            'status' => $status,
        ];
    }
}
