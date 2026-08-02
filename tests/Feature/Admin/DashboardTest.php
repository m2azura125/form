<?php

namespace Tests\Feature\Admin;

use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_admin_dashboard(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');
    }

    public function test_admin_can_view_dashboard_with_submissions(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Submission::factory()->count(3)->create();

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSeeLivewire('admin.dashboard');
    }

    public function test_search_filters_submissions_by_name(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Submission::factory()->create(['nama' => 'Unique Name Match']);
        Submission::factory()->create(['nama' => 'Someone Else']);

        $this->actingAs($admin);

        Livewire::test(\App\Livewire\Admin\Dashboard::class)
            ->set('search', 'Unique Name')
            ->assertSee('Unique Name Match')
            ->assertDontSee('Someone Else');
    }

    public function test_search_also_matches_judul_alat(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Submission::factory()->create(['judul_alat' => 'Robot Line Follower Khusus']);
        Submission::factory()->create(['judul_alat' => 'Alat Lainnya']);

        $this->actingAs($admin);

        Livewire::test(\App\Livewire\Admin\Dashboard::class)
            ->set('search', 'Line Follower')
            ->assertSee('Robot Line Follower Khusus')
            ->assertDontSee('Alat Lainnya');
    }

    public function test_admin_can_soft_delete_submission(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $submission = Submission::factory()->create();

        $this->actingAs($admin);

        Livewire::test(\App\Livewire\Admin\Dashboard::class)
            ->call('confirmDelete', $submission->id)
            ->call('deleteSubmission');

        $this->assertSoftDeleted('submissions', ['id' => $submission->id]);
    }

    public function test_only_super_admin_can_access_trash_or_restore(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $submission = Submission::factory()->create();
        $submission->delete();

        $this->actingAs($admin);

        Livewire::test(\App\Livewire\Admin\Dashboard::class)
            ->call('restoreSubmission', $submission->id)
            ->assertForbidden();
    }

    public function test_super_admin_can_restore_soft_deleted_submission(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $submission = Submission::factory()->create();
        $submission->delete();

        $this->actingAs($superAdmin);

        Livewire::test(\App\Livewire\Admin\Dashboard::class)
            ->call('restoreSubmission', $submission->id);

        $this->assertDatabaseHas('submissions', ['id' => $submission->id, 'deleted_at' => null]);
    }

    public function test_admin_can_update_submission_status_via_edit_form(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $submission = Submission::factory()->create(['status' => Submission::STATUS_BARU, 'tipe' => Submission::TIPE_PROJECT]);

        $this->actingAs($admin);

        Livewire::test(\App\Livewire\Admin\Dashboard::class)
            ->call('openEdit', $submission->id)
            ->set('form.status', Submission::STATUS_DIPROSES)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('submissions', [
            'id' => $submission->id,
            'status' => Submission::STATUS_DIPROSES,
        ]);
    }

    public function test_accepting_a_new_submission_requires_biaya_jasa_and_deadline(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $submission = Submission::factory()->create(['status' => Submission::STATUS_BARU, 'tipe' => Submission::TIPE_PROJECT]);

        $this->actingAs($admin);

        Livewire::test(\App\Livewire\Admin\Dashboard::class)
            ->call('openAccept', $submission->id)
            ->set('acceptBiayaJasa', '')
            ->call('acceptSubmission')
            ->assertHasErrors(['acceptBiayaJasa']);

        $this->assertDatabaseHas('submissions', [
            'id' => $submission->id,
            'status' => Submission::STATUS_BARU,
        ]);
    }

    public function test_accepting_a_new_submission_sets_biaya_jasa_deadline_and_status_diproses(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $submission = Submission::factory()->create(['status' => Submission::STATUS_BARU, 'tipe' => Submission::TIPE_PROJECT]);
        $newDeadline = now()->addDays(14)->format('Y-m-d');

        $this->actingAs($admin);

        Livewire::test(\App\Livewire\Admin\Dashboard::class)
            ->call('openAccept', $submission->id)
            ->set('acceptBiayaJasa', '750000')
            ->set('acceptDeadline', $newDeadline)
            ->call('acceptSubmission')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('submissions', [
            'id' => $submission->id,
            'status' => Submission::STATUS_DIPROSES,
            'biaya_jasa' => 750000,
        ]);
        $this->assertSame($newDeadline, $submission->fresh()->deadline->format('Y-m-d'));
    }

    public function test_accepting_a_lain_lain_submission_requires_penerima(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $submission = Submission::factory()->create(['status' => Submission::STATUS_BARU, 'tipe' => Submission::TIPE_LAIN_LAIN]);

        $this->actingAs($admin);

        Livewire::test(\App\Livewire\Admin\Dashboard::class)
            ->call('openAccept', $submission->id)
            ->set('acceptBiayaJasa', '100000')
            ->set('acceptDeadline', $submission->deadline->format('Y-m-d'))
            ->call('acceptSubmission')
            ->assertHasErrors(['acceptPenerima']);

        $this->assertDatabaseHas('submissions', [
            'id' => $submission->id,
            'status' => Submission::STATUS_BARU,
        ]);
    }

    public function test_accepting_a_lain_lain_submission_saves_penerima(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $submission = Submission::factory()->create(['status' => Submission::STATUS_BARU, 'tipe' => Submission::TIPE_LAIN_LAIN]);

        $this->actingAs($admin);

        Livewire::test(\App\Livewire\Admin\Dashboard::class)
            ->call('openAccept', $submission->id)
            ->set('acceptBiayaJasa', '100000')
            ->set('acceptDeadline', $submission->deadline->format('Y-m-d'))
            ->set('acceptPenerima', Submission::PENERIMA_KRISNA)
            ->call('acceptSubmission')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('submissions', [
            'id' => $submission->id,
            'status' => Submission::STATUS_DIPROSES,
            'penerima' => Submission::PENERIMA_KRISNA,
        ]);
    }

    public function test_rejecting_a_new_submission_sets_status_ditolak(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $submission = Submission::factory()->create(['status' => Submission::STATUS_BARU]);

        $this->actingAs($admin);

        Livewire::test(\App\Livewire\Admin\Dashboard::class)
            ->call('rejectSubmission', $submission->id);

        $this->assertDatabaseHas('submissions', [
            'id' => $submission->id,
            'status' => Submission::STATUS_DITOLAK,
        ]);
    }

    public function test_cannot_reject_a_submission_that_is_already_being_processed(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $submission = Submission::factory()->create(['status' => Submission::STATUS_DIPROSES]);

        $this->actingAs($admin);

        Livewire::test(\App\Livewire\Admin\Dashboard::class)
            ->call('rejectSubmission', $submission->id);

        $this->assertDatabaseHas('submissions', [
            'id' => $submission->id,
            'status' => Submission::STATUS_DIPROSES,
        ]);
    }

    public function test_completing_a_processed_submission_sets_status_selesai(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $submission = Submission::factory()->create([
            'status' => Submission::STATUS_DIPROSES,
            'biaya_jasa' => 200000,
            'tipe' => Submission::TIPE_PROJECT,
        ]);

        $this->actingAs($admin);

        Livewire::test(\App\Livewire\Admin\Dashboard::class)
            ->call('openComplete', $submission->id)
            ->set('completeMetodePembayaran', Submission::METODE_TRANSFER)
            ->set('completeJumlahBayar', '200000')
            ->call('completeSubmission')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('submissions', [
            'id' => $submission->id,
            'status' => Submission::STATUS_SELESAI,
            'metode_pembayaran' => Submission::METODE_TRANSFER,
            'jumlah_bayar' => 200000,
        ]);
    }

    public function test_completing_a_submission_requires_jumlah_bayar_at_least_biaya_jasa(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $submission = Submission::factory()->create([
            'status' => Submission::STATUS_DIPROSES,
            'biaya_jasa' => 200000,
            'tipe' => Submission::TIPE_PROJECT,
        ]);

        $this->actingAs($admin);

        Livewire::test(\App\Livewire\Admin\Dashboard::class)
            ->call('openComplete', $submission->id)
            ->set('completeJumlahBayar', '100000')
            ->call('completeSubmission')
            ->assertHasErrors(['completeJumlahBayar']);

        $this->assertDatabaseHas('submissions', [
            'id' => $submission->id,
            'status' => Submission::STATUS_DIPROSES,
        ]);
    }

    public function test_cannot_complete_a_submission_that_is_still_new(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $submission = Submission::factory()->create(['status' => Submission::STATUS_BARU]);

        $this->actingAs($admin);

        Livewire::test(\App\Livewire\Admin\Dashboard::class)
            ->call('openComplete', $submission->id)
            ->call('completeSubmission');

        $this->assertDatabaseHas('submissions', [
            'id' => $submission->id,
            'status' => Submission::STATUS_BARU,
        ]);
    }

    public function test_admin_can_edit_penerima_for_completed_lain_lain_submission(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $submission = Submission::factory()->create([
            'status' => Submission::STATUS_SELESAI,
            'tipe' => Submission::TIPE_LAIN_LAIN,
            'penerima' => Submission::PENERIMA_KRISNA,
            'biaya_jasa' => 150000,
            'jumlah_bayar' => 150000,
            'metode_pembayaran' => Submission::METODE_TUNAI,
        ]);

        $this->actingAs($admin);

        Livewire::test(\App\Livewire\Admin\Dashboard::class)
            ->call('openEdit', $submission->id)
            ->set('form.penerima', Submission::PENERIMA_ALDO)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('submissions', [
            'id' => $submission->id,
            'penerima' => Submission::PENERIMA_ALDO,
            'status' => Submission::STATUS_SELESAI,
        ]);
    }

    public function test_completing_lain_lain_submission_saves_selected_penerima(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $submission = Submission::factory()->create([
            'status' => Submission::STATUS_DIPROSES,
            'tipe' => Submission::TIPE_LAIN_LAIN,
            'penerima' => Submission::PENERIMA_KRISNA,
            'biaya_jasa' => 200000,
        ]);

        $this->actingAs($admin);

        Livewire::test(\App\Livewire\Admin\Dashboard::class)
            ->call('openComplete', $submission->id)
            ->set('completeMetodePembayaran', Submission::METODE_TRANSFER)
            ->set('completeJumlahBayar', '200000')
            ->set('completePenerima', Submission::PENERIMA_ALDO)
            ->call('completeSubmission')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('submissions', [
            'id' => $submission->id,
            'status' => Submission::STATUS_SELESAI,
            'penerima' => Submission::PENERIMA_ALDO,
        ]);
    }

    public function test_pembagian_prioritas_deducted_before_percentage_split(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Submission::factory()->create([
            'status' => Submission::STATUS_SELESAI,
            'tipe' => Submission::TIPE_PROJECT,
            'biaya_jasa' => 100000,
            'pembagian_prioritas' => 20000,
            'penerima_prioritas' => 'lainnya',
        ]);

        $this->actingAs($admin);

        // Project net = 100,000 - 20,000 = 80,000.
        // Toko (23%) = 18,400. Krisna (38.5%) = 30,800. Aldo (38.5%) = 30,800.
        // Other (Prioritas) = 20,000.
        Livewire::test(\App\Livewire\Admin\Dashboard::class)
            ->assertViewHas('bagiHasil', function ($bagiHasil) {
                return $bagiHasil[Submission::PENERIMA_TOKO]['nominal'] === 18400
                    && $bagiHasil[Submission::PENERIMA_KRISNA]['nominal'] === 30800
                    && $bagiHasil[Submission::PENERIMA_ALDO]['nominal'] === 30800;
            })
            ->assertViewHas('totalOther', 20000)
            ->assertViewHas('totalPendapatan', 100000);
    }

    public function test_pembagian_prioritas_assigned_to_specific_person_krisna(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Submission::factory()->create([
            'status' => Submission::STATUS_SELESAI,
            'tipe' => Submission::TIPE_PROJECT,
            'biaya_jasa' => 700000,
            'pembagian_prioritas' => 200000,
            'penerima_prioritas' => 'krisna',
        ]);

        $this->actingAs($admin);

        // Total = 700,000. Prioritas Krisna = 200,000. Net = 500,000.
        // Toko (23% of 500k) = 115,000.
        // Krisna (38.5% of 500k = 192,500 + 200,000 prioritas) = 392,500.
        // Aldo (38.5% of 500k) = 192,500.
        // Total Other = 0.
        Livewire::test(\App\Livewire\Admin\Dashboard::class)
            ->assertViewHas('bagiHasil', function ($bagiHasil) {
                return $bagiHasil[Submission::PENERIMA_TOKO]['nominal'] === 115000
                    && $bagiHasil[Submission::PENERIMA_KRISNA]['nominal'] === 392500
                    && $bagiHasil[Submission::PENERIMA_ALDO]['nominal'] === 192500;
            })
            ->assertViewHas('totalOther', 0)
            ->assertViewHas('totalPendapatan', 700000);
    }

    public function test_dashboard_defaults_to_current_month_and_year_session(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $currentMonth = (string) date('n');
        $currentYear = (string) date('Y');

        $this->actingAs($admin);

        Livewire::test(\App\Livewire\Admin\Dashboard::class)
            ->assertSet('bulan', $currentMonth)
            ->assertSet('tahun', $currentYear);
    }

    public function test_switching_month_session_resets_monthly_stats_and_bagi_hasil(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Create submission in July 2026
        Submission::factory()->create([
            'nama' => 'Juli Submission',
            'status' => Submission::STATUS_SELESAI,
            'biaya_jasa' => 500000,
            'tanggal_pengajuan' => '2026-07-15',
        ]);

        $this->actingAs($admin);

        // Viewing August 2026 should show 0 for July submission (resets to 0 for August)
        Livewire::test(\App\Livewire\Admin\Dashboard::class)
            ->set('tahun', '2026')
            ->set('bulan', '8')
            ->assertDontSee('Juli Submission')
            ->assertViewHas('totalPendapatan', 0)
            ->assertViewHas('summary', function ($summary) {
                return $summary['total'] === 0 && $summary['selesai'] === 0;
            });

        // Switching to July 2026 (Bulan 7) should display July's data
        Livewire::test(\App\Livewire\Admin\Dashboard::class)
            ->set('tahun', '2026')
            ->set('bulan', '7')
            ->assertSee('Juli Submission')
            ->assertViewHas('totalPendapatan', 500000)
            ->assertViewHas('summary', function ($summary) {
                return $summary['total'] === 1 && $summary['selesai'] === 1;
            });
    }

    public function test_all_months_total_view_aggregates_all_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Submission::factory()->create([
            'nama' => 'Submission July',
            'status' => Submission::STATUS_SELESAI,
            'biaya_jasa' => 200000,
            'tanggal_pengajuan' => '2026-07-10',
        ]);

        Submission::factory()->create([
            'nama' => 'Submission August',
            'status' => Submission::STATUS_SELESAI,
            'biaya_jasa' => 300000,
            'tanggal_pengajuan' => '2026-08-10',
        ]);

        $this->actingAs($admin);

        // Setting bulan to 'all' should show total across both July and August
        Livewire::test(\App\Livewire\Admin\Dashboard::class)
            ->set('tahun', '2026')
            ->call('setBulan', 'all')
            ->assertSee('Submission July')
            ->assertSee('Submission August')
            ->assertViewHas('totalPendapatan', 500000)
            ->assertViewHas('summary', function ($summary) {
                return $summary['total'] === 2 && $summary['selesai'] === 2;
            });
    }
}
