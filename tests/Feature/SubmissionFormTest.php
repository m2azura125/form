<?php

namespace Tests\Feature;

use App\Models\Submission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SubmissionFormTest extends TestCase
{
    use RefreshDatabase;

    private function fillValidForm($component)
    {
        return $component
            ->set('form.nama', 'Budi Santoso')
            ->set('form.asal_kampus', 'Universitas Gadjah Mada')
            ->set('form.judul_alat', 'Alat Pengering Otomatis Berbasis Arduino')
            ->set('form.tipe', Submission::TIPE_PROJECT)
            ->set('form.fitur', 'Sensor kelembaban, kontrol suhu otomatis, notifikasi selesai.')
            ->set('form.tanggal_pengajuan', now()->format('Y-m-d'))
            ->set('form.deadline', now()->addDays(7)->format('Y-m-d'));
    }

    public function test_valid_submission_is_saved_with_status_baru(): void
    {
        $this->fillValidForm(Livewire::test(\App\Livewire\Public\SubmissionForm::class))
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('submissions', [
            'nama' => 'Budi Santoso',
            'asal_kampus' => 'Universitas Gadjah Mada',
            'judul_alat' => 'Alat Pengering Otomatis Berbasis Arduino',
            'status' => Submission::STATUS_BARU,
            'biaya_jasa' => null,
        ]);
    }

    public function test_deadline_before_tanggal_pengajuan_is_rejected(): void
    {
        $this->fillValidForm(Livewire::test(\App\Livewire\Public\SubmissionForm::class))
            ->set('form.deadline', now()->subDay()->format('Y-m-d'))
            ->call('submit')
            ->assertHasErrors(['form.deadline']);

        $this->assertDatabaseCount('submissions', 0);
    }

    public function test_judul_alat_is_required(): void
    {
        $this->fillValidForm(Livewire::test(\App\Livewire\Public\SubmissionForm::class))
            ->set('form.judul_alat', '')
            ->call('submit')
            ->assertHasErrors(['form.judul_alat']);

        $this->assertDatabaseCount('submissions', 0);
    }

    public function test_honeypot_silently_blocks_submission(): void
    {
        $this->fillValidForm(Livewire::test(\App\Livewire\Public\SubmissionForm::class))
            ->set('honeypot', 'im-a-bot')
            ->call('submit');

        $this->assertDatabaseCount('submissions', 0);
    }

    public function test_repeated_submission_is_rate_limited_after_five_attempts_per_ip(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->fillValidForm(Livewire::test(\App\Livewire\Public\SubmissionForm::class))
                ->call('submit')
                ->assertHasNoErrors();
        }

        $this->assertDatabaseCount('submissions', 5);

        $this->fillValidForm(Livewire::test(\App\Livewire\Public\SubmissionForm::class))
            ->call('submit')
            ->assertHasErrors(['form.nama']);

        $this->assertDatabaseCount('submissions', 5);
    }

    public function test_honeypot_attempts_also_count_toward_the_rate_limit(): void
    {
        for ($i = 0; $i < 5; $i++) {
            Livewire::test(\App\Livewire\Public\SubmissionForm::class)
                ->set('honeypot', 'im-a-bot')
                ->call('submit');
        }

        $this->fillValidForm(Livewire::test(\App\Livewire\Public\SubmissionForm::class))
            ->call('submit')
            ->assertHasErrors(['form.nama']);

        $this->assertDatabaseCount('submissions', 0);
    }
}
