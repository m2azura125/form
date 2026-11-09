<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\BagiHasilSetting;
use App\Livewire\Admin\Dashboard;
use App\Models\PersentaseBagiHasil;
use App\Models\Submission;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BagiHasilSettingTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_guest_cannot_access_page(): void
    {
        $this->get('/admin/bagi-hasil')->assertRedirect('/login');
    }

    public function test_admin_can_view_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/admin/bagi-hasil')->assertOk()->assertSee('Persentase Bagi Hasil');
    }

    public function test_admin_can_save_percentages_for_a_month(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'name' => 'Aldo']);

        Livewire::actingAs($admin)
            ->test(BagiHasilSetting::class)
            ->set('bulan', '11')
            ->set('tahun', '2026')
            ->set('toko', '20')
            ->set('krisna', '40')
            ->set('aldo', '40')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('persentase_bagi_hasils', [
            'tahun' => 2026,
            'bulan' => 11,
            'toko' => 20,
            'krisna' => 40,
            'aldo' => 40,
            'diubah_oleh' => 'Aldo',
        ]);
    }

    public function test_total_must_be_100(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(BagiHasilSetting::class)
            ->set('toko', '30')
            ->set('krisna', '40')
            ->set('aldo', '40')
            ->call('save')
            ->assertHasErrors(['total']);

        $this->assertDatabaseCount('persentase_bagi_hasils', 0);
    }

    public function test_editing_existing_month_updates_instead_of_duplicating(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $pengaturan = PersentaseBagiHasil::query()->create(['tahun' => 2026, 'bulan' => 10, 'toko' => 20, 'krisna' => 40, 'aldo' => 40]);

        Livewire::actingAs($admin)
            ->test(BagiHasilSetting::class)
            ->call('edit', $pengaturan->id)
            ->assertSet('bulan', '10')
            ->assertSet('toko', '20')
            ->set('toko', '30')
            ->set('krisna', '35')
            ->set('aldo', '35')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('persentase_bagi_hasils', 1);
        $this->assertSame(30.0, $pengaturan->refresh()->toko);
    }

    public function test_admin_can_delete_setting(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $pengaturan = PersentaseBagiHasil::query()->create(['tahun' => 2026, 'bulan' => 10, 'toko' => 20, 'krisna' => 40, 'aldo' => 40]);

        Livewire::actingAs($admin)
            ->test(BagiHasilSetting::class)
            ->call('confirmDelete', $pengaturan->id)
            ->call('delete');

        $this->assertDatabaseCount('persentase_bagi_hasils', 0);
    }

    public function test_month_without_setting_uses_latest_previous_setting_or_default(): void
    {
        PersentaseBagiHasil::query()->create(['tahun' => 2026, 'bulan' => 9, 'toko' => 20, 'krisna' => 40, 'aldo' => 40]);
        PersentaseBagiHasil::query()->create(['tahun' => 2026, 'bulan' => 12, 'toko' => 10, 'krisna' => 45, 'aldo' => 45]);

        $this->assertSame(PersentaseBagiHasil::DEFAULT, PersentaseBagiHasil::persenUntuk(2026, 8));
        $this->assertSame(20.0, PersentaseBagiHasil::persenUntuk(2026, 9)['toko']);
        $this->assertSame(20.0, PersentaseBagiHasil::persenUntuk(2026, 11)['toko']);
        $this->assertSame(10.0, PersentaseBagiHasil::persenUntuk(2027, 3)['toko']);
    }

    public function test_dashboard_uses_percentage_of_each_completion_month(): void
    {
        Carbon::setTestNow('2026-11-15');
        $admin = User::factory()->create(['role' => 'admin']);
        PersentaseBagiHasil::query()->create(['tahun' => 2026, 'bulan' => 11, 'toko' => 20, 'krisna' => 40, 'aldo' => 40]);

        // Oktober: belum ada pengaturan -> bawaan 23 / 38,5 / 38,5.
        Submission::factory()->create([
            'status' => Submission::STATUS_SELESAI,
            'tipe' => Submission::TIPE_PROJECT,
            'biaya_jasa' => 100000,
            'pembagian_prioritas' => 0,
            'tanggal_pengajuan' => '2026-10-05',
            'tanggal_selesai' => '2026-10-20 10:00:00',
        ]);
        // November: 20 / 40 / 40.
        Submission::factory()->create([
            'status' => Submission::STATUS_SELESAI,
            'tipe' => Submission::TIPE_PROJECT,
            'biaya_jasa' => 100000,
            'pembagian_prioritas' => 0,
            'tanggal_pengajuan' => '2026-11-02',
            'tanggal_selesai' => '2026-11-10 10:00:00',
        ]);

        $this->actingAs($admin);

        Livewire::test(Dashboard::class)
            ->set('bulan', '11')
            ->assertViewHas('bagiHasil', fn ($bagiHasil) => $bagiHasil[Submission::PENERIMA_TOKO]['nominal'] === 20000
                && $bagiHasil[Submission::PENERIMA_KRISNA]['nominal'] === 40000
                && $bagiHasil[Submission::PENERIMA_TOKO]['persen'] === 20.0)
            ->set('bulan', '10')
            ->assertViewHas('bagiHasil', fn ($bagiHasil) => $bagiHasil[Submission::PENERIMA_TOKO]['nominal'] === 23000
                && $bagiHasil[Submission::PENERIMA_KRISNA]['nominal'] === 38500)
            ->set('bulan', 'all')
            ->assertViewHas('bagiHasil', fn ($bagiHasil) => $bagiHasil[Submission::PENERIMA_TOKO]['nominal'] === 43000
                && $bagiHasil[Submission::PENERIMA_KRISNA]['nominal'] === 78500
                && $bagiHasil[Submission::PENERIMA_TOKO]['persen'] === null);
    }
}
