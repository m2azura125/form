<?php

namespace App\Livewire\Public;

use App\Livewire\Forms\SubmissionForm as SubmissionFormObject;
use App\Models\Submission;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.public')]
class SubmissionForm extends Component
{
    public SubmissionFormObject $form;

    public string $honeypot = '';

    public bool $submitted = false;

    public function mount(): void
    {
        $this->form->tanggal_pengajuan = now()->format('Y-m-d');
    }

    public function updated(string $property): void
    {
        if (str_starts_with($property, 'form.')) {
            $this->form->validateOnly(str_replace('form.', '', $property));
        }
    }

    public function submit(): void
    {
        $rateLimitKey = 'submission-form:'.request()->ip();

        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            $this->addError('form.nama', 'Terlalu banyak percobaan. Silakan coba lagi dalam beberapa saat.');

            return;
        }

        RateLimiter::hit($rateLimitKey, 60);

        if (filled($this->honeypot)) {
            $this->reset('honeypot');
            $this->submitted = true;

            return;
        }

        $this->form->validate();

        $submission = Submission::query()->create($this->form->payload());

        \App\Models\SubmissionHistory::record(
            $submission,
            \App\Models\SubmissionHistory::AKSI_BUAT,
            "Pengajuan baru dibuat oleh {$submission->nama} ({$submission->tipeLabel()})"
        );

        $this->form->reset();
        $this->form->tanggal_pengajuan = now()->format('Y-m-d');
        $this->submitted = true;
    }

    public function ajukanLagi(): void
    {
        $this->submitted = false;
    }

    public function render()
    {
        return view('livewire.public.submission-form');
    }
}
