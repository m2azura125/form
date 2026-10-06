<?php

namespace Tests\Feature;

use App\Models\Submission;
use App\Models\SubmissionHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PindahBulanTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_pengajuan_baru_dan_diproses_bulan_lalu_dipindah_dengan_sisa_hari_sama(): void
    {
        Carbon::setTestNow('2026-09-24');
        $baru = Submission::factory()->create([
            'status' => Submission::STATUS_BARU,
            'tanggal_pengajuan' => '2026-09-24',
            'deadline' => '2026-09-27',
        ]);
        $diproses = Submission::factory()->create([
            'status' => Submission::STATUS_DIPROSES,
            'tanggal_pengajuan' => '2026-09-10',
            'deadline' => '2026-10-20',
        ]);

        Carbon::setTestNow('2026-10-01 08:00:00');
        $jumlah = Submission::pindahkanKeBulanIni();

        $this->assertSame(2, $jumlah);

        $baru->refresh();
        $this->assertSame('2026-10-01', $baru->tanggal_pengajuan->format('Y-m-d'));
        $this->assertSame('2026-10-04', $baru->deadline->format('Y-m-d'));

        $diproses->refresh();
        $this->assertSame('2026-10-01', $diproses->tanggal_pengajuan->format('Y-m-d'));
        $this->assertSame('2026-11-10', $diproses->deadline->format('Y-m-d'));

        $this->assertDatabaseHas('submission_histories', [
            'submission_id' => $baru->id,
            'aksi' => SubmissionHistory::AKSI_PINDAH_BULAN,
            'user_name' => 'Sistem',
        ]);
    }

    public function test_pengajuan_selesai_ditolak_dan_bulan_ini_tidak_dipindah(): void
    {
        Carbon::setTestNow('2026-10-15');
        $selesai = Submission::factory()->create(['status' => Submission::STATUS_SELESAI, 'tanggal_pengajuan' => '2026-09-05', 'deadline' => '2026-09-10']);
        $ditolak = Submission::factory()->create(['status' => Submission::STATUS_DITOLAK, 'tanggal_pengajuan' => '2026-09-05', 'deadline' => '2026-09-10']);
        $bulanIni = Submission::factory()->create(['status' => Submission::STATUS_BARU, 'tanggal_pengajuan' => '2026-10-03', 'deadline' => '2026-10-10']);

        $this->assertSame(0, Submission::pindahkanKeBulanIni());

        $this->assertSame('2026-09-05', $selesai->refresh()->tanggal_pengajuan->format('Y-m-d'));
        $this->assertSame('2026-09-05', $ditolak->refresh()->tanggal_pengajuan->format('Y-m-d'));
        $this->assertSame('2026-10-03', $bulanIni->refresh()->tanggal_pengajuan->format('Y-m-d'));
    }

    public function test_dijalankan_berulang_tidak_menggeser_lagi(): void
    {
        Carbon::setTestNow('2026-10-01');
        $submission = Submission::factory()->create(['status' => Submission::STATUS_BARU, 'tanggal_pengajuan' => '2026-09-24', 'deadline' => '2026-09-27']);

        Submission::pindahkanKeBulanIni();
        Submission::pindahkanKeBulanIni();

        $this->assertSame('2026-10-04', $submission->refresh()->deadline->format('Y-m-d'));
        $this->assertSame(1, SubmissionHistory::query()->where('aksi', SubmissionHistory::AKSI_PINDAH_BULAN)->count());
    }

    public function test_membuka_dashboard_menjalankan_pemindahan(): void
    {
        Carbon::setTestNow('2026-10-02');
        $admin = User::factory()->create(['role' => 'admin']);
        $submission = Submission::factory()->create(['status' => Submission::STATUS_DIPROSES, 'tanggal_pengajuan' => '2026-09-24', 'deadline' => '2026-09-27']);

        $this->actingAs($admin)->get('/admin/dashboard')->assertOk();

        $this->assertSame('2026-10-04', $submission->refresh()->deadline->format('Y-m-d'));
    }

    public function test_command_pindah_bulan(): void
    {
        Carbon::setTestNow('2026-10-01');
        Submission::factory()->create(['status' => Submission::STATUS_BARU, 'tanggal_pengajuan' => '2026-09-24', 'deadline' => '2026-09-27']);

        $this->artisan('pengajuan:pindah-bulan')
            ->expectsOutput('1 pengajuan dipindah ke bulan ini.')
            ->assertSuccessful();
    }
}
