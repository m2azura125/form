<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Dashboard;
use App\Models\Submission;
use App\Models\SubmissionHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class BuktiDpTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_bukti_dp_for_diproses_submission(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin']);
        $submission = Submission::factory()->create(['status' => Submission::STATUS_DIPROSES]);

        $this->actingAs($admin);

        Livewire::test(Dashboard::class)
            ->call('openUploadDp', $submission->id)
            ->set('buktiDp', UploadedFile::fake()->image('dp.jpg'))
            ->call('saveBuktiDp')
            ->assertHasNoErrors()
            ->assertSet('uploadingDpId', null);

        $submission->refresh();
        $this->assertNotNull($submission->bukti_dp);
        Storage::disk('local')->assertExists($submission->bukti_dp);
        $this->assertDatabaseHas('submission_histories', [
            'submission_id' => $submission->id,
            'aksi' => SubmissionHistory::AKSI_BUKTI_DP,
        ]);
    }

    public function test_replacing_bukti_dp_deletes_old_file(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin']);
        $oldPath = UploadedFile::fake()->image('lama.jpg')->store('bukti-dp', 'local');
        $submission = Submission::factory()->create(['status' => Submission::STATUS_DIPROSES, 'bukti_dp' => $oldPath]);

        $this->actingAs($admin);

        Livewire::test(Dashboard::class)
            ->call('openUploadDp', $submission->id)
            ->set('buktiDp', UploadedFile::fake()->create('baru.pdf', 100, 'application/pdf'))
            ->call('saveBuktiDp')
            ->assertHasNoErrors();

        Storage::disk('local')->assertMissing($oldPath);
        Storage::disk('local')->assertExists($submission->refresh()->bukti_dp);
    }

    public function test_bukti_dp_rejects_invalid_file_type(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin']);
        $submission = Submission::factory()->create(['status' => Submission::STATUS_DIPROSES]);

        $this->actingAs($admin);

        Livewire::test(Dashboard::class)
            ->call('openUploadDp', $submission->id)
            ->set('buktiDp', UploadedFile::fake()->create('virus.exe', 10))
            ->call('saveBuktiDp')
            ->assertHasErrors(['buktiDp' => 'mimes']);

        $this->assertNull($submission->refresh()->bukti_dp);
    }

    public function test_cannot_upload_bukti_dp_for_submission_not_diproses(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $submission = Submission::factory()->create(['status' => Submission::STATUS_BARU]);

        $this->actingAs($admin);

        Livewire::test(Dashboard::class)
            ->call('openUploadDp', $submission->id)
            ->assertSet('uploadingDpId', null);
    }

    public function test_bukti_dp_file_only_accessible_when_logged_in(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin']);
        $path = UploadedFile::fake()->image('dp.jpg')->store('bukti-dp', 'local');
        $submission = Submission::factory()->create(['status' => Submission::STATUS_DIPROSES, 'bukti_dp' => $path]);

        $this->get(route('admin.bukti-dp', $submission))->assertRedirect('/login');

        $this->actingAs($admin)->get(route('admin.bukti-dp', $submission))->assertOk();
    }
}
