<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\HistoryIndex;
use App\Models\Submission;
use App\Models\SubmissionHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Livewire\Volt\Volt;
use Tests\TestCase;

class HistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_krisna_user_can_authenticate_with_password(): void
    {
        $this->seed();

        $component = Volt::test('pages.auth.login')
            ->set('form.login', 'krisna')
            ->set('form.password', 'susjol123');

        $component->call('login');

        $component
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.dashboard', absolute: false));

        $this->assertAuthenticated();
        $this->assertEquals('Krisna', auth()->user()->name);
    }

    public function test_accepting_submission_records_history_with_user_name(): void
    {
        $krisna = User::factory()->create([
            'name' => 'Krisna',
            'username' => 'krisna',
            'role' => 'admin',
        ]);

        $submission = Submission::factory()->create([
            'status' => Submission::STATUS_BARU,
            'tanggal_pengajuan' => now()->format('Y-m-d'),
        ]);

        Livewire::actingAs($krisna)
            ->test(Dashboard::class)
            ->call('openAccept', $submission->id)
            ->set('acceptBiayaJasa', '250000')
            ->set('acceptDeadline', now()->addDays(5)->format('Y-m-d'))
            ->call('acceptSubmission');

        $this->assertDatabaseHas('submission_histories', [
            'submission_id' => $submission->id,
            'user_id' => $krisna->id,
            'user_name' => 'Krisna',
            'aksi' => SubmissionHistory::AKSI_TERIMA,
        ]);

        $this->assertDatabaseHas('submission_histories', [
            'submission_id' => $submission->id,
            'user_id' => $krisna->id,
            'user_name' => 'Krisna',
            'aksi' => SubmissionHistory::AKSI_TULIS_HARGA,
        ]);
    }

    public function test_rejecting_submission_records_history(): void
    {
        $krisna = User::factory()->create([
            'name' => 'Krisna',
            'username' => 'krisna',
            'role' => 'admin',
        ]);

        $submission = Submission::factory()->create([
            'status' => Submission::STATUS_BARU,
        ]);

        Livewire::actingAs($krisna)
            ->test(Dashboard::class)
            ->call('rejectSubmission', $submission->id);

        $this->assertDatabaseHas('submission_histories', [
            'submission_id' => $submission->id,
            'user_id' => $krisna->id,
            'user_name' => 'Krisna',
            'aksi' => SubmissionHistory::AKSI_TOLAK,
        ]);
    }

    public function test_changing_price_records_ganti_harga_history(): void
    {
        $admin = User::factory()->create([
            'name' => 'Administrator',
            'role' => 'admin',
        ]);

        $submission = Submission::factory()->create([
            'biaya_jasa' => 150000,
            'status' => Submission::STATUS_DIPROSES,
        ]);

        Livewire::actingAs($admin)
            ->test(Dashboard::class)
            ->call('openEdit', $submission->id)
            ->set('form.biaya_jasa', '200000')
            ->call('save');

        $this->assertDatabaseHas('submission_histories', [
            'submission_id' => $submission->id,
            'user_id' => $admin->id,
            'user_name' => 'Administrator',
            'aksi' => SubmissionHistory::AKSI_GANTI_HARGA,
        ]);
    }

    public function test_admin_can_view_history_index_page(): void
    {
        $krisna = User::factory()->create([
            'name' => 'Krisna',
            'username' => 'krisna',
            'role' => 'admin',
        ]);

        $submission = Submission::factory()->create(['nama' => 'Budi Santoso', 'judul_alat' => 'Robotic Arm']);

        SubmissionHistory::record($submission, SubmissionHistory::AKSI_TERIMA, 'Menerima pengajuan dan menetapkan biaya jasa: Rp 200.000', null, $krisna);

        $response = $this->actingAs($krisna)->get(route('admin.history'));
        $response->assertOk();
        $response->assertSee('History Perubahan');
        $response->assertSee('Krisna');
        $response->assertSee('Budi Santoso');
        $response->assertSee('Diterima');
    }
}
